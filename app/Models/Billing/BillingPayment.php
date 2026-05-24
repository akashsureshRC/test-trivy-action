<?php

namespace App\Models\Billing;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingPayment extends Model
{
    protected $table = 'billing_payments';

    protected $fillable = [
        'invoice_id',
        'user_id',
        'payment_number',
        'amount',
        'currency',
        'payment_method',
        'status',
        'gateway_reference',
        'gateway_status',
        'gateway_response',
        'payment_reference',
        'paid_at',
        'notes',
        'processed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    const METHOD_PAYFAST = 'payfast';
    const METHOD_MANUAL = 'manual';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_OTHER = 'other';

    // Display names for payment methods
    public static function getMethodDisplayName(string $method): string
    {
        return match($method) {
            self::METHOD_PAYFAST => 'PayFast',
            self::METHOD_MANUAL => 'Manual (Cash/Cheque/Card)',
            self::METHOD_BANK_TRANSFER => 'EFT / Bank Transfer',
            self::METHOD_OTHER => 'Other',
            default => ucfirst($method),
        };
    }

    /**
     * Get the invoice this payment is for
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the user who made this payment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get payment transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'billing_payment_id');
    }

    /**
     * Generate unique payment number
     */
    public static function generatePaymentNumber(): string
    {
        $prefix = 'PAY';
        $year = now()->format('Y');
        $month = now()->format('m');
        
        $lastPayment = self::where('payment_number', 'like', "{$prefix}-{$year}{$month}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastPayment) {
            $lastNumber = (int) substr($lastPayment->payment_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $newNumber);
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted(string $gatewayReference = null, string $gatewayStatus = null): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'paid_at' => now(),
            'gateway_reference' => $gatewayReference ?? $this->gateway_reference,
            'gateway_status' => $gatewayStatus ?? $this->gateway_status,
        ]);

        // Mark invoice as paid
        $this->invoice->markAsPaid();

        // Reinstate user if suspended
        $user = $this->user;
        if ($user->suspended_at) {
            $user->reinstate();
        }
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'notes' => $reason,
        ]);
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return BillingSetting::getCurrencySymbol() . number_format($this->amount, 2);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-warning',
            self::STATUS_COMPLETED => 'bg-success',
            self::STATUS_FAILED => 'bg-danger',
            self::STATUS_REFUNDED => 'bg-info',
            default => 'bg-secondary',
        };
    }

    /**
     * Scope for completed payments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}
