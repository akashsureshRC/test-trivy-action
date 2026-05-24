<?php


namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class IncomePolicy extends Model
{
    use HasFactory;
    protected $table = 'income_policies';
    protected $fillable = [
    'employee_id',
    'payout_amount',
    'term',
];
public function employee():BelongsTo
{
    return $this->belongsTo(Employee::class, 'employee_id');
}

public function incomePolicy()
{
    return $this->hasOne(IncomePolicy::class, 'employee_id', 'employee_id');
}
public function payroll()
{
    return $this->belongsTo(Payroll::class, 'employee_id', 'employee_id');
}

public function basicSalary():HasOne
{
    return $this->hasOne(BasicSalary::class, 'employee_id', 'employee_id');
}


public function calculateUIF()
{
    return ($this->basicSalary->fixed_salary + $this->payout_amount) * 0.01;
}


public function calculatePayTax()
{
    return ($this->basicSalary->fixed_salary + $this->payout_amount) * 0.15;
}


public function calculateNetPay()
{
    $fixed_salary = $this->basicSalary->fixed_salary ?? 0;
    $uif = $this->calculateUIF();
    $payTax = $this->calculatePayTax();
    
    return ($fixed_salary + $this->payout_amount) - ($uif + $payTax);
}
}
