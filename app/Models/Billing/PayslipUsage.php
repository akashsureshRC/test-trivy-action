<?php

namespace App\Models\Billing;

use App\Models\User;
use App\Models\WorkSpace;
use App\Models\Hrm\PaySlip;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayslipUsage extends Model
{
    use \App\Models\Concerns\BelongsToWorkspace;

    use HasFactory;

    protected $fillable = [
        'user_id',
        'workspace_id',
        'billing_cycle_id',
        'payslip_id',
        'employee_id',
        'salary_month',
        'amount_charged',
        'tier_id',
        'cumulative_count',
        'status',
    ];

    protected $casts = [
        'amount_charged' => 'decimal:2',
        'cumulative_count' => 'integer',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_INVOICED = 'invoiced';
    const STATUS_PAID = 'paid';

    /**
     * Get the user (company owner) that owns this usage record
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the workspace this usage belongs to
     */
    public function workspace()
    {
        return $this->belongsTo(WorkSpace::class);
    }

    /**
     * Get the billing cycle this usage is associated with
     */
    public function billingCycle()
    {
        return $this->belongsTo(BillingCycle::class);
    }

    /**
     * Get the payslip that generated this usage
     */
    public function payslip()
    {
        return $this->belongsTo(PaySlip::class);
    }

    /**
     * Get the tier that was applied for this usage
     */
    public function tier()
    {
        return $this->belongsTo(BillingTier::class);
    }

    /**
     * Scope for pending usages
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for invoiced usages
     */
    public function scopeInvoiced($query)
    {
        return $query->where('status', self::STATUS_INVOICED);
    }

    /**
     * Scope for paid usages
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Scope for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for a specific workspace
     */
    public function scopeForWorkspace($query, $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }

    /**
     * Scope for a specific billing cycle
     */
    public function scopeForBillingCycle($query, $billingCycleId)
    {
        return $query->where('billing_cycle_id', $billingCycleId);
    }

    /**
     * Scope for a specific month
     */
    public function scopeForMonth($query, $salaryMonth)
    {
        return $query->where('salary_month', $salaryMonth);
    }

    /**
     * Get usage count for a user in the current billing cycle
     */
    public static function getCurrentCycleCount($userId, $billingCycleId = null)
    {
        $query = self::where('user_id', $userId);
        
        if ($billingCycleId) {
            $query->where('billing_cycle_id', $billingCycleId);
        }
        
        return $query->count();
    }

    /**
     * Get total amount charged for a user in a billing cycle
     */
    public static function getTotalChargedAmount($userId, $billingCycleId)
    {
        return self::where('user_id', $userId)
            ->where('billing_cycle_id', $billingCycleId)
            ->sum('amount_charged');
    }

    /**
     * Mark usages as invoiced for a billing cycle
     */
    public static function markAsInvoiced($userId, $billingCycleId)
    {
        return self::where('user_id', $userId)
            ->where('billing_cycle_id', $billingCycleId)
            ->where('status', self::STATUS_PENDING)
            ->update(['status' => self::STATUS_INVOICED]);
    }

    /**
     * Mark usages as paid for an invoice
     */
    public static function markAsPaid($userId, $billingCycleId)
    {
        return self::where('user_id', $userId)
            ->where('billing_cycle_id', $billingCycleId)
            ->where('status', self::STATUS_INVOICED)
            ->update(['status' => self::STATUS_PAID]);
    }
}
