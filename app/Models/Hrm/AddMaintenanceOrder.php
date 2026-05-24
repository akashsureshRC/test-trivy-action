<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AddMaintenanceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_name',
        'bank',
        'account_number',
        'branch_code', 
        'account_type',
        'include_eftexport',
        'eft_payment_type',
        'your_reference',
        'beneficiary_reference'
    ];
    
    
}
