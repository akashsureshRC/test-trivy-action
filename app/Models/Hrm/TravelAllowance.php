<?php


namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class TravelAllowance extends Model
{
    use HasFactory;
    protected $table = 'travel_allowances';
    protected $fillable = ['employee_id',
        'fixed_allowance', 'fixed_amount',
        'reimbursed_expenses', 'company_petrol_card',
        'reimbursed_per_km', 'rate_per_km', 'subject_to_20_tax','term'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function calculateUIF()
{
    // Assuming UIF is a fixed amount of 200
    return 200;
}

// Method to calculate pay tax
public function calculatePayTax()
{
    // Assuming pay tax is a fixed amount of 100
    return 100;
}

// Method to calculate net pay considering travel allowance
public function calculateNetPay()
{
    $fixed_salary = $this->fixed_salary;
    $uif = $this->calculateUIF();
    $payTax = $this->calculatePayTax();
    $travel_allowance = $this->travel_allowance ?? 0; // Default to 0 if travel_allowance is not set

    // Calculate the net pay considering fixed salary, UIF, pay tax, and travel allowance
    return $fixed_salary + $travel_allowance - ($uif + $payTax);
}
public function payroll()
{
    return $this->belongsTo(Payroll::class, 'employee_id', 'employee_id');
}
}
