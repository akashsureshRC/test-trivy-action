@extends('layouts.main')
@section('page-title')
{{__('Dashboard')}}
@endsection
@push('css')
<style>
.dash-content > .page-header {
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
    <!-- Total Customers -->
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="ti ti-building"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $dashboardData['total_customers'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">{{__('Total Customers')}}</p>
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
                    <p class="text-muted mb-0">{{__('Total Workspaces')}}</p>
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
                    <p class="text-muted mb-0">{{__('Total Employees')}}</p>
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
                    <p class="text-muted mb-0">{{__('Processed Payslips')}}</p>
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
                    <p class="text-muted mb-0">{{__('Total Payroll Cost')}}</p>
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
                    <p class="text-muted mb-0">{{__('ESS Enabled Users')}}</p>
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
                    <p class="text-muted mb-0">{{__('Pending Leave Requests')}}</p>
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
                    <p class="text-muted mb-0">{{__('ESS Adoption Rate')}}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statutory Deductions Card -->
    <div class="col-xl-6 col-md-6 col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{__('Monthly Statutory Deductions')}} ({{ $dashboardData['current_month'] ?? '' }})</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-3 text-center border-end">
                        <h6 class="text-muted mb-1">{{__('PAYE')}}</h6>
                        <h5 class="mb-0 text-primary">R {{ number_format($dashboardData['total_paye'] ?? 0, 2) }}</h5>
                    </div>
                    <div class="col-3 text-center border-end">
                        <h6 class="text-muted mb-1">{{__('UIF')}}</h6>
                        <h5 class="mb-0 text-info">R {{ number_format($dashboardData['total_uif'] ?? 0, 2) }}</h5>
                    </div>
                    <div class="col-3 text-center">
                        <h6 class="text-muted mb-1">{{__('SDL')}}</h6>
                        <h5 class="mb-0 text-warning">R {{ number_format($dashboardData['total_sdl'] ?? 0, 2) }}</h5>
                    </div>
                    <div class="col-3 text-center">
                        <h6 class="text-muted mb-1">{{__('Total Statutory')}}</h6>
                        <h4 class="mb-0 text-danger">R {{ number_format(($dashboardData['total_paye'] ?? 0) + ($dashboardData['total_uif'] ?? 0) + ($dashboardData['total_sdl'] ?? 0), 2) }}</h4>
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
                    <p class="text-muted mb-0">{{__('Active Payroll Cycles')}}</p>
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