<?php

namespace App\Models\Billing;

use App\Models\User;
use App\Models\Billing\PayslipUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    protected $table = 'invoices';

    protected $fillable = [
        'user_id',
        'billing_cycle_id',
        'invoice_number',
        'period_start',
        'period_end',
        'total_payslips',
        'subtotal',
        'tax_amount',
        'tax_percentage',
        'total_amount',
        'status',
        'issue_date',
        'due_date',
        'paid_at',
        'sent_at',
        'notes',
        'last_reminder_sent_at',
        'reminder_count',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'issue_date' => 'date',
        'due_date' => 'date',
        'total_payslips' => 'integer',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'sent_at' => 'datetime',
        'last_reminder_sent_at' => 'datetime',
        'reminder_count' => 'integer',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the user that owns this invoice
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the billing cycle for this invoice
     */
    public function billingCycle(): BelongsTo
    {
        return $this->belongsTo(BillingCycle::class);
    }

    /**
     * Get the invoice items
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('tier_sort_order');
    }

    /**
     * Get payments for this invoice
     */
    public function payments(): HasMany
    {
        return $this->hasMany(BillingPayment::class);
    }

    /**
     * Get bank transfer proof submissions for this invoice
     */
    public function bankTransferSubmissions(): HasMany
    {
        return $this->hasMany(BankTransferPayment::class);
    }

    /**
     * Get the successful payment
     */
    public function successfulPayment(): HasOne
    {
        return $this->hasOne(BillingPayment::class)->where('status', 'completed');
    }

    /**
     * Generate unique invoice number
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = BillingSetting::getInvoicePrefix();
        $year = now()->format('Y');
        $month = now()->format('m');
        
        $lastInvoice = self::where('invoice_number', 'like', "{$prefix}-{$year}{$month}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $newNumber);
    }

    /**
     * Create invoice from billing cycle
     */
    public static function createFromBillingCycle(BillingCycle $cycle): self
    {
        $priceCalculation = BillingTier::calculateCumulativePrice($cycle->total_payslips);
        
        $subtotal = $priceCalculation['subtotal'];
        $taxPercentage = BillingSetting::isTaxEnabled() ? BillingSetting::getTaxPercentage() : 0;
        $taxAmount = $subtotal * ($taxPercentage / 100);
        $totalAmount = $subtotal + $taxAmount;
        
        $dueDays = BillingSetting::getDueDays();

        $invoice = self::create([
            'user_id' => $cycle->user_id,
            'billing_cycle_id' => $cycle->id,
            'invoice_number' => self::generateInvoiceNumber(),
            'period_start' => $cycle->period_start,
            'period_end' => $cycle->period_end,
            'total_payslips' => $cycle->total_payslips,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'tax_percentage' => $taxPercentage,
            'total_amount' => $totalAmount,
            'status' => self::STATUS_PENDING,
            'issue_date' => now(),
            'due_date' => now()->addDays($dueDays),
        ]);

        // Create invoice items from breakdown
        foreach ($priceCalculation['breakdown'] as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => "{$item['tier_name']}: {$item['quantity']} payslips @ " . BillingSetting::getCurrencySymbol() . number_format($item['unit_price'], 2),
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'amount' => $item['amount'],
                'tier_name' => $item['tier_name'],
                'tier_sort_order' => $item['sort_order'],
            ]);
        }

        // Mark billing cycle as invoiced
        $cycle->markAsInvoiced();

        return $invoice;
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(): void
    {
        // Check if this is the user's first payment (trial conversion)
        $isFirstPayment = self::where('user_id', $this->user_id)
            ->where('status', self::STATUS_PAID)
            ->where('id', '!=', $this->id)
            ->doesntExist();

        $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
        ]);

        // Mark associated payslip usages as paid
        if ($this->billing_cycle_id) {
            PayslipUsage::markAsPaid($this->user_id, $this->billing_cycle_id);
        }

        // Update user billing status
        if ($this->user) {
            // Reactivate if suspended
            if ($this->user->billing_status === 'suspended') {
                $this->user->reinstate();
            }
            // Convert from trial to active if this is first payment
            elseif ($this->user->billing_status === 'trial' || $isFirstPayment) {
                $this->user->forceFill([
                    'billing_status' => 'active',
                ])->save();
                
                // Send trial converted email for first-time payment
                if ($isFirstPayment) {
                    try {
                        $payment = $this->payments()->where('status', 'completed')->latest()->first();
                        \Illuminate\Support\Facades\Mail::to($this->user->email)
                            ->send(new \App\Mail\Billing\TrialConverted($this->user, $this, $payment));
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Failed to send trial converted email', [
                            'user_id' => $this->user_id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Mark invoice as sent
     */
    public function markAsSent(): void
    {
        $this->update([
            'sent_at' => now(),
        ]);
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status !== self::STATUS_PAID 
            && $this->status !== self::STATUS_CANCELLED
            && $this->due_date->isPast();
    }

    /**
     * Check if invoice is paid
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Get formatted total
     */
    public function getFormattedTotalAttribute(): string
    {
        return BillingSetting::getCurrencySymbol() . number_format($this->total_amount, 2);
    }

    /**
     * Get display status (shows 'past due' for overdue invoices)
     */
    public function getStatusDisplayAttribute(): string
    {
        if ($this->status === self::STATUS_PENDING && $this->isOverdue()) {
            return 'past due';
        }
        
        return $this->status;
    }

    /**
     * Get period label
     */
    public function getPeriodLabelAttribute(): string
    {
        return $this->period_start->format('M d, Y') . ' - ' . $this->period_end->format('M d, Y');
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        $status = $this->status_display;
        
        return match($status) {
            'pending' => 'bg-warning',
            'past due' => 'bg-danger',
            'paid' => 'bg-success',
            'cancelled' => 'bg-dark',
            default => 'bg-secondary',
        };
    }

    /**
     * Scope for pending invoices
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for unpaid invoices (alias for pending)
     */
    public function scopeUnpaid($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for overdue invoices (pending + past due date)
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where('due_date', '<', now());
    }
}
