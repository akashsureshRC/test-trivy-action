<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payrun extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'term',
        'payslip_id',
        'payroll_id',
        'payment_method',
    ];
    
    public function payslip(){
        return $this->belongsTo(PaySlip::class,'payslip_id');
    }

    protected static function newFactory()
    {
        return null; // Factory not migrated
    }
}
