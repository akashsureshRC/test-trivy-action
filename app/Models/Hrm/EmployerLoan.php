<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployerLoan extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id','interest_rate', 
    'regular_repayment',
     'calculate_interest_benefit','interest_benefit_amount','term'];

    protected $casts = [
        'calculate_interest_benefit' => 'boolean',
        'interest_rate' => 'decimal:2',
        'regular_repayment' => 'decimal:2',
        'interest_benefit_amount' => 'decimal:2',
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
