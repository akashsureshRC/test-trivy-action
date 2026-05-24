<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnnualBonus extends Model
{
    use HasFactory;

    protected $fillable = [ 'bonus_amount','employee_id','term'];


}
