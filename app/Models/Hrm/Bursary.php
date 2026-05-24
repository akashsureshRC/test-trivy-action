<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bursary extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'term',
        'taxable_portion',
        'exempt_portion',
        'type',
        'employee_handles_payment',
        'to_disabled_person',
    ];


}
