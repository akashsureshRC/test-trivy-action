<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DividendsSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'term',
        'directive_number',
        'directive_issue_date',
        'directive_income_source_code',
        'directive_income_amount',
        'tax_to_deduct',
    ];


}
