<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicalCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount', 'medical_cost','employee_id','term'
    ];


}
