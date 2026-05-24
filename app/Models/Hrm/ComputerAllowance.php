<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ComputerAllowance extends Model
{
    use HasFactory;

    protected $fillable = ['computer_allowance','employee_id','term'];


}
