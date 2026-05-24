<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomEmployerContribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'input_type', 
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
        
        'different_rate_for_every_employee' => 'boolean',
        'selected_income_items' => 'array',
    ];
}
