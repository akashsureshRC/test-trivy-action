<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IncomeProtection extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id',
        'amount',
        'amount_deducted',
        'amount_paid',
        'employer_own',
        'term',
    ];
    public function employeeprofile()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function incomeProtection()
    {
        return $this->hasMany(IncomeProtection::class);
    }
}
