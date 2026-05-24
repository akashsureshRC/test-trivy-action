<?php

namespace App\Models\Billing;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BillingCycle extends Model
{
    protected $table = 'billing_cycles';

    protected $fillable = [
        'user_id',
        'period_start',
        'period_end',
        'status',
        'total_payslips',
        'closed_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_payslips' => 'integer',
        'closed_at' => 'datetime',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';
    const STATUS_INVOICED = 'invoiced';

    /**
     * Get the user that owns this billing cycle
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the invoice for this billing cycle
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Get the active billing cycle for a user
     */
    public static function getActiveForUser(int $userId): ?self
    {
        return self::where('user_id', $userId)
            ->where('status', self::STATUS_ACTIVE)
            ->first();
    }

    /**
     * Create or get active billing cycle for user
     */
    public static function getOrCreateForUser(int $userId): self
    {
        $active = self::getActiveForUser($userId);
        
        if ($active) {
            return $active;
        }

        // Create new billing cycle - Always starts on the 1st of the current month
        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfMonth();

        return self::create([
            'user_id' => $userId,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'status' => self::STATUS_ACTIVE,
            'total_payslips' => 0,
        ]);
    }

    /**
     * Increment payslip count
     */
    public function incrementPayslips(int $count = 1): void
    {
        $this->increment('total_payslips', $count);
    }

    /**
     * Close the billing cycle
     */
    public function close(): void
    {
        $this->update([
            'status' => self::STATUS_CLOSED,
            'closed_at' => now(),
        ]);
    }

    /**
     * Mark as invoiced
     */
    public function markAsInvoiced(): void
    {
        $this->update([
            'status' => self::STATUS_INVOICED,
        ]);
    }

    /**
     * Check if billing cycle is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get period label
     */
    public function getPeriodLabelAttribute(): string
    {
        return $this->period_start->format('M d, Y') . ' - ' . $this->period_end->format('M d, Y');
    }
}
