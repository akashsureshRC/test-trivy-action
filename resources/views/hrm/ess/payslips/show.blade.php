@extends('hrm.ess.layouts.app')

@section('page-title', 'Payslip Details')
@section('page-subtitle', formatMonthYear($payslip->salary_month))

@section('content')
<!-- Back Button & Download -->
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <a href="{{ route('ess.payslips') }}" class="ess-btn ess-btn-outline">
            <i data-feather="arrow-left" style="width: 16px; height: 16px;"></i>
            Back to Payslips
        </a>
        <a href="{{ route('ess.payslips.download', \Illuminate\Support\Facades\Crypt::encrypt($payslip->id)) }}" 
           class="ess-btn ess-btn-rc-primary">
            <i data-feather="download" style="width: 16px; height: 16px;"></i>
            Download PDF
        </a>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <!-- Left Column - Employee Info & Net Pay -->
    <div class="col-lg-4">
        <!-- Employee Details -->
        <div class="ess-card">
            <div class="ess-card-header">
                <h3 class="ess-card-title">Employee Details</h3>
            </div>
            <div class="ess-list-item" style="border-bottom: none;">
                <div>
                    <p style="color: var(--ess-text-muted); font-size: 12px; margin: 0;">Name</p>
                    <strong style="color: var(--ess-text);">{{ $employee->first_name }} {{ $employee->last_name }}</strong>
                </div>
            </div>
            <div class="ess-list-item" style="border-bottom: none;">
                <div>
                    <p style="color: var(--ess-text-muted); font-size: 12px; margin: 0;">Employee ID</p>
                    <strong style="color: var(--ess-text);">{{ $employee->employee_id }}</strong>
                </div>
            </div>
            <div class="ess-list-item" style="border-bottom: none;">
                <div>
                    <p style="color: var(--ess-text-muted); font-size: 12px; margin: 0;">Department</p>
                    <strong style="color: var(--ess-text);">{{ $employee->department->name ?? 'N/A' }}</strong>
                </div>
            </div>
            <div class="ess-list-item" style="border-bottom: none;">
                <div>
                    <p style="color: var(--ess-text-muted); font-size: 12px; margin: 0;">Designation</p>
                    <strong style="color: var(--ess-text);">{{ $employee->designation->name ?? 'N/A' }}</strong>
                </div>
            </div>
            <hr style="margin: 0;">
            <div class="ess-list-item" style="border-bottom: none;">
                <div>
                    <p style="color: var(--ess-text-muted); font-size: 12px; margin: 0;">Pay Period</p>
                    <strong style="color: var(--ess-text);">{{ formatMonthYear($payslip->salary_month) }}</strong>
                </div>
            </div>
            <div class="ess-list-item" style="border-bottom: none;">
                <div>
                    <p style="color: var(--ess-text-muted); font-size: 12px; margin: 0;">Generated On</p>
                    <strong style="color: var(--ess-text);">{{ formatDateTime($payslip->created_at) }}</strong>
                </div>
            </div>
            <div class="ess-list-item" style="border-bottom: none;">
                <div>
                    <p style="color: var(--ess-text-muted); font-size: 12px; margin: 0;">Status</p>
                    @if($payslip->status == 1 || $payslip->status == 'generated')
                        <span class="badge bg-success">Generated</span>
                    @else
                        <span class="badge bg-warning">Pending</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Net Pay Summary Card -->
        <div class="ess-card" style="margin-top: 24px; background: linear-gradient(135deg, var(--ess-primary) 0%, var(--ess-primary-dark) 100%); color: white;">
            <div style="text-align: center; padding: 24px;">
                <p style="opacity: 0.8; font-size: 14px; margin-bottom: 8px;">Net Pay</p>
                <h2 style="font-size: 32px; font-weight: 700; margin: 0;">R {{ number_format($payslipDetail['net_pay'], 2) }}</h2>
            </div>
            <hr style="margin: 0; border-color: rgba(255,255,255,0.2);">
            <div class="row" style="padding: 16px;">
                <div class="col-6 text-center">
                    <p style="opacity: 0.8; font-size: 12px; margin-bottom: 4px;">Total Earnings</p>
                    <strong style="font-size: 16px;">R {{ number_format($payslipDetail['total_earnings'], 2) }}</strong>
                </div>
                <div class="col-6 text-center">
                    <p style="opacity: 0.8; font-size: 12px; margin-bottom: 4px;">Total Deductions</p>
                    <strong style="font-size: 16px;">R {{ number_format($payslipDetail['total_deductions'], 2) }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column - Earnings & Deductions -->
    <div class="col-lg-8">
        <!-- Earnings Section -->
        <div class="ess-card">
            <div class="ess-card-header" style="background: rgba(16, 185, 129, 0.1); border-left: 4px solid var(--ess-success);">
                <h3 class="ess-card-title" style="color: var(--ess-success);">
                    <i data-feather="plus-circle" style="width: 18px; height: 18px; margin-right: 8px;"></i>
                    Earnings
                </h3>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th style="font-size: 12px; text-transform: uppercase; color: var(--ess-text-muted); font-weight: 600;">Description</th>
                            <th style="font-size: 12px; text-transform: uppercase; color: var(--ess-text-muted); font-weight: 600; text-align: right;">Amount (R)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="color: var(--ess-text); font-weight: 500;">Basic Salary</td>
                            <td style="text-align: right; color: var(--ess-text);">{{ number_format($payslipDetail['basic_salary'], 2) }}</td>
                        </tr>
                        @foreach($payslipDetail['allowances'] as $allowance)
                            <tr>
                                <td style="color: var(--ess-text-muted);">{{ $allowance['title'] }}</td>
                                <td style="text-align: right; color: var(--ess-text);">{{ number_format($allowance['amount'], 2) }}</td>
                            </tr>
                        @endforeach
                        @foreach($payslipDetail['commissions'] as $commission)
                            <tr>
                                <td style="color: var(--ess-text-muted);">{{ $commission['title'] }}</td>
                                <td style="text-align: right; color: var(--ess-text);">{{ number_format($commission['amount'], 2) }}</td>
                            </tr>
                        @endforeach
                        @foreach($payslipDetail['other_payments'] as $payment)
                            <tr>
                                <td style="color: var(--ess-text-muted);">{{ $payment['title'] }}</td>
                                <td style="text-align: right; color: var(--ess-text);">{{ number_format($payment['amount'], 2) }}</td>
                            </tr>
                        @endforeach
                        @foreach($payslipDetail['overtimes'] as $overtime)
                            <tr>
                                <td style="color: var(--ess-text-muted);">
                                    {{ $overtime['title'] }}
                                    <small style="color: var(--ess-text-muted);">({{ $overtime['hours'] }} hrs @ R{{ number_format($overtime['rate'], 2) }})</small>
                                </td>
                                <td style="text-align: right; color: var(--ess-text);">{{ number_format($overtime['amount'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background: rgba(16, 185, 129, 0.1);">
                        <tr>
                            <td style="font-weight: 600; color: var(--ess-success);">Total Earnings</td>
                            <td style="text-align: right; font-weight: 600; color: var(--ess-success);">{{ number_format($payslipDetail['total_earnings'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Deductions Section -->
        <div class="ess-card" style="margin-top: 24px;">
            <div class="ess-card-header" style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid var(--ess-danger);">
                <h3 class="ess-card-title" style="color: var(--ess-danger);">
                    <i data-feather="minus-circle" style="width: 18px; height: 18px; margin-right: 8px;"></i>
                    Deductions
                </h3>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th style="font-size: 12px; text-transform: uppercase; color: var(--ess-text-muted); font-weight: 600;">Description</th>
                            <th style="font-size: 12px; text-transform: uppercase; color: var(--ess-text-muted); font-weight: 600; text-align: right;">Amount (R)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(array_merge($payslipDetail['deductions'], $payslipDetail['loans']) as $deduction)
                            <tr>
                                <td style="color: var(--ess-text-muted);">{{ $deduction['title'] }}</td>
                                <td style="text-align: right; color: var(--ess-text);">{{ number_format($deduction['amount'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" style="text-align: center; color: var(--ess-text-muted); padding: 20px;">No deductions</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot style="background: rgba(239, 68, 68, 0.1);">
                        <tr>
                            <td style="font-weight: 600; color: var(--ess-danger);">Total Deductions</td>
                            <td style="text-align: right; font-weight: 600; color: var(--ess-danger);">{{ number_format($payslipDetail['total_deductions'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Net Pay Final -->
        <div class="ess-card" style="margin-top: 24px;">
            <table class="table mb-0">
                <tbody>
                    <tr style="border-top: 3px solid var(--ess-text);">
                        <td style="font-size: 18px; font-weight: 700; color: var(--ess-text); padding: 16px;">NET PAY</td>
                        <td style="text-align: right; font-size: 18px; font-weight: 700; color: var(--ess-success); padding: 16px;">R {{ number_format($payslipDetail['net_pay'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
