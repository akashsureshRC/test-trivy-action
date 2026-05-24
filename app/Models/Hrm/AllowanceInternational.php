<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AllowanceInternational extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'term',
        'paid_to_employee',
        'deemed_amount',
        'number_of_days',
    ];


}
