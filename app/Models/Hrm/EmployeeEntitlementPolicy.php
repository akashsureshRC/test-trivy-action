<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeEntitlementPolicy extends Model
{
    use \App\Models\Concerns\BelongsToWorkspace;

    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_management_id',
        'entitlement_id',
        'default_entitlement',
        'workspace',
        'created_by',
    ];

    public function employeeProfile()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    public function leaveManagement()
    {
        return $this->belongsTo(LeaveManagement::class, 'leave_management_id');
    }
    public function entitlementPolicy()
    {
        return $this->belongsTo(EntitlementPolicy::class, 'entitlement_id');
    }
}
