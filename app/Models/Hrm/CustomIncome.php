<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomIncome extends Model
{
    use HasFactory;

     protected $fillable = [
        'name',
        'input_type',
        'taxed_annually',
        'include_in_fluctuating_leave_rate',
        'overtime',
        'affects_wage_eti',
        'amount',
        'rate_factor',
        'employee_work_factor',
        'hours_work_factor',
        'custom_rate',
        'percentage_income',
        'selected_income_items',
        'monthly_amount'
    ];

    protected $casts = [
        'taxed_annually' => 'boolean',
        'include_in_fluctuating_leave_rate' => 'boolean',
        'overtime' => 'boolean',
        'affects_wage_eti' => 'boolean',
        'selected_income_items' => 'array'
    ];
    
    
}
