<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingTier extends Model
{
    protected $table = 'billing_tiers';

    protected $fillable = [
        'name',
        'min_payslips',
        'max_payslips',
        'price_per_payslip',
        'sort_order',
    ];

    protected $casts = [
        'min_payslips' => 'integer',
        'max_payslips' => 'integer',
        'price_per_payslip' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    /**
     * Get all active tiers ordered by sort_order
     */
    public static function getActiveTiers()
    {
        return self::orderBy('sort_order')->get();
    }

    /**
     * Get the tier for a specific payslip number
     */
    public static function getTierForPayslip(int $payslipNumber): ?self
    {
        return self::where('min_payslips', '<=', $payslipNumber)
            ->where(function ($query) use ($payslipNumber) {
                $query->where('max_payslips', '>=', $payslipNumber)
                    ->orWhereNull('max_payslips');
            })
            ->first();
    }

    /**
     * Calculate cumulative price for a given number of payslips
     * Returns array with total and breakdown
     */
    public static function calculateCumulativePrice(int $totalPayslips): array
    {
        $tiers = self::getActiveTiers();
        $breakdown = [];
        $total = 0;
        $remainingPayslips = $totalPayslips;
        $currentPayslip = 1;
        $lastTier = null;

        foreach ($tiers as $tier) {
            if ($remainingPayslips <= 0) {
                break;
            }

            $lastTier = $tier;

            // Calculate how many payslips fall into this tier
            $tierMax = $tier->max_payslips ?? PHP_INT_MAX;
            $payslipsInTier = min(
                $remainingPayslips,
                $tierMax - $currentPayslip + 1
            );

            if ($payslipsInTier > 0) {
                $tierAmount = $payslipsInTier * $tier->price_per_payslip;
                $total += $tierAmount;

                $breakdown[] = [
                    'tier_id' => $tier->id,
                    'tier_name' => $tier->name,
                    'min_payslips' => $tier->min_payslips,
                    'max_payslips' => $tier->max_payslips,
                    'quantity' => $payslipsInTier,
                    'unit_price' => $tier->price_per_payslip,
                    'amount' => $tierAmount,
                    'sort_order' => $tier->sort_order,
                ];

                $remainingPayslips -= $payslipsInTier;
                $currentPayslip = ($tier->max_payslips ?? $currentPayslip + $payslipsInTier) + 1;
            }
        }

        // If there are remaining payslips after all tiers are exhausted,
        // charge them at the last tier's rate
        if ($remainingPayslips > 0 && $lastTier) {
            $tierAmount = $remainingPayslips * $lastTier->price_per_payslip;
            $total += $tierAmount;

            $breakdown[] = [
                'tier_id' => $lastTier->id,
                'tier_name' => $lastTier->name . ' (overflow)',
                'min_payslips' => $currentPayslip,
                'max_payslips' => null,
                'quantity' => $remainingPayslips,
                'unit_price' => $lastTier->price_per_payslip,
                'amount' => $tierAmount,
                'sort_order' => $lastTier->sort_order,
            ];
        }

        return [
            'total_payslips' => $totalPayslips,
            'subtotal' => $total,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Get formatted price range description
     */
    public function getPriceRangeAttribute(): string
    {
        $symbol = BillingSetting::getCurrencySymbol();
        
        if ($this->max_payslips) {
            return "{$this->min_payslips}-{$this->max_payslips} payslips @ {$symbol}{$this->price_per_payslip}";
        }
        
        return "{$this->min_payslips}+ payslips @ {$symbol}{$this->price_per_payslip}";
    }
}
