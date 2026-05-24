<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TerminationLump extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'term',
        'directive_number',
        'directive_issue_date',
        'directive_income_source_code',
        'amount_of_tax_to_deduct',
        'directive_income_amount',
    ];


}
