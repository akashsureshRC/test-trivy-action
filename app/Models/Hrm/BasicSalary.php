<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
class BasicSalary extends Model
{
    use HasFactory;

    protected $table = 'basic_salaries';

    protected $fillable = [
        'hourly_paid',
        'hourly_rate',
        'dont_auto_pay_public_holidays',
        'fixed_salary',
        'paid_for_additional_hours',
        'override_hourly_rate',
        'rate_override',
        'employee_id',
        'normal_hours',
        'ot_hours',
        'term',
    ];
    protected $casts = [
        'fixed_salary' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }


    public function calculateUIF()
    {

        return 200;
    }


    public function calculatePayTax()
    {

        return 100;
    }


    public function calculateNetPay()
    {
        $fixed_salary = $this->fixed_salary;
        $uif = $this->calculateUIF();
        $payTax = $this->calculatePayTax();
        return $fixed_salary  - ($uif + $payTax);
    }
    public function incomePolicies(): HasMany
    {
        return $this->hasMany(IncomePolicy::class, 'employee_id', 'employee_id');
    }

}
