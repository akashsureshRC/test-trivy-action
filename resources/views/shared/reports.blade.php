@extends('layouts.main')
@section('page-title')
{{ __('Reports & Analytics') }}
@endsection
@section('page-breadcrumb')
{{ __('Reports') }}
@endsection

@push('css')
<style>
    .section-title {
        font-size: 16px;
        font-weight: 600;
        color: #973894;
        text-transform: uppercase;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #973894;
    }

    .filter-card {
        background: #f8f9fa;
        border: 1px solid #e6e6e6;
    }

    .chart-container {
        min-height: 300px;
        padding: 16px 0 0 16px;
    }

    .table-report th {
        background: #973894 !important;
        color: #fff !important;
        font-weight: 600;
    }

    .badge-status {
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
    }

    .trend-up {
        color: #28a745;
    }

    .trend-down {
        color: #dc3545;
    }
</style>
@endpush

@section('content')
{{-- Employee Count by Customer --}}
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <x-rc-table title="{{ __('Employee Count by Customer') }}">
                    <x-slot name="headerActions">
                        <form method="GET" action="{{ $filterRoute }}" class="d-flex gap-2" id="employeeFilterForm">
                            <input type="hidden" name="tab" value="employees">
                            <select name="employee_company" class="form-select form-select-sm" style="width: 150px;" onchange="document.getElementById('employeeFilterForm').submit()">
                                <option value="">{{ __('All Customers') }}</option>
                                @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ request('employee_company') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                                @endforeach
                            </select>
                        </form>
                    </x-slot>
                    <x-rc-table.content>
                        <table class="rc-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Customer') }}</th>
                                    <th class="text-center">{{ __('Employees') }}</th>
                                    <th class="text-center">{{ __('ESS Users') }}</th>
                                    <th class="text-center col-status">{{ __('Adoption') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employeeCountData as $row)
                                <tr>
                                    <td style="font-weight: 600;">{{ $row['company'] }}</td>
                                    <td class="text-center">{{ $row['employees'] }}</td>
                                    <td class="text-center">{{ $row['ess_users'] }}</td>
                                    <td class="text-center col-status">
                                        <span class="rc-table-status rc-table-status-{{ $row['adoption_rate'] >= 50 ? 'success' : 'warning' }}">
                                            {{ number_format($row['adoption_rate'], 1) }}%
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <x-rc-table.empty :asRow="true" :colspan="4" icon="ti ti-users-off" title="{{ __('No Data') }}" message="{{ __('No data available') }}" />
                                @endforelse
                            </tbody>
                        </table>
                    </x-rc-table.content>
                </x-rc-table>
            </div>
        </div>
    </div>

    {{-- Payslip Counts by Status --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <x-rc-table title="{{ __('Payslip Counts') }}">
                    <x-slot name="headerActions">
                        <form method="GET" action="{{ $filterRoute }}" class="d-flex gap-2" id="payslipFilterForm">
                            <input type="hidden" name="tab" value="payslips">
                            <select name="payslip_company" class="form-select form-select-sm" style="width: 150px;" onchange="document.getElementById('payslipFilterForm').submit()">
                                <option value="">{{ __('All Customers') }}</option>
                                @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ request('payslip_company') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                                @endforeach
                            </select>
                            <select name="payslip_status" class="form-select form-select-sm" style="width: 120px;" onchange="document.getElementById('payslipFilterForm').submit()">
                                <option value="">{{ __('All Status') }}</option>
                                <option value="0" {{ request('payslip_status') === '0' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                                <option value="1" {{ request('payslip_status') === '1' ? 'selected' : '' }}>{{ __('Finalized') }}</option>
                                <option value="2" {{ request('payslip_status') === '2' ? 'selected' : '' }}>{{ __('Processed') }}</option>
                            </select>
                        </form>
                    </x-slot>
                    <x-rc-table.content>
                        <table class="rc-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Customer') }}</th>
                                    <th class="text-center col-status">{{ __('Draft') }}</th>
                                    <th class="text-center col-status">{{ __('Finalized') }}</th>
                                    <th class="text-center col-status">{{ __('Processed') }}</th>
                                    <th class="text-center">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payslipCountData as $row)
                                <tr>
                                    <td style="font-weight: 600;">{{ $row['company'] }}</td>
                                    <td class="text-center col-status"><span class="rc-table-status rc-table-status-warning">{{ $row['draft'] }}</span></td>
                                    <td class="text-center col-status"><span class="rc-table-status rc-table-status-info">{{ $row['finalized'] }}</span></td>
                                    <td class="text-center col-status"><span class="rc-table-status rc-table-status-success">{{ $row['processed'] }}</span></td>
                                    <td class="text-center"><strong>{{ $row['total'] }}</strong></td>
                                </tr>
                                @empty
                                <x-rc-table.empty :asRow="true" :colspan="5" icon="ti ti-file-off" title="{{ __('No Data') }}" message="{{ __('No data available') }}" />
                                @endforelse
                            </tbody>
                        </table>
                    </x-rc-table.content>
                </x-rc-table>
            </div>
        </div>
    </div>
</div>

{{-- Payroll Cost Section --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <x-rc-table title="{{ __('Payroll Cost Analysis') }}">
                    <x-slot name="headerActions">
                        <form method="GET" action="{{ $filterRoute }}" class="d-flex gap-2" id="payrollCostFilterForm">
                            <input type="hidden" name="tab" value="payroll_cost">
                            <select name="cost_company" class="form-select form-select-sm" style="width: 150px;" onchange="document.getElementById('payrollCostFilterForm').submit()">
                                <option value="">{{ __('All Customers') }}</option>
                                @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ request('cost_company') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                                @endforeach
                            </select>
                            <select name="cost_period" class="form-select form-select-sm" style="width: 150px;" onchange="document.getElementById('payrollCostFilterForm').submit()">
                                <option value="monthly" {{ request('cost_period', 'monthly') == 'monthly' ? 'selected' : '' }}>{{ __('Monthly') }}</option>
                                <option value="yearly" {{ request('cost_period') == 'yearly' ? 'selected' : '' }}>{{ __('Financial Year') }}</option>
                            </select>
                        </form>
                    </x-slot>
                    <x-rc-table.content>
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="chart-container">
                                    <canvas id="payrollCostChart"></canvas>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <table class="rc-table">
                                    <thead>
                                        <tr>
                                            <th class="col-date">{{ __('Period') }}</th>
                                            <th class="text-end col-amount">{{ __('Amount') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($payrollCostData as $row)
                                        <tr>
                                            <td class="col-date">{{ $row['period'] }}</td>
                                            <td class="text-end col-amount"><strong>R {{ number_format($row['amount'], 2) }}</strong></td>
                                        </tr>
                                        @empty
                                        <x-rc-table.empty :asRow="true" :colspan="2" icon="ti ti-currency-dollar-off" title="{{ __('No Data') }}" message="{{ __('No data available') }}" />
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr style="background: #f4f4f4;">
                                            <th style="padding: 12px 16px;">{{ __('Total') }}</th>
                                            <th class="text-end col-amount" style="padding: 12px 16px;">R {{ number_format(collect($payrollCostData)->sum('amount'), 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </x-rc-table.content>
                </x-rc-table>
            </div>
        </div>
    </div>
</div>

{{-- Top Customers Section --}}
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="section-title mb-0 border-0 pb-0">{{ __('Top Customers by Employees') }}</h5>
            </div>
            <div class="card-body">
                @forelse($topCustomersByEmployees as $index => $customer)
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-3" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 50%; color:#fff !important">
                            {{ $index + 1 }}
                        </span>
                        <div>
                            <h6 class="mb-0">{{ $customer['company'] }}</h6>
                            <small class="text-muted">{{ $customer['employees'] }} {{ __('employees') }}</small>
                        </div>
                    </div>
                    <div class="progress" style="width: 100px; height: 8px;">
                        <div class="progress-bar bg-primary" style="width: {{ $customer['percentage'] }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-center text-muted">{{ __('No data available') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="section-title mb-0 border-0 pb-0">{{ __('Top Customers by Payroll Cost') }}</h5>
            </div>
            <div class="card-body">
                @forelse($topCustomersByPayroll as $index => $customer)
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-success me-3" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                            {{ $index + 1 }}
                        </span>
                        <div>
                            <h6 class="mb-0">{{ $customer['company'] }}</h6>
                            <small class="text-muted">R {{ number_format($customer['payroll_cost'], 2) }}</small>
                        </div>
                    </div>
                    <div class="progress" style="width: 100px; height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $customer['percentage'] }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-center text-muted">{{ __('No data available') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Payroll Cost Chart
    var payrollCostData = @json($payrollCostData);
    
    if (payrollCostData.length > 0) {
        var ctx = document.getElementById('payrollCostChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: payrollCostData.map(item => item.period),
                datasets: [{
                    label: '{{ __("Payroll Cost") }}',
                    data: payrollCostData.map(item => item.amount),
                    backgroundColor: 'rgba(57, 86, 202, 0.8)',
                    borderColor: 'rgba(57, 86, 202, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'R ' + context.raw.toLocaleString('en-ZA', {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R ' + value.toLocaleString('en-ZA');
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
