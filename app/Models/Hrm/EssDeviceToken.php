<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EssDeviceToken extends Model
{
    protected $fillable = [
        'employee_id',
        'fcm_token',
        'device_type',
        'device_name',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the employee that owns the device token.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Register or update a device token for an employee.
     */
    public static function registerToken(int $employeeId, string $fcmToken, ?string $deviceType = null, ?string $deviceName = null): self
    {
        return self::updateOrCreate(
            ['fcm_token' => $fcmToken],
            [
                'employee_id' => $employeeId,
                'device_type' => $deviceType,
                'device_name' => $deviceName,
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );
    }

    /**
     * Get active tokens for an employee.
     */
    public static function getActiveTokens(int $employeeId): array
    {
        return self::where('employee_id', $employeeId)
            ->where('is_active', true)
            ->pluck('fcm_token')
            ->toArray();
    }

    /**
     * Deactivate a token.
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }
}
