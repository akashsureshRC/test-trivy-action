<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomAllowance extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'input_type',
        'affects_wage_for_eti_purpose',
        'enable_pro_rata',
        'amount',
        'rate_factor',
        'employee_work _factor',
        'different_rate_for_every_employee',
        'hours_work_factor',
        'custom_rate',
        'monthly_amount',
    ];
    
    
}
