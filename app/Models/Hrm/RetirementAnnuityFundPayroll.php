<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RetirementAnnuityFundPayroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'amount',
        'portion',
        'employee_payment',
        'term'
    ];
    
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
