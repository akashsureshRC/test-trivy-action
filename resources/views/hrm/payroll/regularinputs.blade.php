@extends('layouts.main')
@section('page-title')
{{ __('Add Regular Inputs') }}
@endsection
@section('page-breadcrumb')
{{ __('Employee') }},
{{ __('Payroll') }},
{{ __('Regular Inputs') }}
@endsection
@section('page-action')
<div>
    <a href="{{ route('payroll.index', ['employee_id' => request()->employee_id, 'term' => $term]) }}" class="btn btn-rc-outline btn-sm">
        <i class="ti ti-arrow-left"></i> Back to Payroll
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
    {{-- Income Category --}}
    <div class="col-xxl-4 col-xl-4 col-md-6 mb-4">
        <div class="payroll-add-card">
            <div class="payroll-add-header income">
                <i class="ti ti-trending-up"></i>
                <h5>Income</h5>
            </div>
            <div class="payroll-add-body">
                @if (!in_array('basic_salary', $regularInputs))
                <a href="{{ route('basic-salariess.create', ['employee_id' => request()->employee_id, 'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-cash"></i>
                    <span>Basic Salary</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if (!in_array('commission', $regularInputs))
                <a href="{{ route('payslip-commissions.create', ['employee_id' => request()->employee_id, 'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-percentage"></i>
                    <span>Commission</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if (!in_array('income_policy', $regularInputs))
                <a href="{{ route('income-policies.create', ['employee_id' => request()->employee_id, 'term' => request()->term]) }}" class="payroll-add-item">
                    <i class="ti ti-shield-check"></i>
                    <span>Loss of Income Policy</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if (!in_array('travel_allowance', $regularInputs))
                <a href="{{ route('travel-allowances.create', ['employee_id' => request()->employee_id, 'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-car"></i>
                    <span>Travel Allowance</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if (!in_array('accommodation_benefit', $regularInputs))
                <a href="{{ route('accommodation-benefits.create', ['employee_id' => request()->employee_id, 'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-home"></i>
                    <span>Accommodation Benefit</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if (!in_array('bursaries_scholarships', $regularInputs))
                <a href="{{ route('bursaries-scholarships.create', ['employee_id' => request()->employee_id, 'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-school"></i>
                    <span>Bursaries & Scholarships</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if (!in_array('companyCar', $regularInputs))
                <a href="{{ route('company-cars.create', ['employee_id' => request()->employee_id, 'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-car"></i>
                    <span>Company Car</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if (!in_array('companyCarUnderOperating', $regularInputs))
                <a href="{{ route('company-car-operating.create', ['employee_id' => request()->employee_id, 'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-steering-wheel"></i>
                    <span>Company Car (Operating Lease)</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif

                {{-- Empty state for Income --}}
                @if (in_array('basic_salary', $regularInputs) && in_array('commission', $regularInputs) && in_array('income_policy', $regularInputs) && in_array('travel_allowance', $regularInputs) && in_array('accommodation_benefit', $regularInputs) && in_array('bursaries_scholarships', $regularInputs) && in_array('companyCar', $regularInputs) && in_array('companyCarUnderOperating', $regularInputs))
                <div class="payroll-empty-state" style="padding: 30px 20px;">
                    <i class="ti ti-check-circle" style="color: #10b981;"></i>
                    <p style="color: #6b7280; margin: 0;">All income inputs have been added</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Deduction Category --}}
    <div class="col-xxl-4 col-xl-4 col-md-6 mb-4">
        <div class="payroll-add-card">
            <div class="payroll-add-header deduction">
                <i class="ti ti-trending-down"></i>
                <h5>Deductions</h5>
            </div>
            <div class="payroll-add-body">
                @if (!in_array('garnishee', $regularInputs))
                <a href="{{ route('garnishee.create', ['employee_id' => request()->employee_id, 'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-gavel"></i>
                    <span>Garnishee</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if (!in_array('incomeProtection', $regularInputs))
                <a href="{{ route('income-protection.create', ['employee_id' => request()->employee_id, 'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-shield"></i>
                    <span>Income Protection</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if (!in_array('maintenance_order', $regularInputs))
                <a href="{{ route('maintenance-order.create', ['employee_id' => request()->employee_id, 'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-users"></i>
                    <span>Maintenance Order</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if (!in_array('medical_aid', $regularInputs))
                <a href="{{ route('medical-aid.create', ['employee_id' => request()->employee_id, 'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-heart-plus"></i>
                    <span>Medical Aid</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if (!in_array('pension_fund', $regularInputs))
                <a href="{{ route('pension-fund.create', ['employee_id' => request()->employee_id, 'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-receipt"></i>
                    <span>Pension Fund</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if (!in_array('provident_fund', $regularInputs))
                <a href="{{ route('provident-fund.create', ['employee_id' => request()->employee_id, 'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-building-bank"></i>
                    <span>Provident Fund</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if (!in_array('retirement_annuity', $regularInputs))
                <a href="{{ route('retirement-annuitie.create', ['employee_id' => request()->employee_id, 'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-old"></i>
                    <span>Retirement Annuity Fund</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if (!in_array('union_membership', $regularInputs))
                <a href="{{ route('union-membership.create', ['employee_id' => request()->employee_id, 'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-badge"></i>
                    <span>Union Membership Fee</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if (!in_array('tax_over_deduction', $regularInputs))
                <a href="{{ route('tax-over-deduction.create', ['employee_id' => request()->employee_id, 'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-receipt-tax"></i>
                    <span>Voluntary Tax Over-Deduction</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif

                {{-- Empty state for Deductions --}}
                @if (in_array('garnishee', $regularInputs) && in_array('incomeProtection', $regularInputs) && in_array('maintenance_order', $regularInputs) && in_array('medical_aid', $regularInputs) && in_array('pension_fund', $regularInputs) && in_array('provident_fund', $regularInputs) && in_array('retirement_annuity', $regularInputs) && in_array('union_membership', $regularInputs) && in_array('tax_over_deduction', $regularInputs))
                <div class="payroll-empty-state" style="padding: 30px 20px;">
                    <i class="ti ti-check-circle" style="color: #10b981;"></i>
                    <p style="color: #6b7280; margin: 0;">All deduction inputs have been added</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Other Category --}}
    <div class="col-xxl-4 col-xl-4 col-md-6 mb-4">
        <div class="payroll-add-card">
            <div class="payroll-add-header other">
                <i class="ti ti-dots-circle-horizontal"></i>
                <h5>Other</h5>
            </div>
            <div class="payroll-add-body">
                @if (!in_array('loan', $regularInputs))
                <a href="{{ route('employer-loans.create', ['employee_id' => request()->employee_id, 'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-credit-card"></i>
                    <span>Employer Loan</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if (!in_array('savings_deduction', $regularInputs))
                <a href="{{ route('savings-deductions.create', ['employee_id' => request()->employee_id, 'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-wallet"></i>
                    <span>Savings</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if (!in_array('taxDirectiveEntry', $regularInputs))
                <a href="{{ route('tax-directive-entries.create', ['employee_id' => request()->employee_id, 'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-file-certificate"></i>
                    <span>Tax Directive</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif

                {{-- Empty state for Other --}}
                @if (in_array('loan', $regularInputs) && in_array('savings_deduction', $regularInputs) && in_array('taxDirectiveEntry', $regularInputs))
                <div class="payroll-empty-state" style="padding: 30px 20px;">
                    <i class="ti ti-check-circle" style="color: #10b981;"></i>
                    <p style="color: #6b7280; margin: 0;">All other inputs have been added</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection