<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payroll extends Model
{
    use HasFactory;
    protected $table = 'payrolls';
    protected $fillable = [
        'employee_id',
        'basic_salary',
        'loss_of_income_policy_payout',
        'total_income',
        'total_deductions',
        'voluntary_tax_over_deduction',
        'travel_allowance',
        'accommodation_benefits',
        'taxable_portion',
        'exempt_portion',
        'bursary_type',
        'employee_handles_payment',
        'to_disabled_person',
        'deemed_value',
        'taxable_percentage_id',
        'includes_maintenance_plan',
          'company_car_taxable_amount',
           'company_car_total_amount',
        'company_car_under_operating_amount',
        'company_car_taxable_percentage',
        'company_car_total_amount',
        'beneficiary_name',
        'installment',
        'income_protection_paid_by_employee',
        'income_protection_deducted_from_employee',
        'income_protection_paid_by_employer',
        'income_protection_ownership',
        'maintenance_order_installment',
        'payroll_id',
        'employer_contribution',
        'total_amount',
        'employee_payment',
        'apply_tax_credits',
        'members',
        'employer_loan',
        'interest_benefit_amount',
        'regular_deduction',
        'union_membership_fee',
        'directive_number',
        'directive_type',
        'tax_directive_id',
        'directive_income_source_code',
        'directive_income_amount',
        'amount_of_tax_to_deduct',
        'directive_issue_date',
        'percentage',
        'uif_amount',
        'tax_pay',
        'net_pay'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
    public function basicSalary()
    {
        return $this->hasOne(BasicSalary::class, 'employee_id', 'employee_id');
    }
    public function incomePolicy()
    {
        return $this->hasOne(IncomePolicy::class, 'employee_id', 'employee_id');
    }
   
    public function travelAllowance()
    {
        return $this->hasOne(TravelAllowance::class, 'employee_id', 'employee_id');
    }
    public function accommodationBenefit(): HasOne
    {
        return $this->hasOne(AccommodationBenefit::class, 'employee_id', 'employee_id');
    }
    public function bursaryAndScholarship()
    {
        return $this->hasOne(BursariesScholarship::class, 'employee_id', 'employee_id');
    }
    public function companyCar()
    {
        return $this->belongsTo(CompanyCar::class, 'employee_id', 'employee_id');
    }
    public function companyCarUnderOperating()
    {
        return $this->belongsTo(CompanyCarUnderOperating::class, 'employee_id', 'employee_id');
    }
    public function garnishee()
    {
        return $this->hasMany(Garnishee::class, 'employee_id');
    }
    public function incomeProtection()
    {
        return $this->hasMany(incomeprotection::class);
    }
    public function maintenanceOrder()
    {
        return $this->hasOne(MaintenanceOrder::class, 'payroll_id'); // Foreign key in maintenance_orders
    }
    public function medicalAids()
{
    return $this->hasMany(MedicalAid::class, 'payroll_id');
}
public function unionMembershipFee()
{
    return $this->hasOne(UnionMembershipFee::class, 'payroll_id');
}


public function scopeFilterByEmployee($query, $employeeId)
{
    if ($employeeId) {
        return $query->where('employee_id', $employeeId);
    }
    return $query;
}

public function getCompanyCarUnderOperatingAmountAttribute()
{
    return $this->attributes['company_car_under_operating_amount'] ?? 0;
}



public function getCompanyCarTaxablePercentageAttribute()
{
    return $this->attributes['company_car_taxable_percentage'] ?? 0;
}



public function getCompanyCarTaxableAmountAttribute()
{
    $amount = $this->company_car_under_operating_amount;
    $percentage = $this->company_car_taxable_percentage;

    return $amount * ($percentage / 100);
}



public function getCompanyCarTotalAmountAttribute()
{
    return $this->company_car_under_operating_amount + $this->company_car_taxable_amount;
}
public function providentFundPayroll()
{
    return $this->hasOne(ProvidentFundPayroll::class, 'employee_id', 'employee_id');
}
public function pensionFundPayroll()
{
    return $this->hasOne(ProvidentFundPayroll::class, 'employee_id', 'employee_id');
}
public function voluntaryTaxOverDeduction()
{
    return $this->hasOne(TaxOverDeduction::class, 'employee_id', 'employee_id');
}
public function savingsDeduction()
    {
        return $this->hasOne(SavingsDeduction::class, 'employee_id', 'employee_id');
    }
    public function employerLoan()
{
    return $this->hasOne(EmployerLoan::class, 'employee_id', 'employee_id');
}
public function taxDirective() {
    return $this->belongsTo(TaxDirective::class);
}
public function taxDirectiveEntries()
{
    return $this->hasMany(TaxDirectiveEntry::class, 'employee_id');
}
}
