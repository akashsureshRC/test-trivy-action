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
@push('scripts')
<script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
@endpush
@section('content')
<div class="row" style="padding-top: 0;">
    <!-- Return to Master Admin Banner (when impersonating) -->
    @if(session('impersonating_from'))
    <div class="col-12 mb-3">
        <div class="alert alert-info d-flex align-items-center justify-content-between">
            <span>
                <i class="ti ti-info-circle me-2"></i>
                {{ __('You are currently logged in as a company. Click to return to your account.') }}
            </span>
            <a href="{{ route('master-admin.return') }}" class="btn btn-sm btn-rc-primary">
                <i class="ti ti-arrow-back me-1"></i>{{ __('Return to Master Admin') }}
            </a>
        </div>
    </div>
    @endif

    <!-- Upcoming Tax Year Alert -->
    @if(!empty($dashboardData['upcoming_tax_year_alert']))
    <div class="col-12 mb-3">
        <div class="alert alert-warning d-flex align-items-center justify-content-between">
            <span>
                <i class="ti ti-alert-triangle me-2"></i>
                {{ $dashboardData['upcoming_tax_year_alert'] }}
            </span>
            <a href="{{ route('tax-years.index') }}" class="btn btn-sm btn-warning">
                <i class="ti ti-settings me-1"></i>{{ __('Configure Tax Year') }}
            </a>
        </div>
    </div>
    @endif

    <!-- Total/Assigned Customers -->
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="ti ti-building"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $dashboardData['total_customers'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">{{ isset($isMasterAdmin) && $isMasterAdmin ? __('Assigned Customers') : __('Total Customers') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Workspaces -->
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                    <i class="ti ti-briefcase"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $dashboardData['total_workspaces'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">{{ __('Total Workspaces') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Employees -->
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="ti ti-users"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $dashboardData['total_employees'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">{{ __('Total Employees') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Processed Payslips -->
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="ti ti-file-invoice"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $dashboardData['processed_payslips'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">{{ __('Processed Payslips') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Payroll Cost -->
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="ti ti-cash"></i>
                </div>
                <div>
                    <h3 class="mb-0">R {{ number_format($dashboardData['total_payroll_cost'] ?? 0, 2) }}</h3>
                    <p class="text-muted mb-0">{{ __('Total Payroll Cost') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ESS Enabled Users -->
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                    <i class="ti ti-device-mobile"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $dashboardData['total_ess_users'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">{{ __('ESS Enabled Users') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Leave Requests -->
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                    <i class="ti ti-calendar-event"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $dashboardData['pending_leave_requests'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">{{ __('Pending Leave Requests') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ESS Adoption Rate -->
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="ti ti-percentage"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ number_format($dashboardData['ess_adoption_rate'] ?? 0, 1) }}%</h3>
                    <p class="text-muted mb-0">{{ __('ESS Adoption Rate') }}</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Statutory Deductions Card -->
    <div class="col-xl-6 col-md-6 col-12">
        <div class="card">
            <div class="card-header" style="padding: 10px 20px;">
                <h6 class="mb-0">{{ __('Monthly Statutory Deductions') }} ({{ $dashboardData['current_month'] ?? '' }})</h6>
            </div>
            <div class="card-body" style="padding: 5px;">
                <div class="row">
                    <div class="col-3 text-center border-end">
                        <h6 class="text-muted mb-1 text-xs">{{ __('PAYE') }}</h6>
                        <h5 class="mb-0">R {{ number_format($dashboardData['total_paye'] ?? 0, 2) }}</h5>
                    </div>
                    <div class="col-3 text-center border-end">
                        <h6 class="text-muted mb-1 text-xs">{{ __('UIF') }}</h6>
                        <h5 class="mb-0">R {{ number_format($dashboardData['total_uif'] ?? 0, 2) }}</h5>
                    </div>
                    <div class="col-3 text-center border-end">
                        <h6 class="text-muted mb-1 text-xs">{{ __('SDL') }}</h6>
                        <h5 class="mb-0">R {{ number_format($dashboardData['total_sdl'] ?? 0, 2) }}</h5>
                    </div>
                    <div class="col-3 text-center">
                        <h6 class="text-muted mb-1 text-xs">{{ __('Total Statutory') }}</h6>
                        <h4 class="mb-0">R {{ number_format(($dashboardData['total_paye'] ?? 0) + ($dashboardData['total_uif'] ?? 0) + ($dashboardData['total_sdl'] ?? 0), 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Payroll Cycles -->
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="ti ti-refresh"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $dashboardData['active_payroll_cycles'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">{{ __('Active Payroll Cycles') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Customers -->
    <div class="col-9">
        <x-rc-table title="{{ __('Recent Customers') }}" subtitle="{{ __('Latest 10') }}">
            <x-rc-table.content>
                <table class="rc-table">
                    <thead>
                        <tr>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th class="col-status">{{ __('Plan Type') }}</th>
                            <th class="col-date text-end">{{ __('Created') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentCustomers as $customer)
                        <tr>
                            <td style="font-weight: 600;">{{ $customer->name }}</td>
                            <td>{{ $customer->email }}</td>
                            <td class="col-status">
                                <span class="rc-status rc-status-{{ $customer->isOnTrial() ? 'warning' : 'success' }}">
                                    {{ $customer->isOnTrial() ? __('Trial') : __('Paid') }}
                                </span>
                            </td>
                            <td class="col-date text-end">{{ $customer->created_at ? formatDate($customer->created_at) : '' }}</td>
                        </tr>
                        @empty
                        <x-rc-table.empty :asRow="true" :colspan="4" icon="ti ti-building" title="{{ __('No Customers') }}" message="{{ __('No recent customers found') }}" />
                        @endforelse
                    </tbody>
                </table>
            </x-rc-table.content>
        </x-rc-table>
    </div>
    <!-- Quick Actions -->
    <div class="col-3">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">{{ __('Quick Actions') }}</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @if(isset($isMasterAdmin) && $isMasterAdmin)
                    <div class="col-md-12">
                        <a href="#" class="btn btn-rc-primary w-100 d-flex align-items-center justify-content-between" data-ajax-popup="true" data-size="md" data-title="{{ __('Create New Customer') }}" data-url="{{ route('users.create') }}">
                            <span><i class="ti ti-user-plus me-2"></i>{{ __('Add Customer') }}</span>
                            <i class="ti ti-arrow-right"></i>
                        </a>
                    </div>
                    <div class="col-md-12">
                        <a href="{{ route('users.index') }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-between">
                            <span><i class="ti ti-users me-2"></i>{{ __('Assigned Customers') }}</span>
                            <i class="ti ti-arrow-right"></i>
                        </a>
                    </div>
                    <div class="col-md-12">
                        <a href="{{ route('master-admin.reports') }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-between">
                            <span><i class="ti ti-chart-bar me-2"></i>{{ __('Reports & Analytics') }}</span>
                            <i class="ti ti-arrow-right"></i>
                        </a>
                    </div>
                    <div class="col-md-12">
                        <a href="{{ route('master-admin.payroll-cycles') }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-between">
                            <span><i class="ti ti-calendar-stats me-2"></i>{{ __('Payroll Cycles') }}</span>
                            <i class="ti ti-arrow-right"></i>
                        </a>
                    </div>
                    <div class="col-md-12">
                        <a href="{{ route('billing.invoices.index') }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-between">
                            <span><i class="ti ti-receipt-2 me-2"></i>{{ __('Invoices') }}</span>
                            <i class="ti ti-arrow-right"></i>
                        </a>
                    </div>
                    @else
                    <div class="col-md-12">
                        <a href="#" class="btn btn-rc-primary w-100 d-flex align-items-center justify-content-between" data-ajax-popup="true" data-size="md" data-title="{{ __('Create New Customer') }}" data-url="{{ route('users.create') }}">
                            <span><i class="ti ti-plus me-2"></i>{{ __('Add Customer') }}</span>
                            <i class="ti ti-arrow-right"></i>
                        </a>
                    </div>
                    <div class="col-md-12">
                        <a href="{{ route('users.list.view') }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-between">
                            <span><i class="ti ti-user-plus me-2"></i>{{ __('Customers') }}</span>
                            <i class="ti ti-arrow-right"></i>
                        </a>
                    </div>
                    <div class="col-md-12">
                        <a href="{{ route('super-admin.reports') }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-between">
                            <span><i class="ti ti-chart-bar me-2"></i>{{ __('Reports & Analytics') }}</span>
                            <i class="ti ti-arrow-right"></i>
                        </a>
                    </div>
                    <div class="col-md-12">
                        <a href="{{ route('super-admin.payroll-cycles') }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-between">
                            <span><i class="ti ti-calendar-stats me-2"></i>{{ __('Payroll Cycles') }}</span>
                            <i class="ti ti-arrow-right"></i>
                        </a>
                    </div>
                    <div class="col-md-12">
                        <a href="{{ route('billing.tiers.index') }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-between">
                            <span><i class="ti ti-stack-2 me-2"></i>{{ __('Billing Tiers') }}</span>
                            <i class="ti ti-arrow-right"></i>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if (moduleIsActive('LandingPage'))
    @include('landingpage::layouts.dash_qr')
    @endif
</div>
@endsection

@if (moduleIsActive('LandingPage'))
@include('landingpage::layouts.dash_qr_scripts')
@endif