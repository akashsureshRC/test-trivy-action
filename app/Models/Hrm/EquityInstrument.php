<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EquityInstrument extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'term',
        'directive_number',
        'directive_issue_date',
        'tax_deduct_amount',
        'directive_income_amount'
    ];


}
