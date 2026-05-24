<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RetirementAnnuitie extends Model
{
    use HasFactory;

    protected $fillable = [

        'amount',
        'portion',
        'employee_payment',
        'employee_id',
        'term',
        
    ];
    
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    } 
}
