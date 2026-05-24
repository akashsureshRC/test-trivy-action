@extends('layouts.main')
@section('page-title')
{{ __('Add Payslip Inputs') }}
@endsection
@section('page-breadcrumb')
{{ __('Employee') }},
{{ __('Payroll') }},
{{ __('Payslip Inputs') }}
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
                @if(!in_array('annual_bonus', $paySlipInputs))
                <a href="{{ route('annual-bonuses.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-gift"></i>
                    <span>Annual Bonus</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if(!in_array('annual_payment', $paySlipInputs))
                <a href="{{ route('annual-payments.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-calendar-dollar"></i>
                    <span>Annual Payment</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if(!in_array('arbitration_award', $paySlipInputs))
                <a href="{{ route('arbitration-awards.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-gavel"></i>
                    <span>Arbitration Award</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if(!in_array('dividends_subject', $paySlipInputs))
                <a href="{{ route('dividends-subject.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-chart-pie"></i>
                    <span>Dividends Subject to Tax</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if(!in_array('extra_pay', $paySlipInputs))
                <a href="{{ route('extra-pay.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-plus"></i>
                    <span>Extra Pay</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if(!in_array('once_off_commission', $paySlipInputs))
                <a href="{{ route('once-off-commission.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-percentage"></i>
                    <span>Once-Off Commission</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if(!in_array('restraints_of_trade', $paySlipInputs))
                <a href="{{ route('restraint-of-trade.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-file-certificate"></i>
                    <span>Restraint Of Trade</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif

                {{-- Empty state --}}
                @if (in_array('annual_bonus', $paySlipInputs) && in_array('annual_payment', $paySlipInputs) && in_array('arbitration_award', $paySlipInputs) && in_array('dividends_subject', $paySlipInputs) && in_array('extra_pay', $paySlipInputs) && in_array('once_off_commission', $paySlipInputs) && in_array('restraints_of_trade', $paySlipInputs))
                <div class="payroll-empty-state" style="padding: 30px 20px;">
                    <i class="ti ti-check-circle" style="color: #10b981;"></i>
                    <p style="color: #6b7280; margin: 0;">All income inputs have been added</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Allowance Category --}}
    <div class="col-xxl-4 col-xl-4 col-md-6 mb-4">
        <div class="payroll-add-card">
            <div class="payroll-add-header allowance">
                <i class="ti ti-coins"></i>
                <h5>Allowances</h5>
            </div>
            <div class="payroll-add-body">
                @if(!in_array('broad_based_employee', $paySlipInputs))
                <a href="{{ route('broad-based-employees.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-share"></i>
                    <span>Broad Based Employee Share Plan</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if(!in_array('computer_allowance', $paySlipInputs))
                <a href="{{ route('computer-allowances.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-device-laptop"></i>
                    <span>Computer Allowance</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if(!in_array('expense_claim', $paySlipInputs))
                <a href="{{ route('expense-claims.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-receipt"></i>
                    <span>Expense Claim</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if(!in_array('equity_instruments', $paySlipInputs))
                <a href="{{ route('equity-instruments.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-chart-line"></i>
                    <span>Gain on Vesting of Equity</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if(!in_array('phone_allowance', $paySlipInputs))
                <a href="{{ route('phone-allowances.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-phone"></i>
                    <span>Phone Allowance</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if(!in_array('relocation_allowance', $paySlipInputs))
                <a href="{{ route('relocation-allowances.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-truck"></i>
                    <span>Relocation Allowance</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if(!in_array('allowance_international', $paySlipInputs))
                <a href="{{ route('allowance-internationals.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-plane"></i>
                    <span>Subsistence (International)</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if(!in_array('subsistence_allowance', $paySlipInputs))
                <a href="{{ route('subsistence-allowances.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-map-pin"></i>
                    <span>Subsistence (Local)</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if(!in_array('tool_allowance', $paySlipInputs))
                <a href="{{ route('tool-allowances.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-tool"></i>
                    <span>Tool Allowance</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if(!in_array('uniform_allowance', $paySlipInputs))
                <a href="{{ route('uniform-allowances.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-hanger"></i>
                    <span>Uniform Allowance</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Benefits & Deductions Column --}}
    <div class="col-xxl-4 col-xl-4 col-md-6 mb-4">
        {{-- Benefit Category --}}
        <div class="payroll-add-card mb-4">
            <div class="payroll-add-header benefit">
                <i class="ti ti-heart-handshake"></i>
                <h5>Benefits</h5>
            </div>
            <div class="payroll-add-body">
                @if(!in_array('bursary', $paySlipInputs))
                <a href="{{ route('bursaries.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-school"></i>
                    <span>Bursaries & Scholarships</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if(!in_array('benefit', $paySlipInputs))
                <a href="{{ route('employee-benefits.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-credit-card-off"></i>
                    <span>Employee's Debt Benefit</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if(!in_array('medical_cost', $paySlipInputs))
                <a href="{{ route('medical-costs.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-stethoscope"></i>
                    <span>Medical Costs (Other)</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if(!in_array('covid', $paySlipInputs))
                <a href="{{ route('covid19-disasters.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-virus"></i>
                    <span>COVID-19 Disaster Relief</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if(!in_array('long_service_award', $paySlipInputs))
                <a href="{{ route('long-service-awards.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-award"></i>
                    <span>Long Service Award</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if(!in_array('ters_payout', $paySlipInputs))
                <a href="{{ route('ters-payouts.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-cash"></i>
                    <span>TERS Payout</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if(!in_array('termination_lump_sum', $paySlipInputs))
                <a href="{{ route('termination-lumps.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-logout"></i>
                    <span>Termination Lump Sums</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
            </div>
        </div>

        {{-- Deduction Category --}}
        <div class="payroll-add-card">
            <div class="payroll-add-header deduction">
                <i class="ti ti-trending-down"></i>
                <h5>Deductions</h5>
            </div>
            <div class="payroll-add-body">
                @if(!in_array('donation', $paySlipInputs))
                <a href="{{ route('donations.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-heart"></i>
                    <span>Donations</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if(!in_array('repayment', $paySlipInputs))
                <a href="{{ route('repayments.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-arrow-back"></i>
                    <span>Repayment Of Loan</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif
                @if(!in_array('staff_purchase', $paySlipInputs))
                <a href="{{ route('staff-purchases.create',['employee_id' => request()->employee_id,'term' => $term]) }}" class="payroll-add-item">
                    <i class="ti ti-shopping-cart"></i>
                    <span>Staff Purchases</span>
                    <i class="ti ti-chevron-right"></i>
                </a>
                @endif

                {{-- Empty state --}}
                @if (in_array('donation', $paySlipInputs) && in_array('repayment', $paySlipInputs) && in_array('staff_purchase', $paySlipInputs))
                <div class="payroll-empty-state" style="padding: 30px 20px;">
                    <i class="ti ti-check-circle" style="color: #10b981;"></i>
                    <p style="color: #6b7280; margin: 0;">All deductions have been added</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection