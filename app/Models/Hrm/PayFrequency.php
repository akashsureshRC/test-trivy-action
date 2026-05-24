<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayFrequency extends Model
{
    use HasFactory;
    protected $table = 'add_pay_frequencies';
    protected $fillable = [

        'pay_frequency',
        'last_day_of_period',
        'biweekly_date',
        'last_day_of_month',
        'go_further_back',
        'years_back',
    ];
    
    public function employees()
    {
        return $this->hasMany(Employee::class, 'pay_frequency', 'id');
    }
}
