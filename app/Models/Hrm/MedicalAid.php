<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicalAid extends Model
{
    use HasFactory;

    protected $fillable = [
       'total_amount',
        'employer_contribution',
        'employee_payment',
        'apply_tax_credits',
        'employee_id',
        'payroll_id',
        'members',
        'term',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    public function payroll()
    {
        return $this->belongsTo(Payroll::class, 'payroll_id');
    }
}
