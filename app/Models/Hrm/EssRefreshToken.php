<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EssRefreshToken extends Model
{
    use HasFactory;

    protected $table = 'ess_refresh_tokens';

    protected $fillable = [
        'employee_id',
        'token',
        'device_name',
        'device_type',
        'ip_address',
        'user_agent',
        'expires_at',
        'last_used_at',
        'is_revoked'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'is_revoked' => 'boolean',
    ];

    /**
     * Relationship with Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Generate a new refresh token
     */
    public static function generate($employeeId, $expiresInDays = 30, $deviceInfo = [])
    {
        return self::create([
            'employee_id' => $employeeId,
            'token' => Str::random(128),
            'device_name' => $deviceInfo['device_name'] ?? null,
            'device_type' => $deviceInfo['device_type'] ?? null,
            'ip_address' => $deviceInfo['ip_address'] ?? request()->ip(),
            'user_agent' => $deviceInfo['user_agent'] ?? request()->userAgent(),
            'expires_at' => Carbon::now()->addDays($expiresInDays),
        ]);
    }

    /**
     * Check if token is valid (not expired and not revoked)
     */
    public function isValid()
    {
        return !$this->is_revoked && $this->expires_at->isFuture();
    }

    /**
     * Revoke this token
     */
    public function revoke()
    {
        $this->update(['is_revoked' => true]);
    }

    /**
     * Mark token as used
     */
    public function markAsUsed()
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Revoke all tokens for an employee
     */
    public static function revokeAllForEmployee($employeeId)
    {
        return self::where('employee_id', $employeeId)
                   ->where('is_revoked', false)
                   ->update(['is_revoked' => true]);
    }

    /**
     * Clean up expired tokens (older than 60 days)
     */
    public static function cleanupExpired()
    {
        return self::where('expires_at', '<', Carbon::now()->subDays(60))->delete();
    }
}
