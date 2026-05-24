<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomReimbursement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'input_type', 
        'different_rate_for_every_employee', 
        'custom_rate'
    ];
    
    
}
