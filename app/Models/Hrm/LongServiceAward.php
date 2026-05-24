<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LongServiceAward extends Model
{
    use HasFactory;

    protected $fillable = ['long_cash_portion', 'non_cash_portion','employee_id','term'];


}
