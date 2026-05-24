<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubsistenceAllowance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'term',
        'costs_for_reimbursement',
        'full_amount_paid',
        'number_of_days'
    ];


}
