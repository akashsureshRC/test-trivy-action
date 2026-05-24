@extends('layouts.main')

@section('page-title')
{{ __('Dashboard') }}
@endsection
@push('css')
<style>
    .dash-content>.page-header {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
    }
</style>
@endpush
@section('content')
{{-- Trial Status Widget --}}
@include('partials.trial-status-widget')

<div class="row" style="padding-top: 0;">
    {{-- Metric Cards --}}
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="ti ti-users"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $dashboardData['total_employees'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">{{ __('Total Employees') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="ti ti-device-mobile"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $dashboardData['total_ess_users'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">{{ __('ESS Users') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="ti ti-calendar-event"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $dashboardData['pending_leave_requests'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">{{ __('Pending Leave') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                    <i class="ti ti-file-invoice"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $dashboardData['total_payslips'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">{{ __('Total Payslips') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                    <i class="ti ti-cash"></i>
                </div>
                <div>
                    <h3 class="mb-0">R {{ number_format($dashboardData['total_payroll_cost'] ?? 0, 2) }}</h3>
                    <p class="text-muted mb-0">{{ __('Total Payroll Cost') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Current Billing Amount - Only visible to company users, not payroll_officer --}}
    @if(($dashboardData['user_type'] ?? '') === 'company')
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-secondary bg-opacity-10 text-secondary me-3">
                    <i class="ti ti-receipt-2"></i>
                </div>
                <div>
                    <h3 class="mb-0">R {{ number_format($dashboardData['current_billing_amount'] ?? 0, 2) }}</h3>
                    <p class="text-muted mb-0">{{ __('Current Billing') }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<div class="row">
    {{-- Left Column (60%) --}}
    <div class="col-lg-8">
        {{-- Recent Employees --}}
        <x-rc-table title="{{ __('Recent Employees') }}" class="mb-4">
            <x-rc-table.content>
                    <table class="rc-table">
                        <thead>
                            <tr>
                                <th>{{ __('Employee ID') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th class="col-date">{{ __('Date Added') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dashboardData['recent_employees'] ?? [] as $employee)
                            <tr>
                                <td>{{ $employee->employee_id }}</td>
                                <td>{{ $employee->first_name }} {{ $employee->last_name }}</td>
                                <td>{{ $employee->email }}</td>
                                <td class="col-date">{{ formatDate($employee->created_at) }}</td>
                            </tr>
                            @empty
                            <x-rc-table.empty :asRow="true" :colspan="4" icon="ti ti-users" title="{{ __('No employees found') }}" message="" />
                            @endforelse
                        </tbody>
                    </table>
            </x-rc-table.content>
        </x-rc-table>

        {{-- Recent Payslips --}}
        <x-rc-table title="{{ __('Recent Payslips') }}">
            <x-rc-table.content>
                    <table class="rc-table">
                        <thead>
                            <tr>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Salary Month') }}</th>
                                <th class="col-amount">{{ __('Net Pay') }}</th>
                                <th class="col-status">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dashboardData['recent_payslips'] ?? [] as $payslip)
                            <tr>
                                <td>
                                    @if($payslip->employee)
                                    {{ $payslip->employee->first_name }} {{ $payslip->employee->last_name }}
                                    @else
                                    {{ __('Unknown') }}
                                    @endif
                                </td>
                                <td>{{ $payslip->salary_month }}</td>
                                <td class="col-amount">R {{ number_format($payslip->net_payble, 2) }}</td>
                                <td class="col-status">
                                    @if($payslip->status == 0)
                                    <span class="rc-status rc-status-warning">{{ __('Draft') }}</span>
                                    @elseif($payslip->status == 1)
                                    <span class="rc-status rc-status-info">{{ __('Finalized') }}</span>
                                    @elseif($payslip->status == 2)
                                    <span class="rc-status rc-status-success">{{ __('Processed') }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <x-rc-table.empty :asRow="true" :colspan="4" icon="ti ti-file-invoice" title="{{ __('No payslips found') }}" message="" />
                            @endforelse
                        </tbody>
                    </table>
            </x-rc-table.content>
        </x-rc-table>
    </div>

    {{-- Right Column (40%) --}}
    <div class="col-lg-4">
        {{-- Pending Leave Requests --}}
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Pending Leave Requests') }}</h5>
            </div>
            <div class="card-body">
                @forelse($dashboardData['pending_leaves'] ?? [] as $leave)
                <div class="d-flex align-items-start mb-3 p-3 border rounded">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">{{ $leave->employee_name }}</h6>
                        <p class="mb-1 text-muted small">
                            {{ $leave->leaveManagement->name ?? 'Leave' }} -
                            {{ formatDayMonth($leave->start_date) }} to
                            {{ formatDate($leave->end_date) }}
                        </p>
                        <span class="badge bg-light-warning text-warning">{{ $leave->total_leave_days }} {{ __('days') }}</span>
                    </div>
                    <div>
                        <a href="#"
                            data-url="{{ URL::to('leave/' . $leave->id . '/edit') }}"
                            data-ajax-popup="true"
                            data-size="md"
                            data-title="{{ __('Edit Leave') }}"
                            class="btn btn-sm btn-outline-primary"
                            title="{{ __('Review') }}">
                            <i class="ti ti-eye"></i> {{ __('Review') }}
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="ti ti-calendar-off" style="font-size: 48px;"></i>
                    <p class="mt-2">{{ __('No pending leave requests') }}</p>
                </div>
                @endforelse

                @if(count($dashboardData['pending_leaves'] ?? []) > 0)
                <div class="text-center mt-3">
                    <a href="{{ route('leave.index') }}" class="btn btn-outline-primary btn-sm">
                        {{ __('View All Leave Requests') }}
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Quick Actions') }}</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('employees.new') }}" class="btn btn-rc-primary w-100 d-flex align-items-center justify-content-between">
                        <span><i class="ti ti-user-plus me-2"></i>{{ __('Add Employee') }}</span>
                        <i class="ti ti-arrow-right"></i>
                    </a>
                    <a href="{{ route('payrun.index') }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-between">
                        <span><i class="ti ti-credit-card me-2"></i>{{ __('Run Payroll') }}</span>
                        <i class="ti ti-arrow-right"></i>
                    </a>
                    <a href="{{ route('leave.index') }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-between">
                        <span><i class="ti ti-calendar me-2"></i>{{ __('Manage Leave') }}</span>
                        <i class="ti ti-arrow-right"></i>
                    </a>
                    <a href="{{ route('filing.create') }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-between">
                        <span><i class="ti ti-file-text me-2"></i>{{ __('Monthly Filing') }}</span>
                        <i class="ti ti-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection