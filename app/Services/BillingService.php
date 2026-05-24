<?php

namespace App\Services;

use App\Models\User;
use App\Models\Hrm\PaySlip;
use App\Models\Billing\BillingCycle;
use App\Models\Billing\BillingSetting;
use App\Models\Billing\BillingTier;
use App\Models\Billing\Invoice;
use App\Models\Billing\InvoiceItem;
use App\Models\Billing\PayslipUsage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillingService
{
    /**
     * Track a processed payrun and record usage for billing
     * This is called when a Payment Run is executed on finalized payslips
     */
    public function trackPayrunProcessed(PaySlip $payslip, int $workspaceId): ?PayslipUsage
    {
        // Get the workspace owner (company owner)
        $user = $this->getWorkspaceOwner($workspaceId);
        
        if (!$user) {
            Log::warning('BillingService: No owner found for workspace', ['workspace_id' => $workspaceId]);
            return null;
        }

        // Check if billing is enabled
        if (!BillingSetting::isBillingEnabled()) {
            return null;
        }

        // Check if user is in trial and still has allowance
        if ($this->isUserInActiveTrial($user)) {
            $trialUsageCount = $this->getTrialPayslipsUsed($user);
            $trialLimit = BillingSetting::getTrialPayslipsLimit();
            
            if ($trialUsageCount >= $trialLimit) {
                // Trial payslips exhausted, need to bill
                return $this->recordBillableUsage($payslip, $user, $workspaceId);
            }
            
            // Still within trial - record but don't bill
            return $this->recordTrialUsage($payslip, $user, $workspaceId);
        }

        // User is not in trial or trial expired - bill normally
        return $this->recordBillableUsage($payslip, $user, $workspaceId);
    }

    /**
     * Record billable usage for a payslip
     */
    protected function recordBillableUsage(PaySlip $payslip, User $user, int $workspaceId): PayslipUsage
    {
        return DB::transaction(function () use ($payslip, $user, $workspaceId) {
            // Get or create active billing cycle
            $billingCycle = BillingCycle::getOrCreateForUser($user->id);
            
            // Get cumulative count for this user in this cycle
            $currentCount = PayslipUsage::where('user_id', $user->id)
                ->where('billing_cycle_id', $billingCycle->id)
                ->count();
            
            $cumulativeCount = $currentCount + 1;
            
            // Get the appropriate tier for this payslip position
            $tier = BillingTier::getTierForPayslip($cumulativeCount);
            $amountCharged = $tier ? $tier->price_per_payslip : 0;
            
            // Create usage record
            $usage = PayslipUsage::create([
                'user_id' => $user->id,
                'workspace_id' => $workspaceId,
                'billing_cycle_id' => $billingCycle->id,
                'payslip_id' => $payslip->id,
                'employee_id' => $payslip->employee_id,
                'salary_month' => $payslip->salary_month,
                'amount_charged' => $amountCharged,
                'tier_id' => $tier?->id,
                'cumulative_count' => $cumulativeCount,
                'status' => PayslipUsage::STATUS_PENDING,
            ]);
            
            // Increment billing cycle counter
            $billingCycle->incrementPayslips();
            
            // Update user's payslips count
            $user->increment('payslips_count');
            
            Log::info('BillingService: Recorded billable payslip usage', [
                'user_id' => $user->id,
                'payslip_id' => $payslip->id,
                'cumulative_count' => $cumulativeCount,
                'amount_charged' => $amountCharged,
                'tier' => $tier?->name,
            ]);
            
            return $usage;
        });
    }

    /**
     * Record trial usage for a payslip (no charge)
     */
    protected function recordTrialUsage(PaySlip $payslip, User $user, int $workspaceId): PayslipUsage
    {
        return DB::transaction(function () use ($payslip, $user, $workspaceId) {
            $trialCount = $this->getTrialPayslipsUsed($user) + 1;
            
            // Create usage record with zero charge
            $usage = PayslipUsage::create([
                'user_id' => $user->id,
                'workspace_id' => $workspaceId,
                'billing_cycle_id' => null, // No billing cycle for trial
                'payslip_id' => $payslip->id,
                'employee_id' => $payslip->employee_id,
                'salary_month' => $payslip->salary_month,
                'amount_charged' => 0,
                'tier_id' => null,
                'cumulative_count' => $trialCount,
                'status' => PayslipUsage::STATUS_PAID, // Trial is considered "paid"
            ]);
            
            // Update user's trial payslips used
            $user->increment('trial_payslips_used');
            
            Log::info('BillingService: Recorded trial payslip usage', [
                'user_id' => $user->id,
                'payslip_id' => $payslip->id,
                'trial_count' => $trialCount,
            ]);
            
            return $usage;
        });
    }

    /**
     * Check if user is currently in an active trial period
     */
    public function isUserInActiveTrial(User $user): bool
    {
        // Check if trial has been ended manually
        if (!$user->trial_ends_at) {
            return false;
        }

        $now = Carbon::now();
        $trialEnd = Carbon::parse($user->trial_ends_at);
        
        // Check if trial days haven't expired
        if ($now->greaterThan($trialEnd)) {
            return false;
        }

        // Check if trial payslips haven't been exhausted
        $trialLimit = BillingSetting::getTrialPayslipsLimit();
        $trialUsed = $user->trial_payslips_used ?? 0;
        
        return $trialUsed < $trialLimit;
    }

    /**
     * Get number of trial payslips used by user
     */
    public function getTrialPayslipsUsed(User $user): int
    {
        return $user->trial_payslips_used ?? 0;
    }

    /**
     * Get remaining trial payslips for user
     */
    public function getRemainingTrialPayslips(User $user): int
    {
        $trialLimit = BillingSetting::getTrialPayslipsLimit();
        $used = $this->getTrialPayslipsUsed($user);
        
        return max(0, $trialLimit - $used);
    }

    /**
     * Get remaining trial days for user
     */
    public function getRemainingTrialDays(User $user): int
    {
        if (!$user->trial_ends_at) {
            return 0;
        }

        $now = Carbon::now();
        $trialEnd = Carbon::parse($user->trial_ends_at);
        
        if ($now->greaterThan($trialEnd)) {
            return 0;
        }
        
        return $now->diffInDays($trialEnd);
    }

    /**
     * Get trial status summary for user
     */
    public function getTrialStatus(User $user): array
    {
        $isInTrial = $this->isUserInActiveTrial($user);
        $trialLimit = BillingSetting::getTrialPayslipsLimit();
        $trialDays = BillingSetting::getTrialDays();
        
        return [
            'is_in_trial' => $isInTrial,
            'trial_ends_at' => $user->trial_ends_at,
            'trial_days_remaining' => $this->getRemainingTrialDays($user),
            'trial_days_total' => $trialDays,
            'trial_payslips_used' => $this->getTrialPayslipsUsed($user),
            'trial_payslips_limit' => $trialLimit,
            'trial_payslips_remaining' => $this->getRemainingTrialPayslips($user),
            'trial_reason_ended' => $this->getTrialEndReason($user),
        ];
    }

    /**
     * Get reason why trial ended (or null if still active)
     */
    protected function getTrialEndReason(User $user): ?string
    {
        if ($this->isUserInActiveTrial($user)) {
            return null;
        }

        if (!$user->trial_ends_at) {
            return 'no_trial';
        }

        $now = Carbon::now();
        $trialEnd = Carbon::parse($user->trial_ends_at);
        
        if ($now->greaterThan($trialEnd)) {
            return 'days_expired';
        }

        $trialLimit = BillingSetting::getTrialPayslipsLimit();
        if (($user->trial_payslips_used ?? 0) >= $trialLimit) {
            return 'payslips_exhausted';
        }

        return 'unknown';
    }

    /**
     * Get billing summary for user's current cycle
     */
    public function getCurrentCycleSummary(User $user): array
    {
        $billingCycle = BillingCycle::getActiveForUser($user->id);
        
        if (!$billingCycle) {
            return [
                'has_active_cycle' => false,
                'total_payslips' => 0,
                'total_amount' => 0,
                'breakdown' => [],
            ];
        }

        $usages = PayslipUsage::where('user_id', $user->id)
            ->where('billing_cycle_id', $billingCycle->id)
            ->get();

        $totalAmount = $usages->sum('amount_charged');
        $totalPayslips = $usages->count();

        // Get breakdown by tier
        $breakdown = $usages->groupBy('tier_id')->map(function ($tierUsages, $tierId) {
            $tier = BillingTier::find($tierId);
            return [
                'tier_id' => $tierId,
                'tier_name' => $tier?->name ?? 'Unknown',
                'quantity' => $tierUsages->count(),
                'unit_price' => $tier?->price_per_payslip ?? 0,
                'amount' => $tierUsages->sum('amount_charged'),
            ];
        })->values()->toArray();

        return [
            'has_active_cycle' => true,
            'billing_cycle_id' => $billingCycle->id,
            'period_start' => $billingCycle->period_start->format('Y-m-d'),
            'period_end' => $billingCycle->period_end->format('Y-m-d'),
            'period_label' => $billingCycle->period_label,
            'total_payslips' => $totalPayslips,
            'subtotal' => $totalAmount,
            'tax_amount' => $this->calculateTax($totalAmount),
            'total_amount' => $totalAmount + $this->calculateTax($totalAmount),
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Calculate tax amount
     */
    public function calculateTax(float $amount): float
    {
        if (!BillingSetting::isTaxEnabled()) {
            return 0;
        }

        $taxPercentage = BillingSetting::getTaxPercentage();
        return round($amount * ($taxPercentage / 100), 2);
    }

    /**
     * Get usage history for user
     */
    public function getUsageHistory(User $user, int $limit = 100): array
    {
        $usages = PayslipUsage::where('user_id', $user->id)
            ->with(['payslip', 'tier', 'billingCycle', 'workspace'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $usages->map(function ($usage) {
            return [
                'id' => $usage->id,
                'payslip_id' => $usage->payslip_id,
                'employee_id' => $usage->employee_id,
                'salary_month' => $usage->salary_month,
                'workspace_name' => $usage->workspace?->name ?? 'Unknown',
                'tier_name' => $usage->tier?->name ?? 'Trial',
                'amount_charged' => $usage->amount_charged,
                'cumulative_count' => $usage->cumulative_count,
                'status' => $usage->status,
                'created_at' => $usage->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    /**
     * Calculate estimated bill for given number of payslips
     */
    public function calculateEstimatedBill(int $payslipCount): array
    {
        $calculation = BillingTier::calculateCumulativePrice($payslipCount);
        
        $taxAmount = $this->calculateTax($calculation['subtotal']);
        
        return [
            'total_payslips' => $payslipCount,
            'subtotal' => $calculation['subtotal'],
            'tax_amount' => $taxAmount,
            'tax_enabled' => BillingSetting::isTaxEnabled(),
            'tax_percentage' => BillingSetting::getTaxPercentage(),
            'total' => $calculation['subtotal'] + $taxAmount,
            'breakdown' => $calculation['breakdown'],
            'currency_symbol' => BillingSetting::getCurrencySymbol(),
        ];
    }

    /**
     * Get workspace owner (company owner user)
     */
    protected function getWorkspaceOwner(int $workspaceId): ?User
    {
        // Get the workspace
        $workspace = \App\Models\WorkSpace::find($workspaceId);
        
        if (!$workspace) {
            return null;
        }

        // Return the creator of the workspace (company owner)
        return User::find($workspace->created_by);
    }

    /**
     * Initialize trial for a new user
     */
    public function initializeTrial(User $user): void
    {
        $trialDays = BillingSetting::getTrialDays();
        $trialPayslipsLimit = BillingSetting::getTrialPayslipsLimit();
        
        $user->forceFill([
            'trial_ends_at' => Carbon::now()->addDays($trialDays),
            'trial_payslips_used' => 0,
            'trial_payslips_limit' => $trialPayslipsLimit,
            'billing_status' => 'trial',
        ])->save();

        Log::info('BillingService: Trial initialized for user', [
            'user_id' => $user->id,
            'trial_ends_at' => $user->trial_ends_at,
            'trial_days' => $trialDays,
            'trial_payslips_limit' => $trialPayslipsLimit,
        ]);
    }

    /**
     * End trial for user (convert to billable)
     */
    public function endTrial(User $user): void
    {
        $user->forceFill([
            'trial_ends_at' => Carbon::now(),
            'billing_status' => 'active',
        ])->save();

        Log::info('BillingService: Trial ended for user', ['user_id' => $user->id]);
    }

    /**
     * Check if user has overdue invoices
     */
    public function hasOverdueInvoices(User $user): bool
    {
        return Invoice::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->exists();
    }

    /**
     * Get user's billing status details
     */
    public function getBillingStatus(User $user): array
    {
        $trialStatus = $this->getTrialStatus($user);
        $currentCycle = $this->getCurrentCycleSummary($user);
        $hasOverdue = $this->hasOverdueInvoices($user);
        
        // Determine if user can generate payslips
        $canGeneratePayslips = $this->canUserGeneratePayslips($user, $trialStatus, $hasOverdue);

        return [
            'billing_enabled' => BillingSetting::isBillingEnabled(),
            'user_billing_status' => $user->billing_status ?? 'trial',
            'is_in_trial' => $trialStatus['is_in_trial'],
            'trial' => $trialStatus,
            'current_cycle' => $currentCycle,
            'has_overdue_invoices' => $hasOverdue,
            'can_generate_payslips' => $canGeneratePayslips,
            'payslip_block_reason' => $this->getPayslipBlockReason($user, $trialStatus, $hasOverdue),
            'total_payslips_generated' => $user->payslips_count ?? 0,
        ];
    }

    /**
     * Check if user can generate payslips
     */
    public function canUserGeneratePayslips(User $user, ?array $trialStatus = null, ?bool $hasOverdue = null): bool
    {
        // If billing is disabled, allow everything
        if (!BillingSetting::isBillingEnabled()) {
            return true;
        }

        // Get trial status if not provided
        if ($trialStatus === null) {
            $trialStatus = $this->getTrialStatus($user);
        }
        
        // Get overdue status if not provided
        if ($hasOverdue === null) {
            $hasOverdue = $this->hasOverdueInvoices($user);
        }

        // If user is on active trial, they can generate
        if ($trialStatus['is_in_trial']) {
            return true;
        }

        // If user has overdue invoices, block
        if ($hasOverdue) {
            return false;
        }

        // If user is suspended, block
        if ($user->billing_status === 'suspended') {
            return false;
        }

        // If user is active (has made at least one payment), allow
        if ($user->billing_status === 'active') {
            return true;
        }

        // If trial has expired and no payment made yet, block
        // This handles the "expired (no action)" state
        if ($this->hasTrialExpired($user)) {
            return false;
        }

        return true;
    }

    /**
     * Check if user's trial has expired
     */
    public function hasTrialExpired(User $user): bool
    {
        // If never had a trial
        if (!$user->trial_ends_at && ($user->trial_payslips_used ?? 0) == 0) {
            return false;
        }

        // Check time expiry
        if ($user->trial_ends_at && Carbon::parse($user->trial_ends_at)->isPast()) {
            return true;
        }

        // Check payslip limit expiry
        $trialLimit = BillingSetting::getTrialPayslipsLimit();
        if (($user->trial_payslips_used ?? 0) >= $trialLimit) {
            return true;
        }

        return false;
    }

    /**
     * Get the reason why payslip generation is blocked
     */
    public function getPayslipBlockReason(User $user, ?array $trialStatus = null, ?bool $hasOverdue = null): ?string
    {
        if (!BillingSetting::isBillingEnabled()) {
            return null;
        }

        if ($trialStatus === null) {
            $trialStatus = $this->getTrialStatus($user);
        }
        
        if ($hasOverdue === null) {
            $hasOverdue = $this->hasOverdueInvoices($user);
        }

        // Active trial - no block
        if ($trialStatus['is_in_trial']) {
            return null;
        }

        // Suspended account
        if ($user->billing_status === 'suspended') {
            return 'account_suspended';
        }

        // Overdue invoices
        if ($hasOverdue) {
            return 'overdue_invoices';
        }

        // Trial expired without payment
        if ($this->hasTrialExpired($user) && $user->billing_status !== 'active') {
            return 'trial_expired';
        }

        return null;
    }

    /**
     * Handle payslip deletion - remove usage record
     */
    public function handlePayslipDeletion(PaySlip $payslip): void
    {
        $usage = PayslipUsage::where('payslip_id', $payslip->id)->first();
        
        if (!$usage) {
            return;
        }

        // Only allow deletion of pending usages
        if ($usage->status !== PayslipUsage::STATUS_PENDING) {
            Log::warning('BillingService: Cannot remove usage for non-pending payslip', [
                'payslip_id' => $payslip->id,
                'status' => $usage->status,
            ]);
            return;
        }

        DB::transaction(function () use ($usage) {
            // Decrement billing cycle counter if applicable
            if ($usage->billing_cycle_id) {
                $billingCycle = BillingCycle::find($usage->billing_cycle_id);
                if ($billingCycle && $billingCycle->total_payslips > 0) {
                    $billingCycle->decrement('total_payslips');
                }
            }

            // Decrement user counters
            $user = User::find($usage->user_id);
            if ($user) {
                if ($usage->billing_cycle_id) {
                    // Billable payslip
                    if ($user->payslips_count > 0) {
                        $user->decrement('payslips_count');
                    }
                } else {
                    // Trial payslip
                    if ($user->trial_payslips_used > 0) {
                        $user->decrement('trial_payslips_used');
                    }
                }
            }

            $usage->delete();

            Log::info('BillingService: Removed payslip usage', [
                'payslip_id' => $usage->payslip_id,
                'user_id' => $usage->user_id,
            ]);
        });
    }
}
