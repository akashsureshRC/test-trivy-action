<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExpenseClaim extends Model
{
    use HasFactory;

    protected $fillable = ['amount','employee_id','term'];


}
