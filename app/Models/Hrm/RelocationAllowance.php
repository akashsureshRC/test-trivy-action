<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RelocationAllowance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'term',
        'taxable_allowance',
        'non_taxable_allowance',
        'taxable_items_paid_by_employer',
    ];


}
