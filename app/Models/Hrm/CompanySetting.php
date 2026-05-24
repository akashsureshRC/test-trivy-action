<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'minimum_wage',
        'minimum_wage_monthly',
        'minimum_wage_normal_rate',
        'special_economic_zone',
        'economic_zone',
        'effective_from'
    ];
    
    
}
