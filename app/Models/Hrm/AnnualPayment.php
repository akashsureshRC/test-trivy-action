<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnnualPayment extends Model
{
    use HasFactory;

    protected $fillable = ['annual_amount','employee_id','term'];


}
