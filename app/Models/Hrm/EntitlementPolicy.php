<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EntitlementPolicy extends Model
{
    use HasFactory;
    protected $table = 'entitlement_policies';
    protected $fillable = [
        'leave_management_id',
        'use_custom_name',
        'custom_name',
        'use_hours_worked',
        'hours_per_leave',
        'paid_leave_contributes',
        'default_entitlement',
        'entitlement_after_months',
        'use_upfront_accrual',
        'allow_carry_forward',
        'carry_forward_expiry_months',
        'limit_type',
        'limit_value',
        'cycle_specific_rules',
    ];
    protected $casts = [
        'use_custom_name' => 'boolean',
        'use_hours_worked' => 'boolean',
        'paid_leave_contributes' => 'boolean',
        'use_upfront_accrual' => 'boolean',
        'allow_carry_forward' => 'boolean',
    ];

    public function leaveManagement()
    {
        return $this->belongsTo(LeaveManagement::class);
    }

    public function ranges()
    {
        return $this->hasMany(EntitlementPolicyRange::class);
    }
    public function showLeaveType($type)
    {
        $formattedType = ucfirst(str_replace('-', ' ', $type)); // e.g. sick-leave => Sick Leave
        return view('leave.show', ['leaveTypeTitle' => $formattedType]);
    }
}
