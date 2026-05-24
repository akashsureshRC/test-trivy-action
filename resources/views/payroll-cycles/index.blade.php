@extends('layouts.main')

@section('page-title')
    {{ __('Payroll Cycles') }}
@endsection

@section('page-breadcrumb')
    {{ __('Payroll Cycles') }}
@endsection



@section('content')
    {{-- Summary Cards --}}
    <div class="row stat-cards">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="ti ti-file-text"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">{{ $payslips->where('status', 0)->count() }}</h3>
                        <p class="text-muted mb-0">{{ __('Draft') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                        <i class="ti ti-file-check"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">{{ $payslips->where('status', 1)->count() }}</h3>
                        <p class="text-muted mb-0">{{ __('Finalized') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="ti ti-circle-check"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">{{ $payslips->where('status', 2)->count() }}</h3>
                        <p class="text-muted mb-0">{{ __('Processed') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="ti ti-cash"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">R {{ number_format($payslips->sum('net_payble'), 2) }}</h3>
                        <p class="text-muted mb-0">{{ __('Total Value') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <x-rc-table>
                {{-- Filters --}}
                <x-rc-table.filter action="{{ $filterRoute }}" method="GET">
                    <x-rc-table.filter-group label="Customer" wide>
                        <select name="customer" id="customer" class="rc-filter-select">
                            <option value="">{{ __('All') }}</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ request('customer') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </x-rc-table.filter-group>
                    
                    <x-rc-table.filter-group label="Status">
                        <select name="status" id="status" class="rc-filter-select">
                            <option value="">{{ __('All') }}</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>{{ __('Finalized') }}</option>
                            <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>{{ __('Processed') }}</option>
                        </select>
                    </x-rc-table.filter-group>
                    
                    <x-rc-table.filter-group label="From Date" narrow>
                        <input type="date" name="date_from" id="date_from" class="rc-filter-input" value="{{ request('date_from') }}">
                    </x-rc-table.filter-group>
                    
                    <x-rc-table.filter-group label="To Date" narrow>
                        <input type="date" name="date_to" id="date_to" class="rc-filter-input" value="{{ request('date_to') }}">
                    </x-rc-table.filter-group>
                </x-rc-table.filter>

                {{-- Table Content --}}
                <x-rc-table.content>
                    <table class="rc-table">
                        <thead>
                            <tr>
                                <th class="col-id">{{ __('ID') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Employee') }}</th>
                                <th class="col-date">{{ __('Salary Month') }}</th>
                                <th class="col-amount">{{ __('Net Pay') }}</th>
                                <th class="col-status">{{ __('Status') }}</th>
                                <th class="col-date">{{ __('Created') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payslips as $payslip)
                            <tr>
                                <td class="col-id">#{{ $payslip->id }}</td>
                                <td>{{ $payslip->company_name }}</td>
                                <td>
                                    <span class="text-primary-cell">{{ $payslip->employee_name }}</span>
                                </td>
                                <td class="col-date">
                                    <span class="date-primary">{{ formatShortMonthYear($payslip->salary_month) }}</span>
                                </td>
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
                                <td class="col-date">
                                    <span class="date-primary">{{ formatDate($payslip->created_at) }}</span>
                                    <div class="date-secondary">{{ formatTime($payslip->created_at) }}</div>
                                </td>
                            </tr>
                            @empty
                            <x-rc-table.empty 
                                :asRow="true" 
                                :colspan="7"
                                icon="ti ti-receipt-off"
                                title="No Payroll Cycles Found"
                                message="No payroll cycles match your current filters. Try adjusting your search criteria."
                            />
                            @endforelse
                        </tbody>
                    </table>
                </x-rc-table.content>

                {{-- Pagination --}}
                <x-rc-table.footer :paginator="$payslips" />
            </x-rc-table>
        </div>
    </div>
@endsection
