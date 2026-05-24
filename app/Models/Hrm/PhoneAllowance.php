<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PhoneAllowance extends Model
{
    use HasFactory;

    protected $fillable = ['phone_allowance_amount','employee_id','term'];


}
