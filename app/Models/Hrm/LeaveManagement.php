<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveManagement extends Model
{
    use \App\Models\Concerns\BelongsToWorkspace;

    use HasFactory;
    protected $table = 'leave_managements';
    protected $fillable = [
        'leave_name',
        'cycle_length',
        'cycle_start_type',
        'custom_cycle_date',
        'visible_for',
        'unpaid_leave',
        'show_on_payslip',
        'show_leave_expiry',
        'set_min_balance_rule',
        'minimum_balance',
        'allow_rule_override',
        'hide_balances',
        'workspace_id',
    ];
    protected $casts = [
        'unpaid_leave' => 'boolean',
        'show_on_payslip' => 'boolean',
        'show_leave_expiry' => 'boolean',
        'set_min_balance_rule' => 'boolean',
        'hide_balances' => 'boolean',
    ];
    public function entitlementPolicies()
    {
        return $this->hasMany(EntitlementPolicy::class);
    }

    public function employeeProfile()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
