<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeSalaryDetail extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'calculation_type', 'monthly_amount', 'annual_amount'];
    
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
