<?php

namespace App\Console\Commands;

use App\Models\Billing\Invoice;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckOverdueInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:check-overdue 
                            {--send-reminders : Send reminder emails for overdue invoices}
                            {--suspend : Suspend users with severely overdue invoices}
                            {--dry-run : Show what would happen without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for overdue invoices and optionally send reminders or suspend accounts';

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * Create a new command instance.
     */
    public function __construct(InvoiceService $invoiceService)
    {
        parent::__construct();
        $this->invoiceService = $invoiceService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for overdue invoices...');
        
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        
        $gracePeriodDays = \App\Models\Billing\BillingSetting::getGracePeriodDays();
        
        // Get all overdue invoices (pending status, past due date)
        $overdueInvoices = Invoice::where('status', 'pending')
            ->where('due_date', '<', now())
            ->with('user')
            ->get();
        
        if ($overdueInvoices->isEmpty()) {
            $this->info('No overdue invoices found');
            return 0;
        }
        
        $this->info("Found {$overdueInvoices->count()} overdue invoice(s)");
        
        // Categorize by severity based on grace period
        $mildlyOverdue = $overdueInvoices->filter(function ($invoice) use ($gracePeriodDays) {
            $daysOverdue = $invoice->due_date->diffInDays(now());
            return $daysOverdue <= ($gracePeriodDays / 2);
        });
        
        $moderatelyOverdue = $overdueInvoices->filter(function ($invoice) use ($gracePeriodDays) {
            $daysOverdue = $invoice->due_date->diffInDays(now());
            return $daysOverdue > ($gracePeriodDays / 2) && $daysOverdue < $gracePeriodDays;
        });
        
        $severelyOverdue = $overdueInvoices->filter(function ($invoice) use ($gracePeriodDays) {
            $daysOverdue = $invoice->due_date->diffInDays(now());
            return $daysOverdue >= $gracePeriodDays;
        });
        
        $this->table(
            ['Severity', 'Count', 'Days Overdue'],
            [
                ['Mild', $mildlyOverdue->count(), '1-' . floor($gracePeriodDays / 2) . ' days'],
                ['Moderate', $moderatelyOverdue->count(), floor($gracePeriodDays / 2 + 1) . '-' . ($gracePeriodDays - 1) . ' days'],
                ['Severe (Will Suspend)', $severelyOverdue->count(), $gracePeriodDays . '+ days (past grace period)'],
            ]
        );
        
        // Show detailed list
        $this->newLine();
        $this->info('Overdue Invoice Details:');
        
        $tableData = [];
        foreach ($overdueInvoices as $invoice) {
            $daysOverdue = $invoice->due_date->diffInDays(now());
            $tableData[] = [
                $invoice->invoice_number,
                $invoice->user->name ?? 'N/A',
                $invoice->user->email ?? 'N/A',
                'R ' . number_format($invoice->total_amount, 2),
                $invoice->due_date->format('Y-m-d'),
                $daysOverdue . ' days',
            ];
        }
        
        $this->table(
            ['Invoice #', 'User', 'Email', 'Amount', 'Due Date', 'Overdue'],
            $tableData
        );
        
        // Send reminders
        if ($this->option('send-reminders')) {
            $this->sendReminders($overdueInvoices, $isDryRun);
        }
        
        // Suspend accounts
        if ($this->option('suspend')) {
            $this->suspendAccounts($severelyOverdue, $isDryRun);
        }
        
        return 0;
    }

    /**
     * Send reminder emails for overdue invoices
     */
    protected function sendReminders($invoices, bool $isDryRun): void
    {
        $this->newLine();
        $this->info('Sending payment reminders...');
        
        $sentCount = 0;
        
        foreach ($invoices as $invoice) {
            $this->line(" - {$invoice->invoice_number} to {$invoice->user->email}");
            
            if (!$isDryRun) {
                try {
                    // Send reminder email
                    setAdminConfigEmail();
                    \Mail::to($invoice->user->email)->send(
                        new \App\Mail\Billing\PaymentReminder($invoice)
                    );
                    
                    // Log the reminder
                    Log::info('Payment reminder sent', [
                        'invoice_id' => $invoice->id,
                        'user_id' => $invoice->user_id,
                        'email' => $invoice->user->email
                    ]);
                    
                    $sentCount++;
                } catch (\Exception $e) {
                    $this->error("   Failed to send: {$e->getMessage()}");
                    Log::error('Payment reminder failed', [
                        'invoice_id' => $invoice->id,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                $sentCount++;
            }
        }
        
        $this->info("✓ " . ($isDryRun ? 'Would send' : 'Sent') . " {$sentCount} reminder(s)");
    }

    /**
     * Suspend accounts with severely overdue invoices
     */
    protected function suspendAccounts($invoices, bool $isDryRun): void
    {
        $this->newLine();
        $this->info('Processing account suspensions...');
        
        if ($invoices->isEmpty()) {
            $this->info('No severely overdue invoices to process');
            return;
        }
        
        $suspendedCount = 0;
        
        foreach ($invoices as $invoice) {
            $user = $invoice->user;
            
            if (!$user) {
                continue;
            }
            
            // Skip if already suspended
            if ($user->billing_status === 'suspended') {
                $this->line(" - {$user->email} already suspended");
                continue;
            }
            
            $this->line(" - Suspending {$user->email} (Invoice: {$invoice->invoice_number})");
            
            if (!$isDryRun) {
                try {
                    $user->suspend("Overdue invoice #{$invoice->invoice_number}");
                    
                    // Send suspension notification using admin's configured SMTP
                    setAdminConfigEmail();
                    \Mail::to($user->email)->send(
                        new \App\Mail\Billing\AccountSuspended($user, $invoice)
                    );
                    
                    Log::warning('User account suspended due to overdue payment', [
                        'user_id' => $user->id,
                        'invoice_id' => $invoice->id,
                        'days_overdue' => $invoice->due_date->diffInDays(now())
                    ]);
                    
                    $suspendedCount++;
                } catch (\Exception $e) {
                    $this->error("   Failed: {$e->getMessage()}");
                    Log::error('Account suspension failed', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                $suspendedCount++;
            }
        }
        
        $this->info("✓ " . ($isDryRun ? 'Would suspend' : 'Suspended') . " {$suspendedCount} account(s)");
    }
}
