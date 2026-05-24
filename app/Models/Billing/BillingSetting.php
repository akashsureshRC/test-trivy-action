<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BillingSetting extends Model
{
    protected $table = 'billing_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a billing setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = 'billing_setting_' . $key;
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a billing setting value
     */
    public static function set(string $key, $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        
        Cache::forget('billing_setting_' . $key);
        Cache::forget('billing_settings_all');
    }

    /**
     * Get all billing settings as key-value array
     */
    public static function getAllSettings(): array
    {
        return Cache::remember('billing_settings_all', 3600, function () {
            return self::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Clear all billing settings cache
     */
    public static function clearCache(): void
    {
        $settings = self::all();
        foreach ($settings as $setting) {
            Cache::forget('billing_setting_' . $setting->key);
        }
        Cache::forget('billing_settings_all');
    }

    /**
     * Get grace period in days
     */
    public static function getGracePeriodDays(): int
    {
        return (int) self::get('grace_period_days', 14);
    }

    /**
     * Get payment due days (days until invoice is due after generation)
     */
    public static function getDueDays(): int
    {
        return (int) self::get('invoice_due_days', 3);
    }

    /**
     * Get trial duration in days
     */
    public static function getTrialDays(): int
    {
        return (int) self::get('trial_days', 14);
    }

    /**
     * Get trial payslips limit
     */
    public static function getTrialPayslipsLimit(): int
    {
        return (int) self::get('trial_payslips_limit', 10);
    }

    /**
     * Check if billing is enabled
     */
    public static function isBillingEnabled(): bool
    {
        return self::get('billing_enabled', 'true') === 'true';
    }

    /**
     * Check if tax is enabled
     */
    public static function isTaxEnabled(): bool
    {
        return self::get('tax_enabled', 'false') === 'true';
    }

    /**
     * Get tax percentage
     */
    public static function getTaxPercentage(): float
    {
        return (float) self::get('tax_percentage', 15);
    }

    /**
     * Get currency symbol
     */
    public static function getCurrencySymbol(): string
    {
        return self::get('currency_symbol', 'R');
    }

    /**
     * Get invoice prefix
     */
    public static function getInvoicePrefix(): string
    {
        return self::get('invoice_prefix', 'INV');
    }
}
