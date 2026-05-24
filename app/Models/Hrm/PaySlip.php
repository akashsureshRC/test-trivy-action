<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\TaxYear;

class PaySlip extends Model
{
    use \App\Models\Concerns\BelongsToWorkspace;

    use HasFactory;

    protected $fillable = [
        'employee_id',
        'net_payble',
        'basic_salary',
        'salary_month',
        'status',
        'emp201_status',
        'emp201_finalized_at',
        'allowance',
        'commission',
        'loan',
        'saturation_deduction',
        'other_payment',
        'overtime',
        'workspace',
        'created_by',
        'tax_year_id',
        'paye_amount',
        'uif_amount',
        'sdl_amount',
        'unpaid_leave_deduction',
    ];

    protected $dates = [
        'emp201_finalized_at',
        'created_at',
        'updated_at',
    ];
    public function employee_profile(){
        return $this->belongsTo(Employee::class,'employee_id');
    }
    
    // Alias for employee_profile for convenience
    public function employee(){
        return $this->belongsTo(Employee::class,'employee_id');
    }
    
    public function payrun(){
        return $this->hasOne(Payrun::class,'payslip_id');
    }

    public function taxYear(){
        return $this->belongsTo(TaxYear::class, 'tax_year_id');
    }
    protected static function newFactory()
    {
        return null; // Factory not migrated
    }
}
