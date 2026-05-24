<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyBasicSalary extends Model
{
    use HasFactory;

    protected $fillable = [
        'hourly_rate',
        'dont_auto_pay_holidays',
        'enable_shifts',
        'employee_minimum_pay',
        'employee_fixed_component',
        'work_minimum_pay',
        'work_fixed_component',
        'override_holiday_pay_rates',
        'holiday_normal_multiplier',
        'holiday_overtime_multiplier',
        'override_sunday_pay_rates',
        'normally_works_multiplier',
        'normally_off_multiplier',
        'separate_overtime_hours',
    ];
    
    
}
