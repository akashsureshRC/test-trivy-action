<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OnceOffCommission extends Model
{
    use HasFactory;

    protected $fillable = ['commission_amount','employee_id','term'];


}
