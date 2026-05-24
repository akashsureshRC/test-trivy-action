<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Leave extends Model
{
    use \App\Models\Concerns\BelongsToWorkspace;

    use HasFactory;

    protected $fillable = [
        'employee_id',
        'user_id',
        'leave_type_id',
        'leave_management_id',
        'applied_on',
        'start_date',
        'end_date',
        'total_leave_days',
        'leave_reason',
        'remark',
        'status',
        'workspace',
        'created_by',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'source',
    ];
    
    protected static function newFactory()
    {
        return null; // Factory not migrated
    }

    public static function getLeaveType($leave_type)
    {
        return null;
    }

    public function leaveType()
    {
        return null;
    }

    public function leaveManagement()
    {
        return $this->belongsTo(LeaveManagement::class, 'leave_management_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by', 'id');
    }
}
