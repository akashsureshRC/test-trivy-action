<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\BillingCycle;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:generate-invoices 
                            {--user= : Generate invoice for a specific user ID}
                            {--all : Generate invoices for all eligible users}
                            {--close-cycles : Close all completed billing cycles and generate invoices}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate invoices for billing cycles';

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
        $this->info('Starting invoice generation...');
        
        if ($this->option('close-cycles')) {
            return $this->closeCyclesAndGenerateInvoices();
        }
        
        if ($userId = $this->option('user')) {
            return $this->generateForUser($userId);
        }
        
        if ($this->option('all')) {
            return $this->generateForAllUsers();
        }
        
        $this->error('Please specify --user=ID, --all, or --close-cycles option');
        return 1;
    }

    /**
     * Generate invoice for a specific user
     */
    protected function generateForUser(int $userId): int
    {
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("User with ID {$userId} not found");
            return 1;
        }
        
        $this->info("Generating invoice for user: {$user->name} ({$user->email})");
        
        try {
            $invoice = $this->invoiceService->generateForUser($user);
            
            if ($invoice) {
                $this->info("✓ Invoice #{$invoice->invoice_number} generated successfully");
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['Invoice Number', $invoice->invoice_number],
                        ['Payslip Count', $invoice->total_payslips],
                        ['Subtotal', 'R ' . number_format($invoice->subtotal, 2)],
                        ['VAT', 'R ' . number_format($invoice->tax_amount, 2)],
                        ['Total', 'R ' . number_format($invoice->total_amount, 2)],
                        ['Due Date', $invoice->due_date->format('Y-m-d')],
                    ]
                );
                return 0;
            } else {
                $this->warn('No invoice generated (no billable usage or already invoiced)');
                return 0;
            }
        } catch (\Exception $e) {
            $this->error("Error generating invoice: {$e->getMessage()}");
            Log::error('Invoice generation failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Generate invoices for all eligible users
     */
    protected function generateForAllUsers(): int
    {
        // Get all company owners with active billing cycles
        $users = User::where('type', 'company')
            ->whereHas('billingCycles', function ($query) {
                $query->where('status', 'active')
                    ->whereDate('end_date', '<=', now());
            })
            ->get();
        
        if ($users->isEmpty()) {
            $this->info('No users with completed billing cycles found');
            return 0;
        }
        
        $this->info("Found {$users->count()} users with completed billing cycles");
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();
        
        $successCount = 0;
        $errorCount = 0;
        $skippedCount = 0;
        
        foreach ($users as $user) {
            try {
                $invoice = $this->invoiceService->generateForUser($user);
                
                if ($invoice) {
                    $successCount++;
                } else {
                    $skippedCount++;
                }
            } catch (\Exception $e) {
                $errorCount++;
                Log::error('Invoice generation failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("Invoice Generation Summary:");
        $this->table(
            ['Status', 'Count'],
            [
                ['Generated', $successCount],
                ['Skipped', $skippedCount],
                ['Errors', $errorCount],
            ]
        );
        
        return $errorCount > 0 ? 1 : 0;
    }

    /**
     * Close all completed billing cycles and generate invoices
     */
    protected function closeCyclesAndGenerateInvoices(): int
    {
        $this->info('Closing completed billing cycles and generating invoices...');
        
        try {
            $invoices = $this->invoiceService->closeAndInvoiceActiveCycles();
            
            if (empty($invoices)) {
                $this->info('No billing cycles to close');
                return 0;
            }
            
            $this->info("Generated " . count($invoices) . " invoice(s)");
            
            $tableData = [];
            foreach ($invoices as $invoice) {
                $tableData[] = [
                    $invoice->invoice_number,
                    $invoice->user->name ?? 'N/A',
                    $invoice->payslip_count,
                    'R ' . number_format($invoice->total_amount, 2),
                    $invoice->due_date->format('Y-m-d'),
                ];
            }
            
            $this->table(
                ['Invoice #', 'User', 'Payslips', 'Total', 'Due Date'],
                $tableData
            );
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            Log::error('Close cycles failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
