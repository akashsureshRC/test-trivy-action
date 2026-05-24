<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MaintenanceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'payroll_id',
         'installment',
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
