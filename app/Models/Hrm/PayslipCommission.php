<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayslipCommission extends Model
{
    use HasFactory;
    protected $fillable = [
        'name_payslip',
        'commission_amount',
        'commission_type',
        'status',
        'employee_id',
        'term'
    ];
}
