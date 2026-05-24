<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyCar extends Model
{
    use HasFactory;
    protected $table = 'company_cars';
    protected $fillable = [
        'employee_id',
       'deemed_value',
        'includes_maintenance_plan',
        'taxable_percentage_id',
        'term',
        
       
    ];

    public function taxableType()
    {
        return $this->belongsTo(CompanyCarTaxableType::class, 'taxable_percentage_id');
    }
    public function employee()
{
    return $this->belongsTo(Employee::class);
}
public function payroll()
{
    return $this->hasOne(Payroll::class, 'employee_id', 'employee_id');
}
}
