<?php

namespace App\Models\Hrm;
use App\Models\Hrm\CompanyCarTaxableType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyCarUnderOperating extends Model
{
    use HasFactory;

    protected $table = 'company_cars_under_operating'; 

    protected $fillable = ['employee_id','amount', 'taxable_percentage','term'];

    public function taxableType()
    {
        return $this->belongsTo(CompanyCarTaxableType::class, 'taxable_percentage', 'id');
      
    }
    public function employeeProfile()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
    public function basicSalary()
    {
        return $this->hasOne(BasicSalary::class, 'employee_id', 'employee_id');
    }
    public function payroll()
    {
        return $this->hasOne(Payroll::class, 'employee_id', 'employee_id');
    }
}
