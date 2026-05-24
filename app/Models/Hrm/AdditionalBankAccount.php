<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdditionalBankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_name',
        'eft_format',
        'bank',
        'account_number',
        'branch_code',
        'account_type',
    ];
    
    
}
