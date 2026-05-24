<?php

namespace App\Services;

use App\Mail\Billing\InvoiceGenerated;
use App\Mail\Billing\PaymentReceived;
use App\Mail\Billing\PaymentReminder;
use App\Mail\Billing\AccountSuspended;
use App\Models\User;
use App\Models\Billing\BillingCycle;
use App\Models\Billing\BillingPayment;
use App\Models\Billing\BillingSetting;
use App\Models\Billing\Invoice;
use App\Models\Billing\PayslipUsage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoiceService
{
    /**
     * Generate invoice for a billing cycle
     */
    public function generateInvoice(BillingCycle $cycle): ?Invoice
    {
        // Don't generate invoice if no payslips
        if ($cycle->total_payslips <= 0) {
            Log::info('InvoiceService: No payslips in cycle, skipping invoice generation', [
                'billing_cycle_id' => $cycle->id,
                'user_id' => $cycle->user_id,
            ]);
            return null;
        }

        // Don't generate if already invoiced
        if ($cycle->status === BillingCycle::STATUS_INVOICED) {
            Log::warning('InvoiceService: Cycle already invoiced', ['billing_cycle_id' => $cycle->id]);
            return null;
        }

        return DB::transaction(function () use ($cycle) {
            $invoice = Invoice::createFromBillingCycle($cycle);
            
            // Mark payslip usages as invoiced
            PayslipUsage::markAsInvoiced($cycle->user_id, $cycle->id);

            Log::info('InvoiceService: Invoice generated', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'user_id' => $cycle->user_id,
                'total_amount' => $invoice->total_amount,
            ]);

            return $invoice;
        });
    }

    /**
     * Close billing cycle and generate invoice
     */
    public function closeCycleAndInvoice(BillingCycle $cycle): ?Invoice
    {
        return DB::transaction(function () use ($cycle) {
            // Close the cycle
            $cycle->close();
            
            // Generate invoice
            return $this->generateInvoice($cycle);
        });
    }

    /**
     * Generate invoice for a specific user
     */
    public function generateForUser(User $user): ?Invoice
    {
        $cycle = BillingCycle::where('user_id', $user->id)
            ->where('status', BillingCycle::STATUS_ACTIVE)
            ->where('period_end', '<=', now())
            ->first();

        if (!$cycle) {
            return null;
        }

        $invoice = $this->closeCycleAndInvoice($cycle);
        
        if ($invoice) {
            $this->sendInvoiceEmail($invoice);
        }
        
        return $invoice;
    }

    /**
     * Process all cycles due for invoicing
     * 
     * @param bool $forceAll If true, process all active cycles with payslips regardless of period_end date
     */
    public function processAllDueCycles(bool $forceAll = false): array
    {
        $processedCount = 0;
        $invoices = [];
        $errors = [];

        // Find all active cycles that have ended (or all active cycles if forced)
        $query = BillingCycle::where('status', BillingCycle::STATUS_ACTIVE);
        
        if (!$forceAll) {
            // Only process cycles that have ended
            $query->where('period_end', '<', now());
        } else {
            // When forcing, only process cycles that have payslips
            $query->where('total_payslips', '>', 0);
        }
        
        $cycles = $query->get();

        foreach ($cycles as $cycle) {
            try {
                $invoice = $this->closeCycleAndInvoice($cycle);
                if ($invoice) {
                    // Send invoice email to customer
                    $this->sendInvoiceEmail($invoice);
                    
                    $invoices[] = $invoice;
                    $processedCount++;
                }
            } catch (\Exception $e) {
                Log::error('InvoiceService: Failed to process cycle', [
                    'billing_cycle_id' => $cycle->id,
                    'error' => $e->getMessage(),
                ]);
                $errors[] = ['cycle_id' => $cycle->id, 'error' => $e->getMessage()];
            }
        }

        return [
            'processed' => $processedCount,
            'invoices' => $invoices,
            'errors' => $errors,
        ];
    }

    /**
     * Process all cycles due for invoicing and return invoices
     */
    public function closeAndInvoiceActiveCycles(): array
    {
        $result = $this->processAllDueCycles();
        return $result['invoices'];
    }

    /**
     * Mark overdue invoices
     */
    public function markOverdueInvoices(): int
    {
        $count = 0;

        $invoices = Invoice::where('status', Invoice::STATUS_PENDING)
            ->where('due_date', '<', now())
            ->get();

        foreach ($invoices as $invoice) {
            $invoice->markAsOverdue();
            $count++;

            // Optionally suspend user after grace period
            $this->handleOverdueInvoice($invoice);
        }

        return $count;
    }

    /**
     * Handle overdue invoice actions
     */
    protected function handleOverdueInvoice(Invoice $invoice): void
    {
        $user = $invoice->user;
        $daysPastDue = now()->diffInDays($invoice->due_date);

        // Check suspension threshold (e.g., 7 days past due)
        $suspendAfterDays = (int) BillingSetting::get('suspend_after_days', 7);

        if ($daysPastDue >= $suspendAfterDays && !$user->suspended_at) {
            $user->suspend('Overdue invoice: ' . $invoice->invoice_number);
            
            Log::warning('InvoiceService: User suspended for overdue invoice', [
                'user_id' => $user->id,
                'invoice_id' => $invoice->id,
                'days_overdue' => $daysPastDue,
            ]);
        }
    }

    /**
     * Create a payment for an invoice
     */
    public function createPayment(Invoice $invoice, string $method = BillingPayment::METHOD_PAYFAST): BillingPayment
    {
        return BillingPayment::create([
            'invoice_id' => $invoice->id,
            'user_id' => $invoice->user_id,
            'payment_number' => BillingPayment::generatePaymentNumber(),
            'amount' => $invoice->total_amount,
            'currency' => 'ZAR',
            'payment_method' => $method,
            'status' => BillingPayment::STATUS_PENDING,
        ]);
    }

    /**
     * Process successful payment
     */
    public function processSuccessfulPayment(
        BillingPayment $payment, 
        string $gatewayReference, 
        string $gatewayStatus = 'COMPLETE',
        array $gatewayResponse = []
    ): void {
        DB::transaction(function () use ($payment, $gatewayReference, $gatewayStatus, $gatewayResponse) {
            // Update payment
            $payment->update([
                'status' => BillingPayment::STATUS_COMPLETED,
                'paid_at' => now(),
                'gateway_reference' => $gatewayReference,
                'gateway_status' => $gatewayStatus,
                'gateway_response' => $gatewayResponse,
            ]);

            // Mark invoice as paid
            $invoice = $payment->invoice;
            $invoice->markAsPaid();

            // Reinstate user if suspended
            $user = $payment->user;
            if ($user->billing_status === 'suspended') {
                $user->reinstate();
            }

            Log::info('InvoiceService: Payment processed successfully', [
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'gateway_reference' => $gatewayReference,
            ]);
            
            // Send payment confirmation email
            $this->sendPaymentConfirmationEmail($invoice, $payment);
        });
    }

    /**
     * Process failed payment
     */
    public function processFailedPayment(BillingPayment $payment, string $reason = null, array $gatewayResponse = []): void
    {
        $payment->update([
            'status' => BillingPayment::STATUS_FAILED,
            'gateway_response' => $gatewayResponse,
            'notes' => $reason,
        ]);

        Log::warning('InvoiceService: Payment failed', [
            'payment_id' => $payment->id,
            'invoice_id' => $payment->invoice_id,
            'reason' => $reason,
        ]);
    }

    /**
     * Get unpaid invoices for a user
     */
    public function getUnpaidInvoices(User $user)
    {
        return Invoice::where('user_id', $user->id)
            ->unpaid()
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get invoice summary for dashboard
     */
    public function getInvoiceSummary(User $user): array
    {
        $invoices = Invoice::where('user_id', $user->id)->get();

        return [
            'total_invoices' => $invoices->count(),
            'total_paid' => $invoices->where('status', Invoice::STATUS_PAID)->count(),
            'total_pending' => $invoices->where('status', Invoice::STATUS_PENDING)->count(),
            'total_overdue' => $invoices->filter(function($inv) { return $inv->status === 'pending' && $inv->due_date && $inv->due_date->isPast(); })->count(),
            'total_amount_paid' => $invoices->where('status', Invoice::STATUS_PAID)->sum('total_amount'),
            'total_amount_pending' => $invoices->where('status', Invoice::STATUS_PENDING)->sum('total_amount'),
        ];
    }

    /**
     * Send invoice email to user
     */
    public function sendInvoiceEmail(Invoice $invoice): bool
    {
        try {
            $user = $invoice->user;
            
            setAdminConfigEmail();
            Mail::to($user->email)->send(new InvoiceGenerated($invoice));
            
            $invoice->markAsSent();
            
            Log::info('InvoiceService: Invoice email sent', [
                'invoice_id' => $invoice->id,
                'user_email' => $user->email,
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('InvoiceService: Failed to send invoice email', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send payment confirmation email
     */
    public function sendPaymentConfirmationEmail(Invoice $invoice, BillingPayment $payment): bool
    {
        try {
            $user = $invoice->user;
            
            // Use admin's configured SMTP for billing communication
            setAdminConfigEmail();
            Mail::to($user->email)->send(new PaymentReceived($invoice, $payment));
            
            Log::info('InvoiceService: Payment confirmation email sent', [
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
                'user_email' => $user->email,
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('InvoiceService: Failed to send payment confirmation email', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send payment reminder email
     */
    public function sendPaymentReminderEmail(Invoice $invoice): bool
    {
        try {
            $user = $invoice->user;
            
            // Use admin's configured SMTP for billing communication
            setAdminConfigEmail();
            Mail::to($user->email)->send(new PaymentReminder($invoice));
            
            Log::info('InvoiceService: Payment reminder email sent', [
                'invoice_id' => $invoice->id,
                'user_email' => $user->email,
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('InvoiceService: Failed to send payment reminder email', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send account suspended email
     */
    public function sendAccountSuspendedEmail(User $user, ?Invoice $invoice = null): bool
    {
        try {
            // Use admin's configured SMTP for billing communication
            setAdminConfigEmail();
            Mail::to($user->email)->send(new AccountSuspended($user, $invoice));
            
            Log::info('InvoiceService: Account suspended email sent', [
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('InvoiceService: Failed to send account suspended email', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
