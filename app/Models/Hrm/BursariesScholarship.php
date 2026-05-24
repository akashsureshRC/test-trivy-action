<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BursariesScholarship extends Model
{
    use HasFactory;
    protected $table = 'bursaries_scholarships'; 
    protected $fillable = [
        'employee_id',
        'taxable_portion',
        'exempt_portion',
        'bursary_type',
        'employee_handles_payment',
        'to_disabled_person',
        'term',
    ];
    public function payroll()
    {
        return $this->belongsTo(Payroll::class, 'employee_id', 'employee_id');
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
