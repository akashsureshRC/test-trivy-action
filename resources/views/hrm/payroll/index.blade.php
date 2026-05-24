@extends('layouts.main')
@section('page-title')
{{ __('Payslip') }}
@endsection
@section('page-breadcrumb')
{{ __('Employee') }}, {{ __('Payslip') }}
@endsection

@section('page-action')
<div>
    <a href="{{ route('employees.list') }}" class="btn btn-rc-outline btn-rc-sm">
        <i class="ti ti-users me-1"></i> Employee List
    </a>
</div>
@endsection
@php
$company_settings = getCompanyAllSetting();
@endphp

@push('css')
<link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
@endpush

@section('content')
<div class="row">
    {{-- Main Content - Left Column --}}
    <div class="col-xxl-8 col-xl-8">
        {{-- Employee Profile Header --}}
        <div class="payroll-employee-header">
            <div class="payroll-employee-info">
                <h4>{{ $employee->first_name }} {{ $employee->last_name }}</h4>
                <p>{{ $employee->employee_id ?? 'N/A' }} • {{ $employee->designation->name ?? 'N/A' }}</p>
            </div>
            <div class="payroll-employee-actions" style="display: flex; align-items: center; gap: 10px;">
                <select class="form-control" id="termSelect" style="width: 180px; font-size: 14px;">
                    <option value="">Select Term</option>
                    <option value="{{ route('payroll.create-payslip', $employee->id) }}">Manually Add Payslip</option>
                    @foreach ($all_terms as $all_term)
                    <option
                        value="{{ route('payroll.index', ['employee_id' => $employee->id, 'term' => $all_term->salary_month]) }}"
                        {{ $all_term->salary_month == $term ? 'selected' : '' }}>
                        {{ $all_term->salary_month }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Regular Inputs Section --}}
        <div class="payroll-input-card">
            <div class="payroll-input-header">
                <h5>
                    Regular Inputs
                </h5>
                <a href="{{ route('payroll.regularinputs', ['employee_id' => request()->employee_id, 'term' => $term]) }}" class="btn btn-rc-primary btn-rc-sm">
                    <i class="ti ti-plus me-1"></i> Add
                </a>
            </div>
            <div class="payroll-input-body">
                {{-- Basic Salary --}}
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        @if ($basicSalaryData)
                        <a href="{{ route('basic-salariess.edit', ['employeeId' => $employee->id, 'term' => request('term')]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            {{ $basicSalaryData->hourly_paid == 1 ? 'Basic Salary - Hourly Paid' : 'Basic Salary' }}
                        </a>
                        @else
                        <span>Basic Salary</span>
                        @endif
                    </div>
                    <div class="payroll-input-value">
                        @if ($basicSalaryData && $basicSalaryData->hourly_paid == 0)
                        <span class="amount">{{ number_format($basicSalary ?? 0, 2) }}</span>
                        @elseif ($basicSalaryData && $basicSalaryData->hourly_paid == 1)
                        <span class="amount">{{ number_format($basicSalaryData->hourly_rate ?? 0, 2) }}/hr</span>
                        @else
                        <span class="amount text-muted">0.00</span>
                        @endif
                    </div>
                </div>

                {{-- Loss of Income Policy --}}
                @if (isset($income_policy))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('income-policies.edit', $income_policy->id) }}?term={{ $term }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Loss Of Income Policy
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($income_policy->payout_amount, 2) }}</span>
                        <form action="{{ route('income-policies.destroy', ['id' => $income_policy->id, 'term' => $term]) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Commission --}}
                @if (isset($commission))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('payslip-commissions.edit', $commission->id) }}?term={{ request('term') }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Commission
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($commission->commission_amount, 2) }}</span>
                        <form action="{{ route('payslip-commissions.destroy', ['id' => $commission->id, 'term' => $term]) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Travel Allowance --}}
                @if (isset($travel_allowance))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('travel-allowances.edit', $travel_allowance->id) }}?term={{ $term }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Travel Allowance
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($travel_allowance->fixed_amount, 2) }}</span>
                        <form action="{{ route('travel-allowances.destroy', ['id' => $travel_allowance->id, 'term' => $term]) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @if ($travel_allowance->reimbursed_per_km == 1)
                <div class="payroll-input-subitem">
                    <span class="label">Reimbursed per Km travelled</span>
                    <span class="value">{{ number_format($travel_allowance->rate_per_km, 2) }}/km</span>
                </div>
                @endif
                @endif

                {{-- Accommodation Benefit --}}
                @if (isset($accommodation_benefit))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('accommodation-benefits.edit', $accommodation_benefit->id) }}?term={{ $term }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Accommodation Benefit
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($accommodation_benefit->amount, 2) }}</span>
                        <form action="{{ route('accommodation-benefits.destroy', ['id' => $accommodation_benefit->id, 'term' => $term]) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Bursaries and Scholarships --}}
                @if (isset($bursaries_scholarships))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('bursaries-scholarships.edit', $bursaries_scholarships->id) }}?term={{ $term }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Bursaries and Scholarships
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($bursaries_scholarships->taxable_portion + $bursaries_scholarships->exempt_portion, 2) }}</span>
                        <form action="{{ route('bursaries-scholarships.destroy', ['id' => $bursaries_scholarships->id, 'term' => $term]) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Company Car --}}
                @if (isset($companyCar))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('company-cars.edit', $companyCar->id) }}?term={{ request('term', $term) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Company Car
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($companyCar->deemed_value, 2) }}</span>
                        <form action="{{ route('company-cars.destroy', ['id' => $companyCar->id, 'term' => $term]) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Company Car Under Operating --}}
                @if (isset($companyCarUnderOperating))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('company-car-operating.edit', $companyCarUnderOperating->id) }}?term={{ request('term', $term) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Company Car Under Operating
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($companyCarUnderOperating->amount, 2) }}</span>
                        <form action="{{ route('company-car-operating.destroy', ['id' => $companyCarUnderOperating->id, 'term' => $term]) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Garnishee --}}
                @if (isset($garnishee))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('garnishee.edit', $garnishee->id) }}?term={{ request('term') }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Garnishee
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount text-danger">-{{ number_format($garnishee->installment, 2) }}</span>
                        <form action="{{ route('garnishee.destroy', ['id' => $garnishee->id, 'term' => $term]) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Income Protection --}}
                @if (isset($incomeProtection))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('income-protection.edit', $incomeProtection->id) }}?term={{ request('term', $term) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Income Protection
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount text-danger">-{{ number_format($incomeProtection->amount_deducted, 2) }}</span>
                        <form action="{{ route('income-protection.destroy', ['id' => $incomeProtection->id, 'term' => $term]) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Maintenance Order --}}
                @if (isset($maintenance_order))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('maintenance-order.edit', $maintenance_order->id) }}?term={{ request('term', $term) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Maintenance Order
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount text-danger">-{{ number_format($maintenance_order->installment, 2) }}</span>
                        <form action="{{ route('maintenance-order.destroy', ['id' => $maintenance_order->id, 'term' => $term]) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Medical Aid --}}
                @if (isset($medical_aid))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('medical-aid.edit', $medical_aid->id) }}?term={{ request('term') }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Medical Aid
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($medical_aid->total_amount, 2) }}</span>
                        <form action="{{ route('medical-aid.destroy', ['id' => $medical_aid->id, 'term' => $term]) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Employer Contribution</span>
                    <span class="value">{{ number_format($medical_aid->employer_contribution, 2) }}</span>
                </div>
                @endif

                {{-- Pension Fund --}}
                @if (isset($pension_fund))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('pension-fund.edit', ['id' => $pension_fund->id, 'term' => request('term')]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Pension Fund
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        @if ($pension_fund->fixed_contribution_employee > 0 || $pension_fund->fixed_contribution_employer > 0)
                        <span class="amount text-danger">-{{ number_format($pension_fund->fixed_contribution_employee, 2) }}</span>
                        @else
                        <span class="amount text-danger">-{{ rtrim(rtrim(number_format($pension_fund->percentage_rfi_employee, 2, '.', ''), '0'), '.') }}% RFI</span>
                        @endif
                        <form action="{{ route('pension-fund.destroy', ['id' => $pension_fund->id, 'term' => $term]) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Provident Fund --}}
                @if (isset($provident_fund))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('provident-fund.edit', ['id' => $provident_fund->id, 'employee_id' => request()->get('employee_id'), 'term' => request()->get('term')]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Provident Fund
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        @if ($provident_fund->fixed_contribution_employee > 0 || $provident_fund->fixed_contribution_employer > 0)
                        <span class="amount text-danger">-{{ number_format($provident_fund->fixed_contribution_employee, 2) }}</span>
                        @else
                        <span class="amount text-danger">-{{ rtrim(rtrim(number_format($provident_fund->percentage_rfi_employee, 2, '.', ''), '0'), '.') }}% RFI</span>
                        @endif
                        <form action="{{ route('provident-fund.destroy', ['employee_id' => $provident_fund->employee_id, 'id' => $provident_fund->id, 'term' => $term]) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Retirement Annuity --}}
                @if (isset($retirement_annuity))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('retirement-annuitie.edit', ['id' => $retirement_annuity->id, 'term' => request('term')]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Retirement Annuity Fund
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount text-danger">-{{ number_format($retirement_annuity->amount, 2) }}</span>
                        <form action="{{ route('retirement-annuitie.destroy', ['id' => $retirement_annuity->id, 'term' => $term]) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Union Membership --}}
                @if (isset($union_membership))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('union-membership.edit', ['id' => $union_membership->id, 'term' => request('term')]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Union Membership Fee
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount text-danger">-{{ number_format($union_membership->amount_per_period, 2) }}</span>
                        <form action="{{ route('union-membership.destroy', ['id' => $union_membership, 'term' => $term]) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Tax Over Deduction --}}
                @if (isset($tax_over_deduction))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('tax-over-deduction.edit', ['id' => $tax_over_deduction->id, 'term' => request('term')]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Voluntary Tax Over-Deduction
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount text-danger">-{{ number_format($tax_over_deduction->per_period, 2) }}</span>
                        <form action="{{ route('tax-over-deduction.destroy', ['id' => $tax_over_deduction, 'term' => $term]) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Employer Loan --}}
                @if (isset($employer_loan))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('employer-loans.edit', ['id' => $employer_loan->id, 'term' => request('term')]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Employer Loan
                        </a>
                    </div>
                    <div class="payroll-input-value text-end">
                        @php
                            $interestBenefitAmount = $employer_loan->calculated_interest_benefit_amount ?? $employer_loan->interest_benefit_amount ?? 0;
                        @endphp
                        <span>
                            <span class="amount">{{ number_format($employer_loan->regular_repayment, 2) }}</span>
                            @if((int) ($employer_loan->calculate_interest_benefit ?? 0) === 1 && (float) $interestBenefitAmount > 0)
                                <small class="text-muted d-block">Interest Benefit: <span class="amount">{{ number_format($interestBenefitAmount, 2) }}</span></small>
                            @endif
                        </span>
                        <form action="{{ route('employer-loans.destroy', ['id' => $employer_loan, 'term' => $term]) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Savings Deduction --}}
                @if (isset($savings_deduction))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('savings-deductions.edit', ['id' => $savings_deduction->id, 'term' => request('term')]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Savings
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount text-danger">-{{ number_format($savings_deduction->regular_deduction, 2) }}</span>
                        <form action="{{ route('savings-deductions.destroy', ['id' => $savings_deduction, 'term' => $term]) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Tax Directive Entry --}}
                @if (isset($taxDirectiveEntry))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('tax-directive-entries.edit', ['id' => $taxDirectiveEntry->id, 'term' => request()->get('term')]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Tax Directive
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($taxDirectiveEntry->directive_income_amount, 2) }}</span>
                        <form action="{{ route('tax-directive-entries.destroy', ['id' => $taxDirectiveEntry, 'term' => $term]) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Payslip Inputs Section --}}
        <div class="payroll-input-card">
            <div class="payroll-input-header">
                <h5>
                    Payslip Inputs: {{ formatDate($term) }}
                </h5>
                <a href="{{ route('payroll.payslipinputs', ['employee_id' => request()->employee_id, 'term' => $term]) }}" class="btn btn-rc-primary btn-rc-sm">
                    <i class="ti ti-plus me-1"></i> Add
                </a>
            </div>
            <div class="payroll-input-body">
                {{-- Hourly Pay Basic Salary --}}
                @if ($basicSalaryData && $basicSalaryData->hourly_paid == 1)
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('basic-salariess.hourlyPay', ['id' => $basicSalaryData->id, 'term' => $term]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Basic Salary (Hourly)
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($basicSalaryData->normal_hour_value * $basicSalaryData->hourly_rate, 2) }}</span>
                    </div>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Normal Hours: {{ $basicSalaryData->normal_hour_value }}</span>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Overtime Hours: {{ $basicSalaryData->ot_hour_value }}</span>
                </div>
                @endif

                {{-- Annual Bonus --}}
                @if (isset($annual_bonus))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('annual-bonuses.edit', ['id' => $annual_bonus->id, 'term' => request()->get('term')]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Annual Bonus
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($annual_bonus->bonus_amount, 2) }}</span>
                        <form action="{{ route('annual-bonuses.destroy', $annual_bonus->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Annual Payment --}}
                @if (isset($annual_payment))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('annual-payments.edit', ['id' => $annual_payment->id, 'term' => request()->get('term')]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Annual Payment
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($annual_payment->annual_amount, 2) }}</span>
                        <form action="{{ route('annual-payments.destroy', $annual_payment->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Extra Pay --}}
                @if (isset($extra_pay))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('extra-pay.edit', ['id' => $extra_pay->id, 'term' => request()->get('term')]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Extra Pay
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($extra_pay->amount, 2) }}</span>
                        <form action="{{ route('extra-pay.destroy', $extra_pay->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Once Off Commission --}}
                @if (isset($once_off_commission))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('once-off-commission.edit', ['id' => $once_off_commission->id, 'term' => request()->get('term')]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Once Off Commission
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($once_off_commission->commission_amount, 2) }}</span>
                        <form action="{{ route('once-off-commission.destroy', $once_off_commission->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Restraint Of Trade --}}
                @if (isset($restraints_of_trade))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('restraint-of-trade.edit', ['id' => $restraints_of_trade->id, 'term' => request()->get('term')]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Restraint Of Trade
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($restraints_of_trade->amount, 2) }}</span>
                        <form action="{{ route('restraint-of-trade.destroy', $restraints_of_trade->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Arbitration Award --}}
                @if (isset($arbitration_award))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('arbitration-awards.edit', ['id' => $arbitration_award->id, 'term' => request()->get('term')]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Arbitration Award
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($arbitration_award->directive_income_amount, 2) }}</span>
                        <form action="{{ route('arbitration-awards.destroy', $arbitration_award->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Directive Number: {{ $arbitration_award->directive_number }}</span>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Directive Issue Date: {{ $arbitration_award->directive_issue_date }}</span>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Tax to Deduct: {{ number_format($arbitration_award->tax_to_deduct, 2) }}</span>
                </div>
                @endif

                {{-- Dividends Subject to Income Tax --}}
                @if (isset($dividends_subject))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('dividends-subject.edit', ['id' => $dividends_subject->id, 'term' => request()->get('term')]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Dividends Subject to Income Tax
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($dividends_subject->directive_income_amount, 2) }}</span>
                        <form action="{{ route('dividends-subject.destroy', $dividends_subject->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Directive Number: {{ $dividends_subject->directive_number }}</span>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Directive Issue Date: {{ $dividends_subject->directive_issue_date }}</span>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Source Code: {{ $dividends_subject->directive_income_source_code }}</span>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Tax to Deduct: {{ number_format($dividends_subject->tax_to_deduct, 2) }}</span>
                </div>
                @endif

                {{-- Allowances --}}
                @if (isset($broad_based_employee))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('broad-based-employees.edit', ['id' => $broad_based_employee->id, 'term' => request()->get('term')]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Broad-Based Employee Share Plan
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($broad_based_employee->amount, 2) }}</span>
                        <form action="{{ route('broad-based-employees.destroy', $broad_based_employee->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                @if (isset($computer_allowance))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('computer-allowances.edit', ['id' => $computer_allowance->id, 'term' => request()->get('term')]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Computer Allowance
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($computer_allowance->computer_allowance, 2) }}</span>
                        <form action="{{ route('computer-allowances.destroy', $computer_allowance->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                @if (isset($expense_claim))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('expense-claims.edit', ['id' => $expense_claim->id, 'term' => $term]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Expense Claim
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($expense_claim->amount, 2) }}</span>
                        <form action="{{ route('expense-claims.destroy', $expense_claim->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                @if (isset($equity_instruments))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('equity-instruments.edit', ['id' => $equity_instruments->id, 'term' => $term]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Gain on Vesting of Equity Instruments
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($equity_instruments->directive_income_amount, 2) }}</span>
                        <form action="{{ route('equity-instruments.destroy', $equity_instruments->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Directive Number: {{ $equity_instruments->directive_number }}</span>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Directive Issue Date: {{ $equity_instruments->directive_issue_date }}</span>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Tax to Deduct: {{ number_format($equity_instruments->tax_deduct_amount, 2) }}</span>
                </div>
                @endif

                @if (isset($phone_allowance))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('phone-allowances.edit', ['id' => $phone_allowance->id, 'term' => $term]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Phone Allowance
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($phone_allowance->phone_allowance_amount, 2) }}</span>
                        <form action="{{ route('phone-allowances.destroy', $phone_allowance->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                @if (isset($relocation_allowance))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('relocation-allowances.edit', ['id' => $relocation_allowance->id, 'term' => $term]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Relocation Allowance
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($relocation_allowance->taxable_allowance + $relocation_allowance->non_taxable_allowance, 2) }}</span>
                        <form action="{{ route('relocation-allowances.destroy', $relocation_allowance->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Taxable: {{ number_format($relocation_allowance->taxable_allowance, 2) }}</span>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Non-Taxable: {{ number_format($relocation_allowance->non_taxable_allowance, 2) }}</span>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Items Paid by Employer: {{ number_format($relocation_allowance->taxable_items_paid_by_employer, 2) }}</span>
                </div>
                @endif

                @if (isset($allowance_international))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('allowance-internationals.edit', ['id' => $allowance_international->id, 'term' => $term]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Subsistence Allowance International
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($allowance_international->paid_to_employee, 2) }}</span>
                        <form action="{{ route('allowance-internationals.destroy', $allowance_international->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Max Daily Deemed: {{ number_format($allowance_international->deemed_amount, 2) }}</span>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Days: {{ $allowance_international->number_of_days }}</span>
                </div>
                @endif

                @if (isset($subsistence_allowance))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('subsistence-allowances.edit', ['id' => $subsistence_allowance->id, 'term' => $term]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Subsistence Allowance Local
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($subsistence_allowance->full_amount_paid, 2) }}</span>
                        <form action="{{ route('subsistence-allowances.destroy', $subsistence_allowance->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Costs for Reimbursement: {{ number_format($subsistence_allowance->costs_for_reimbursement, 2) }}</span>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Days: {{ $subsistence_allowance->number_of_days }}</span>
                </div>
                @endif

                @if (isset($tool_allowance))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('tool-allowances.edit', ['id' => $tool_allowance->id, 'term' => $term]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Tool Allowance
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($tool_allowance->amount, 2) }}</span>
                        <form action="{{ route('tool-allowances.destroy', $tool_allowance->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                @if (isset($uniform_allowance))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('uniform-allowances.edit', ['id' => $uniform_allowance->id, 'term' => $term]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Uniform Allowance
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($uniform_allowance->amount, 2) }}</span>
                        <form action="{{ route('uniform-allowances.destroy', $uniform_allowance->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Benefits --}}
                @if (isset($bursary))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('bursaries.edit', ['id' => $bursary->id, 'term' => $term ?? $bursary->term]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Bursaries And Scholarships
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($bursary->taxable_portion + $bursary->exempt_portion, 2) }}</span>
                        <form action="{{ route('bursaries.destroy', $bursary->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Taxable: {{ number_format($bursary->taxable_portion, 2) }}</span>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Exempt: {{ number_format($bursary->exempt_portion, 2) }}</span>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Type: {{ $bursary->Type }}</span>
                </div>
                @endif

                @if (isset($benefit))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('employee-benefits.edit', ['id' => $benefit->id, 'term' => $term]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Employee's Debt Benefit
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($benefit->amount, 2) }}</span>
                        <form action="{{ route('employee-benefits.destroy', $benefit->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                @if (isset($medical_cost))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('medical-costs.edit', ['id' => $medical_cost->id, 'term' => $term]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Medical Costs (Other than medical scheme)
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($medical_cost->amount, 2) }}</span>
                        <form action="{{ route('medical-costs.destroy', $medical_cost->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Beneficiary: {{ $medical_cost->medical_cost }}</span>
                </div>
                @endif

                @if (isset($covid))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('covid19-disasters.edit', ['id' => $covid->id, 'term' => $term]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            COVID-19 Disaster Relief
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($covid->amount, 2) }}</span>
                        <form action="{{ route('covid19-disasters.destroy', $covid->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                @if (isset($long_service_award))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('long-service-awards.edit', ['id' => $long_service_award->id, 'term' => $term]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Long Service Award
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($long_service_award->long_cash_portion + $long_service_award->non_cash_portion, 2) }}</span>
                        <form action="{{ route('long-service-awards.destroy', $long_service_award->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Cash: {{ number_format($long_service_award->long_cash_portion, 2) }}</span>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Non-Cash: {{ number_format($long_service_award->non_cash_portion, 2) }}</span>
                </div>
                @endif

                @if (isset($ters_payout))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('ters-payouts.edit', ['id' => $ters_payout->id, 'term' => $term]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            TERS Payout
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($ters_payout->amount, 2) }}</span>
                        <form action="{{ route('ters-payouts.destroy', $ters_payout->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                @if (isset($termination_lump_sum))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('termination-lumps.edit', ['id' => $termination_lump_sum->id, 'term' => $term]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Termination Lump Sums
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount">{{ number_format($termination_lump_sum->directive_income_amount ?? 0, 2) }}</span>
                        <form action="{{ route('termination-lumps.destroy', $termination_lump_sum->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Directive Number: {{ $termination_lump_sum->directive_number }}</span>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Directive Issue Date: {{ $termination_lump_sum->directive_issue_date }}</span>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Source Code: {{ $termination_lump_sum->directive_income_source_code }}</span>
                </div>
                <div class="payroll-input-subitem">
                    <span class="label">Tax to Deduct: {{ number_format($termination_lump_sum->amount_of_tax_to_deduct, 2) }}</span>
                </div>
                @endif

                {{-- Deductions --}}
                @if (isset($donation))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('donations.edit', ['id' => $donation->id, 'term' => $term]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Donations
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount text-danger">-{{ number_format($donation->amount, 2) }}</span>
                        <form action="{{ route('donations.destroy', $donation->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                @if (isset($repayment))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('repayments.edit', ['id' => $repayment->id, 'term' => $term]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Repayment Of Loan
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount text-danger">-{{ number_format($repayment->amount, 2) }}</span>
                        <form action="{{ route('repayments.destroy', $repayment->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                @if (isset($staff_purchase))
                <div class="payroll-input-item">
                    <div class="payroll-input-label">
                        <a href="{{ route('staff-purchases.edit', ['id' => $staff_purchase->id, 'term' => $term]) }}">
                            <i class="ti ti-pencil edit-icon"></i>
                            Staff Purchases
                        </a>
                    </div>
                    <div class="payroll-input-value">
                        <span class="amount text-danger">-{{ number_format($staff_purchase->amount, 2) }}</span>
                        <form action="{{ route('staff-purchases.destroy', $staff_purchase->id) }}" method="POST" data-confirm-message="Are you sure you want to delete this?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn"><i class="ti ti-x"></i></button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Empty state if no payslip inputs --}}
                @if (!isset($annual_bonus) && !isset($annual_payment) && !isset($extra_pay) && !isset($once_off_commission) && !isset($restraints_of_trade) && !isset($arbitration_award) && !isset($dividends_subject) && !isset($broad_based_employee) && !isset($computer_allowance) && !isset($expense_claim) && !isset($equity_instruments) && !isset($phone_allowance) && !isset($relocation_allowance) && !isset($allowance_international) && !isset($subsistence_allowance) && !isset($tool_allowance) && !isset($uniform_allowance) && !isset($bursary) && !isset($benefit) && !isset($medical_cost) && !isset($covid) && !isset($long_service_award) && !isset($ters_payout) && !isset($termination_lump_sum) && !isset($donation) && !isset($repayment) && !isset($staff_purchase) && !($basicSalaryData && $basicSalaryData->hourly_paid == 1))
                <div class="payroll-empty-state">
                    <i class="ti ti-file-off"></i>
                    <p>No payslip inputs added for this period</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Summary Sidebar - Right Column --}}
    <div class="col-xxl-4 col-xl-4">
        <div class="payroll-summary-card">
            <div class="payroll-summary-header">
                <h5 class="text-white">Payroll Summary</h5>
            </div>

            {{-- Income --}}
            <div class="payroll-summary-section">
                <h6>Income</h6>
                <div class="payroll-summary-row highlight">
                    <span class="label"><strong>Total Income</strong></span>
                    <span class="value"><strong>{{ number_format($basicSalary + $totalIncome + $totalBenefit + $totalRegularInputIncome ?? 0, 2) }}</strong></span>
                </div>
                @if ($basicSalaryData && $basicSalaryData->hourly_paid == 0)
                <div class="payroll-summary-row">
                    <span class="label">Basic Salary</span>
                    <span class="value">{{ number_format($basicSalary, 2) }}</span>
                </div>
                @elseif ($basicSalaryData && $basicSalaryData->hourly_paid == 1)
                <div class="payroll-summary-row">
                    <span class="label">Basic Salary Hourly Pay</span>
                    <span class="value">{{ number_format($basicSalaryData->normal_hour_amount, 2) }}</span>
                </div>
                <div class="payroll-summary-row">
                    <span class="label">Overtime</span>
                    <span class="value">{{ number_format($basicSalaryData->ot_hour_amount, 2) }}</span>
                </div>
                @else
                <div class="payroll-summary-row">
                    <span class="label">Basic Salary</span>
                    <span class="value">0.00</span>
                </div>
                @endif
                @if(isset($income_policy))
                <div class="payroll-summary-row">
                    <span class="label">Loss Of Income Policy</span>
                    <span class="value">{{ number_format($income_policy->payout_amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($commission))
                <div class="payroll-summary-row">
                    <span class="label">Commission</span>
                    <span class="value">{{ number_format($commission->commission_amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($accommodation_benefit))
                <div class="payroll-summary-row">
                    <span class="label">Accommodation Benefit</span>
                    <span class="value">{{ number_format($accommodation_benefit->amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($companyCar))
                <div class="payroll-summary-row">
                    <span class="label">Company Car Benefit - Taxable Portion</span>
                    <span class="value">{{ number_format($companyCar->taxable_value ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($companyCarUnderOperating))
                <div class="payroll-summary-row">
                    <span class="label">Company Car Benefit - Taxable Portion (operating lease)</span>
                    <span class="value">{{ number_format($companyCarUnderOperating->taxable_value ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($savings_deduction))
                <div class="payroll-summary-row">
                    <span class="label">Savings</span>
                    <span class="value">{{ number_format($savings_deduction->regular_deduction, 2) }}</span>
                </div>
                @endif
                {{-- Payslip Inputs --}}
                @if(isset($annual_bonus))
                <div class="payroll-summary-row">
                    <span class="label">Annual Bonus</span>
                    <span class="value">{{ number_format($annual_bonus->bonus_amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($annual_payment))
                <div class="payroll-summary-row">
                    <span class="label">Annual Payment</span>
                    <span class="value">{{ number_format($annual_payment->annual_amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($extra_pay))
                <div class="payroll-summary-row">
                    <span class="label">Extra Pay</span>
                    <span class="value">{{ number_format($extra_pay->amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($once_off_commission))
                <div class="payroll-summary-row">
                    <span class="label">Once Off Commission</span>
                    <span class="value">{{ number_format($once_off_commission->commission_amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($restraints_of_trade))
                <div class="payroll-summary-row">
                    <span class="label">Restraints of Trade</span>
                    <span class="value">{{ number_format($restraints_of_trade->amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($arbitration_award))
                <div class="payroll-summary-row">
                    <span class="label">Arbitration Award</span>
                    <span class="value">{{ number_format($arbitration_award->directive_income_amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($dividends_subject))
                <div class="payroll-summary-row">
                    <span class="label">Dividends Restricted Equity</span>
                    <span class="value">{{ number_format($dividends_subject->directive_income_amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($termination_lump_sum))
                <div class="payroll-summary-row">
                    <span class="label">Gratuities / Severance Benefits</span>
                    <span class="value">{{ number_format($termination_lump_sum->directive_income_amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($benefit))
                <div class="payroll-summary-row">
                    <span class="label">Employee Debt Benefit</span>
                    <span class="value">{{ number_format($benefit->amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($medical_cost))
                <div class="payroll-summary-row">
                    <span class="label">Medical Costs Benefit</span>
                    <span class="value">{{ number_format($medical_cost->amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($covid))
                <div class="payroll-summary-row">
                    <span class="label">COVID-19 Disaster Relief</span>
                    <span class="value">{{ number_format($covid->amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($long_service_award))
                <div class="payroll-summary-row">
                    <span class="label">Long Service Award - Cash</span>
                    <span class="value">{{ number_format($long_service_award->long_cash_portion ?? 0, 2) }}</span>
                </div>
                <div class="payroll-summary-row">
                    <span class="label">Long Service Award - Non-Cash</span>
                    <span class="value">{{ number_format($long_service_award->non_cash_portion ?? 0, 2) }}</span>
                </div>
                @endif
            </div>

            {{-- Allowances --}}
            <div class="payroll-summary-section">
                <h6>Allowances</h6>
                <div class="payroll-summary-row highlight">
                    <span class="label"><strong>Total Allowances</strong></span>
                    <span class="value"><strong>{{ number_format($totalAllowance ?? 0, 2) }}</strong></span>
                </div>
                @if(isset($travel_allowance))
                <div class="payroll-summary-row">
                    <span class="label">Travel Allowance</span>
                    <span class="value">{{ number_format($travel_allowance->fixed_amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($bursaries_scholarships) && $bursaries_scholarships->employee_handles_payment == 1)
                <div class="payroll-summary-row">
                    <span class="label">Taxable Bursaries and Scholarships (Regular)</span>
                    <span class="value">{{ number_format($bursaries_scholarships->taxable_portion ?? 0, 2) }}</span>
                </div>
                <div class="payroll-summary-row">
                    <span class="label">Exempt Bursaries and Scholarships Benefit (Regular)</span>
                    <span class="value">{{ number_format($bursaries_scholarships->exempt_portion ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($bursary) && $bursary->employee_handles_payment == 1)
                <div class="payroll-summary-row">
                    <span class="label">Taxable Bursaries and Scholarships</span>
                    <span class="value">{{ number_format($bursary->taxable_portion ?? 0, 2) }}</span>
                </div>
                <div class="payroll-summary-row">
                    <span class="label">Exempt Bursaries and Scholarships</span>
                    <span class="value">{{ number_format($bursary->exempt_portion ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($broad_based_employee))
                <div class="payroll-summary-row">
                    <span class="label">Broad Based Employee Share Plan</span>
                    <span class="value">{{ number_format($broad_based_employee->amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($computer_allowance))
                <div class="payroll-summary-row">
                    <span class="label">Computer Allowance</span>
                    <span class="value">{{ number_format($computer_allowance->computer_allowance ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($expense_claim))
                <div class="payroll-summary-row">
                    <span class="label">Expense Claim</span>
                    <span class="value">{{ number_format($expense_claim->amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($equity_instruments))
                <div class="payroll-summary-row">
                    <span class="label">Gain from Vesting Equity</span>
                    <span class="value">{{ number_format($equity_instruments->directive_income_amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($phone_allowance))
                <div class="payroll-summary-row">
                    <span class="label">Phone Allowance</span>
                    <span class="value">{{ number_format($phone_allowance->phone_allowance_amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($tool_allowance))
                <div class="payroll-summary-row">
                    <span class="label">Tool Allowance</span>
                    <span class="value">{{ number_format($tool_allowance->amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($uniform_allowance))
                <div class="payroll-summary-row">
                    <span class="label">Uniform Allowance</span>
                    <span class="value">{{ number_format($uniform_allowance->amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($relocation_allowance))
                <div class="payroll-summary-row">
                    <span class="label">Relocation Allowance - Taxable</span>
                    <span class="value">{{ number_format($relocation_allowance->taxable_allowance ?? 0, 2) }}</span>
                </div>
                <div class="payroll-summary-row">
                    <span class="label">Relocation Allowance - Non-Taxable</span>
                    <span class="value">{{ number_format($relocation_allowance->non_taxable_allowance ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($allowance_international))
                <div class="payroll-summary-row">
                    <span class="label">Intl. Subsistence - under limit</span>
                    <span class="value">{{ number_format($allowance_international->paid_to_employee ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($subsistence_allowance))
                <div class="payroll-summary-row">
                    <span class="label">Local Subsistence - under limit</span>
                    <span class="value">{{ number_format($subsistence_allowance->full_amount_paid ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($medical_aid))
                <div class="payroll-summary-row">
                    <span class="label">Medical Aid Benefit Paid Out</span>
                    <span class="value">{{ number_format($medical_aid->employer_contribution ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($employer_loan))
                @php
                    $interestBenefitAmount = $employer_loan->calculated_interest_benefit_amount ?? $employer_loan->interest_benefit_amount ?? 0;
                @endphp
                <div class="payroll-summary-row">
                    <span class="label">Employer Loan</span>
                    <span class="value">{{ number_format($employer_loan->regular_repayment ?? 0, 2) }}</span>
                </div>
                @if((int) ($employer_loan->calculate_interest_benefit ?? 0) === 1 && (float) $interestBenefitAmount > 0)
                <div class="payroll-summary-row">
                    <span class="label">Employer Loan Interest Benefit</span>
                    <span class="value">{{ number_format($interestBenefitAmount, 2) }}</span>
                </div>
                @endif
                @endif
                @if(isset($ters_payout))
                <div class="payroll-summary-row">
                    <span class="label">TERS Payout</span>
                    <span class="value">{{ number_format($ters_payout->amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($taxDirectiveEntry))
                <div class="payroll-summary-row">
                    <span class="label">Directive Income Amount</span>
                    <span class="value">{{ number_format($taxDirectiveEntry->directive_income_amount, 2) }}</span>
                </div>
                @endif
            </div>

            {{-- Deductions --}}
            <div class="payroll-summary-section">
                <h6>Deductions</h6>
                <div class="payroll-summary-row highlight">
                    <span class="label"><strong>Total Deductions</strong></span>
                    <span class="value text-danger"><strong>{{ number_format($totalDeduction ?? 0, 2) }}</strong></span>
                </div>
                {{-- Regular inputs --}}
                @if(isset($garnishee))
                <div class="payroll-summary-row">
                    <span class="label">Garnishee</span>
                    <span class="value">{{ number_format($garnishee->installment ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($incomeProtection))
                <div class="payroll-summary-row">
                    <span class="label">Income protection policy - employee</span>
                    <span class="value">{{ number_format($incomeProtection->amount_deducted ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($maintenance_order))
                <div class="payroll-summary-row">
                    <span class="label">Maintenance Order</span>
                    <span class="value">{{ number_format($maintenance_order->installment ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($pension_fund))
                <div class="payroll-summary-row">
                    <span class="label">Pension Fund - Employee</span>
                    <span class="value">{{ number_format($pension_fund->fixed_contribution_employee ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($provident_fund))
                <div class="payroll-summary-row">
                    <span class="label">Provident Fund - Employee</span>
                    <span class="value">{{ number_format($provident_fund->fixed_contribution_employee ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($retirement_annuity))
                <div class="payroll-summary-row">
                    <span class="label">Retirement Annuity - Employee</span>
                    <span class="value">{{ number_format($retirement_annuity->amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($union_membership))
                <div class="payroll-summary-row">
                    <span class="label">Union Membership Fee</span>
                    <span class="value">{{ number_format($union_membership->amount_per_period ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($tax_over_deduction))
                <div class="payroll-summary-row">
                    <span class="label">Voluntary Tax Over-Deduction</span>
                    <span class="value">{{ number_format($tax_over_deduction->per_period ?? 0, 2) }}</span>
                </div>
                @endif
                {{-- Payslip Inputs --}}
                @if(isset($arbitration_award))
                <div class="payroll-summary-row">
                    <span class="label">Tax on Arbitration Award</span>
                    <span class="value">{{ number_format($arbitration_award->tax_to_deduct ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($dividends_subject))
                <div class="payroll-summary-row">
                    <span class="label">Tax on Dividends Restricted Equity</span>
                    <span class="value">{{ number_format($dividends_subject->tax_to_deduct ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($equity_instruments))
                <div class="payroll-summary-row">
                    <span class="label">Tax on Gain from Vesting Equity</span>
                    <span class="value">{{ number_format($equity_instruments->tax_deduct_amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($donation))
                <div class="payroll-summary-row">
                    <span class="label">Donation</span>
                    <span class="value">{{ number_format($donation->amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($repayment))
                <div class="payroll-summary-row">
                    <span class="label">Repayment Of Loan</span>
                    <span class="value">{{ number_format($repayment->amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($staff_purchase))
                <div class="payroll-summary-row">
                    <span class="label">Staff Purchases</span>
                    <span class="value">{{ number_format($staff_purchase->amount ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($termination_lump_sum))
                <div class="payroll-summary-row">
                    <span class="label">Tax on Gratuities / Severance Benefits</span>
                    <span class="value">{{ number_format($termination_lump_sum->amount_of_tax_to_deduct ?? 0, 2) }}</span>
                </div>
                @endif
                @if(isset($taxDirectiveEntry))
                <div class="payroll-summary-row">
                    <span class="label">Directive Deduction Amount</span>
                    <span class="value">{{ number_format($taxDirectiveEntry->amount_of_tax_to_deduct, 2) }}</span>
                </div>
                @endif
                <div class="payroll-summary-row">
                    <span class="label">UIF Employee</span>
                    <span class="value">{{ number_format($uif, 2) }}</span>
                </div>
                <div class="payroll-summary-row">
                    <span class="label">SDL Employee</span>
                    <span class="value">{{ number_format($sdl, 2) }}</span>
                </div>
                <div class="payroll-summary-row">
                    <span class="label">Tax Payee</span>
                    <span class="value">{{ number_format($payTax, 2) }}</span>
                </div>
                <div class="payroll-summary-row">
                    <span class="label">Loss of Pay</span>
                    <span class="value">{{ number_format($lossOfPay, 2) }}</span>
                </div>
            </div>

            {{-- Net Pay --}}
            <div class="payroll-summary-total net-pay">
                <span class="label">Net Pay</span>
                <span class="value">R {{ number_format($netPay ?? 0, 2) }}</span>
            </div>

            {{-- Finalize / Preview Actions --}}
            <div class="payroll-summary-actions" style="padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e5e7eb;">
                @if ($current_payslip && $current_payslip->status == 1)
                <a href="{{ route('payslip.unfinalize', $current_payslip->id) }}" class="btn btn-rc-primary btn-rc-sm">
                    <i class="ti ti-lock-open me-1"></i> Un Finalize
                </a>
                @elseif ($current_payslip && $current_payslip->status == 2)
                <button type="button" class="btn btn-rc-primary btn-rc-sm" disabled>
                    <i class="ti ti-lock-open me-1"></i> Un Finalize
                </button>
                @else
                <form method="POST" action="{{ route('payslip.finalize', $employee->id) }}">
                    @csrf
                    <input name="term" value="{{ $term }}" type="hidden">
                    <input name="basic_salary" value="{{ $basicSalary }}" type="hidden">
                    <input name="allowance" value="{{ $totalAllowance }}" type="hidden">
                    <input name="benefits" value="{{ $totalBenefit }}" type="hidden">
                    <input name="deduction" value="{{ $totalDeduction }}" type="hidden">
                    <input name="net_pay" value="{{ $netPay }}" type="hidden">
                    <button type="submit" class="btn btn-rc-primary btn-rc-sm">
                        <i class="ti ti-lock me-1"></i> Finalize
                    </button>
                </form>
                @endif
                <a href="{{ route('payslip.preview', ['id' => $employee->id, 'term' => $term]) }}" target="_blank" class="btn btn-rc-outline btn-rc-sm">
                    <i class="ti ti-file-text me-1"></i> Preview
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('termSelect').addEventListener('change', function() {
        let selectedUrl = this.value;
        if (selectedUrl) {
            window.location.href = selectedUrl;
        }
    });
</script>
@endsection