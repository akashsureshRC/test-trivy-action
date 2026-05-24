<?php

namespace App\Models\Hrm;
use App\Models\Hrm\TaxDirective;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaxDirectiveEntry extends Model
{
    use HasFactory;
    protected $table = 'tax_directives_entries';
    protected $fillable = ['employee_id',
        'directive_number', 'tax_directive_id',
        'directive_income_source_code',
        'directive_income_amount',
        'amount_of_tax_to_deduct',
        'directive_issue_date',
        'percentage',
        'term',
    ];
    
    public function taxDirective() {
        return $this->belongsTo(TaxDirective::class);
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    public function payroll()
{
    return $this->hasOne(Payroll::class, 'tax_directive_entry_id');
}
}
