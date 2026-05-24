<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeSalary extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'annual_ctc',
        'basic_salary',
        'commission_amount',
        'uif_employee',
        'tax',
        'net_salary'
    ];
    
    public function employeeProfile()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
