<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\BillingSetting;
use App\Models\PayslipUsage;
use App\Services\BillingService;
use Illuminate\Console\Command;

class BillingStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:status 
                            {--user= : Show billing status for a specific user ID}
                            {--all : Show billing summary for all company users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display billing status and statistics';

    /**
     * @var BillingService
     */
    protected $billingService;

    /**
     * Create a new command instance.
     */
    public function __construct(BillingService $billingService)
    {
        parent::__construct();
        $this->billingService = $billingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($userId = $this->option('user')) {
            return $this->showUserStatus($userId);
        }
        
        if ($this->option('all')) {
            return $this->showAllUsersStatus();
        }
        
        // Default: show system-wide statistics
        return $this->showSystemStatistics();
    }

    /**
     * Show billing status for a specific user
     */
    protected function showUserStatus(int $userId): int
    {
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("User with ID {$userId} not found");
            return 1;
        }
        
        $this->info("Billing Status for: {$user->name} ({$user->email})");
        $this->newLine();
        
        // Basic info
        $this->table(
            ['Field', 'Value'],
            [
                ['User ID', $user->id],
                ['User Type', $user->type],
                ['Billing Status', $user->billing_status ?? 'active'],
                ['Trial Status', $user->trial_ends_at ? 'Trial until ' . $user->trial_ends_at->format('Y-m-d') : 'No trial'],
            ]
        );
        
        // Trial status
        $trialStatus = $this->billingService->getTrialStatus($user);
        
        $this->newLine();
        $this->info('Trial Information:');
        $this->table(
            ['Field', 'Value'],
            [
                ['In Trial', $trialStatus['in_trial'] ? 'Yes' : 'No'],
                ['Days Remaining', $trialStatus['days_remaining'] ?? 'N/A'],
                ['Trial Payslips Used', $trialStatus['trial_payslips_used'] ?? 0],
                ['Trial Payslips Limit', $trialStatus['trial_payslips_limit'] ?? 'N/A'],
                ['Trial Exhausted', $trialStatus['trial_exhausted'] ? 'Yes' : 'No'],
            ]
        );
        
        // Current cycle
        $cycleSummary = $this->billingService->getCurrentCycleSummary($user);
        
        $this->newLine();
        $this->info('Current Billing Cycle:');
        $this->table(
            ['Field', 'Value'],
            [
                ['Cycle Status', $cycleSummary['cycle_status']],
                ['Start Date', $cycleSummary['cycle_start'] ?? 'N/A'],
                ['End Date', $cycleSummary['cycle_end'] ?? 'N/A'],
                ['Payslips Count', $cycleSummary['payslips_count']],
                ['Current Charges', 'R ' . number_format($cycleSummary['current_charges'], 2)],
                ['Estimated Total', 'R ' . number_format($cycleSummary['estimated_total'], 2)],
            ]
        );
        
        // Recent usage
        $recentUsage = PayslipUsage::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        if ($recentUsage->isNotEmpty()) {
            $this->newLine();
            $this->info('Recent Usage (Last 10):');
            
            $tableData = [];
            foreach ($recentUsage as $usage) {
                $tableData[] = [
                    $usage->created_at->format('Y-m-d H:i'),
                    $usage->payslip_count,
                    $usage->is_trial_usage ? 'Trial' : 'Paid',
                    'R ' . number_format($usage->amount_charged, 2),
                ];
            }
            
            $this->table(
                ['Date', 'Payslips', 'Type', 'Amount'],
                $tableData
            );
        }
        
        return 0;
    }

    /**
     * Show billing summary for all company users
     */
    protected function showAllUsersStatus(): int
    {
        $users = User::where('type', 'company')
            ->withCount(['billingCycles', 'invoices', 'payslipUsages'])
            ->get();
        
        if ($users->isEmpty()) {
            $this->info('No company users found');
            return 0;
        }
        
        $this->info('Billing Summary for All Company Users');
        $this->newLine();
        
        $tableData = [];
        foreach ($users as $user) {
            $cycleSummary = $this->billingService->getCurrentCycleSummary($user);
            
            $tableData[] = [
                $user->id,
                $user->name,
                $user->billing_status ?? 'active',
                $user->billing_cycles_count,
                $user->invoices_count,
                $cycleSummary['payslips_count'],
                'R ' . number_format($cycleSummary['estimated_total'], 2),
            ];
        }
        
        $this->table(
            ['ID', 'Name', 'Status', 'Cycles', 'Invoices', 'Current Payslips', 'Est. Bill'],
            $tableData
        );
        
        return 0;
    }

    /**
     * Show system-wide billing statistics
     */
    protected function showSystemStatistics(): int
    {
        $this->info('System-Wide Billing Statistics');
        $this->newLine();
        
        // Billing settings
        $settings = BillingSetting::first();
        
        if ($settings) {
            $this->info('Billing Configuration:');
            $this->table(
                ['Setting', 'Value'],
                [
                    ['Base Rate per Payslip', 'R ' . number_format($settings->base_rate_per_payslip ?? 0, 2)],
                    ['Minimum Monthly Fee', 'R ' . number_format($settings->minimum_monthly_fee ?? 0, 2)],
                    ['VAT Rate', ($settings->vat_rate ?? 15) . '%'],
                    ['Trial Days', $settings->trial_days ?? 14],
                    ['Trial Payslips', $settings->trial_payslips ?? 10],
                    ['Grace Period Days', $settings->grace_period_days ?? 7],
                ]
            );
        }
        
        // Usage statistics
        $this->newLine();
        $this->info('Usage Statistics:');
        
        $totalUsers = User::where('type', 'company')->count();
        $activeTrials = User::where('type', 'company')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now())
            ->count();
        
        $totalPayslipsThisMonth = PayslipUsage::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('payslip_count');
        
        $totalRevenueThisMonth = PayslipUsage::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('is_trial_usage', false)
            ->sum('amount_charged');
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Company Users', $totalUsers],
                ['Active Trials', $activeTrials],
                ['Payslips This Month', $totalPayslipsThisMonth],
                ['Revenue This Month', 'R ' . number_format($totalRevenueThisMonth, 2)],
            ]
        );
        
        // Invoice statistics
        $this->newLine();
        $this->info('Invoice Statistics:');
        
        $pendingInvoices = \App\Models\Invoice::whereIn('status', ['draft', 'sent'])->count();
        $overdueInvoices = \App\Models\Invoice::where('status', 'sent')
            ->whereDate('due_date', '<', now())
            ->count();
        $paidInvoicesThisMonth = \App\Models\Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->count();
        $paidAmountThisMonth = \App\Models\Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->sum('total_amount');
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Pending Invoices', $pendingInvoices],
                ['Overdue Invoices', $overdueInvoices],
                ['Paid This Month', $paidInvoicesThisMonth],
                ['Collected This Month', 'R ' . number_format($paidAmountThisMonth, 2)],
            ]
        );
        
        return 0;
    }
}
