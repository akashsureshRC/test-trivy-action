<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomBenefit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'input_type', 
        'exclude_from_accounting',
         'bargaining_council_item',
        'enable_pro_rata',
         'amount', 
         'rate_factor', 
         'different_rate_for_every_employee',
        'custom_rate', 
        'percentage_income',
         'selected_income_items', 
         'formula',
          'monthly_amount'
    ];
    
    protected $casts = [
        'exclude_from_accounting' => 'boolean',
        'bargaining_council_item' => 'boolean',
        'enable_pro_rata' => 'boolean',
        'different_rate_for_every_employee' => 'boolean',
        'selected_income_items' => 'array',
    ];
}
