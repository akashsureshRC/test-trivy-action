<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class TaxYear extends Model
{
    protected $fillable = [
        'label',
        'effective_from',
        'effective_to',
        'tax_brackets',
        'primary_rebate',
        'secondary_rebate',
        'tertiary_rebate',
        'secondary_rebate_age',
        'tertiary_rebate_age',
        'uif_rate',
        'uif_ceiling',
        'sdl_rate',
        'eti_min_age',
        'eti_max_age',
        'eti_salary_cap',
        'eti_max_amount',
        'eti_rate',
        'ot_multiplier',
        'medical_aid_tax_rate',
        'travel_allowance_tax_rate',
        'is_locked',
        'locked_by',
        'locked_at',
    ];

    protected $casts = [
        'tax_brackets'              => 'array',
        'effective_from'            => 'date',
        'effective_to'              => 'date',
        'is_locked'                 => 'boolean',
        'locked_at'                 => 'datetime',
        'primary_rebate'            => 'decimal:2',
        'secondary_rebate'          => 'decimal:2',
        'tertiary_rebate'           => 'decimal:2',
        'uif_rate'                  => 'decimal:4',
        'uif_ceiling'               => 'decimal:2',
        'sdl_rate'                  => 'decimal:4',
        'eti_salary_cap'            => 'decimal:2',
        'eti_max_amount'            => 'decimal:2',
        'eti_rate'                  => 'decimal:4',
        'ot_multiplier'             => 'decimal:2',
        'medical_aid_tax_rate'      => 'decimal:4',
        'travel_allowance_tax_rate' => 'decimal:4',
    ];

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopeLocked($query)
    {
        return $query->where('is_locked', true);
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function lockedByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'locked_by');
    }

    public function paySlips()
    {
        return $this->hasMany(\App\Models\Hrm\PaySlip::class);
    }

    // ──────────────────────────────────────────────
    // Static helpers
    // ──────────────────────────────────────────────

    /**
     * Resolve the locked tax year for a given payroll term (Y-m or Y-m-d).
     *
     * SA tax year runs 1 Mar – 28/29 Feb.
     * A term of "2025-06" falls within the 2025/2026 year (effective_from 2025-03-01).
     *
     * Results are cached per-request to avoid repeated DB hits.
     */
    public static function resolveForTerm(string $term): ?self
    {
        static $cache = [];

        // Normalise to first-of-month date
        $termDate = Carbon::parse($term)->startOfMonth();
        $cacheKey = $termDate->format('Y-m');

        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $taxYear = static::locked()
            ->where('effective_from', '<=', $termDate)
            ->where('effective_to', '>=', $termDate)
            ->first();

        $cache[$cacheKey] = $taxYear;

        return $taxYear;
    }
}
