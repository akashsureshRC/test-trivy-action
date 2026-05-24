<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Covid19Disaster extends Model
{
    use HasFactory;

    protected $fillable = ['amount', 'affects_wage_eti','employee_id','term'];


}
