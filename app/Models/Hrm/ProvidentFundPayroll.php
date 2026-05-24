<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProvidentFundPayroll extends Model
{
    use HasFactory;

    protected $fillable = [

        'employee_id',
        'contribution',
        'fixed_contribution_employee',
        'fixed_contribution_employer',
        'percentage_rfi_employee',
        'percentage_rfi_employer',
        'category',
        'term'
    ];
    
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
