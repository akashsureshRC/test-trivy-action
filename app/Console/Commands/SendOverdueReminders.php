<?php

namespace App\Console\Commands;

use App\Mail\Billing\InvoiceOverdueReminder;
use App\Models\Billing\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOverdueReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:send-overdue-reminders 
                            {--days=* : Only send reminders for invoices overdue by specific days (e.g., 7, 14, 30)}
                            {--force : Send reminders regardless of last reminder date}
                            {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder emails for overdue invoices';

    /**
     * Default reminder schedule (days after due date).
     */
    protected array $defaultReminderDays = [1, 7, 14, 30];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting overdue invoice reminder process...');
        
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $specificDays = $this->option('days');
        
        // Get reminder days from options or use defaults
        $reminderDays = !empty($specificDays) ? array_map('intval', $specificDays) : $this->defaultReminderDays;
        
        // Get all unpaid invoices that are overdue
        $overdueInvoices = Invoice::with('user')
            ->where('status', 'pending')
            ->whereNotNull('due_date')
            ->where('due_date', '<', Carbon::today())
            ->get();

        if ($overdueInvoices->isEmpty()) {
            $this->info('No overdue invoices found.');
            return self::SUCCESS;
        }

        $this->info("Found {$overdueInvoices->count()} overdue invoices.");
        
        $sentCount = 0;
        $skippedCount = 0;

        foreach ($overdueInvoices as $invoice) {
            $daysOverdue = Carbon::today()->diffInDays(Carbon::parse($invoice->due_date));
            
            // Check if this invoice should receive a reminder today
            if (!$this->shouldSendReminder($invoice, $daysOverdue, $reminderDays, $force)) {
                $skippedCount++;
                if ($this->output->isVerbose()) {
                    $this->line("  Skipping {$invoice->invoice_number} ({$daysOverdue} days overdue) - not scheduled or already sent");
                }
                continue;
            }

            // Check if user has email
            if (!$invoice->user || !$invoice->user->email) {
                $this->warn("  No email for invoice {$invoice->invoice_number} - skipping");
                $skippedCount++;
                continue;
            }

            if ($dryRun) {
                $this->info("  [DRY RUN] Would send reminder to {$invoice->user->email} for invoice {$invoice->invoice_number} ({$daysOverdue} days overdue)");
                $sentCount++;
                continue;
            }

            try {
                setAdminConfigEmail();
                Mail::to($invoice->user->email)->send(new InvoiceOverdueReminder($invoice));
                
                // Update last reminder tracking
                $invoice->update([
                    'last_reminder_sent_at' => now(),
                    'reminder_count' => ($invoice->reminder_count ?? 0) + 1,
                ]);

                $this->info("  ✓ Sent reminder to {$invoice->user->email} for invoice {$invoice->invoice_number} ({$daysOverdue} days overdue)");
                $sentCount++;

                Log::info('Overdue reminder sent', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'user_email' => $invoice->user->email,
                    'days_overdue' => $daysOverdue,
                ]);

            } catch (\Exception $e) {
                $this->error("  ✗ Failed to send reminder for invoice {$invoice->invoice_number}: {$e->getMessage()}");
                
                Log::error('Failed to send overdue reminder', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info("Overdue reminder process completed:");
        $this->info("  - Sent: {$sentCount}");
        $this->info("  - Skipped: {$skippedCount}");
        
        return self::SUCCESS;
    }

    /**
     * Determine if a reminder should be sent for this invoice today.
     */
    protected function shouldSendReminder(Invoice $invoice, int $daysOverdue, array $reminderDays, bool $force): bool
    {
        // If force flag is set, always send
        if ($force) {
            return true;
        }

        // Check if days overdue matches our reminder schedule
        $matchesSchedule = in_array($daysOverdue, $reminderDays);
        
        // Also send reminders for milestones beyond our schedule (every 30 days after 30)
        if (!$matchesSchedule && $daysOverdue > 30 && $daysOverdue % 30 === 0) {
            $matchesSchedule = true;
        }

        if (!$matchesSchedule) {
            return false;
        }

        // Check if we already sent a reminder today
        if ($invoice->last_reminder_sent_at) {
            $lastSent = Carbon::parse($invoice->last_reminder_sent_at);
            if ($lastSent->isToday()) {
                return false;
            }
        }

        return true;
    }
}
