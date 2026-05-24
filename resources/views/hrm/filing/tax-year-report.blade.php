@extends('layouts.main')

@section('page-title')
    {{ __('Tax Year Summary Report') }}
@endsection

@section('page-breadcrumb')
    {{ __('Filing') }}, {{ __('Tax Year Report') }}
@endsection

@section('content')
<div class="row">
    {{-- Filter --}}
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('filing.tax-year-report') }}" class="row align-items-end g-3">
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Select Tax Year') }}</label>
                        <select name="tax_year_id" class="form-select">
                            <option value="">{{ __('-- Choose a locked tax year --') }}</option>
                            @foreach($taxYears as $ty)
                                <option value="{{ $ty->id }}" {{ $selectedId == $ty->id ? 'selected' : '' }}>
                                    {{ $ty->label }} ({{ $ty->effective_from->format('d M Y') }} – {{ $ty->effective_to->format('d M Y') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-rc-primary">
                            <i class="ti ti-filter me-1"></i>{{ __('Generate') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($reportData)
    {{-- Summary Cards --}}
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="ti ti-receipt-tax"></i>
                </div>
                <div>
                    <h3 class="mb-0">R {{ number_format($reportData->total_paye ?? 0, 2) }}</h3>
                    <p class="text-muted mb-0">{{ __('Total PAYE') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                    <i class="ti ti-shield-check"></i>
                </div>
                <div>
                    <h3 class="mb-0">R {{ number_format($reportData->total_uif ?? 0, 2) }}</h3>
                    <p class="text-muted mb-0">{{ __('Total UIF') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="ti ti-school"></i>
                </div>
                <div>
                    <h3 class="mb-0">R {{ number_format($reportData->total_sdl ?? 0, 2) }}</h3>
                    <p class="text-muted mb-0">{{ __('Total SDL') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="ti ti-file-invoice"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ number_format($reportData->payslip_count ?? 0) }}</h3>
                    <p class="text-muted mb-0">{{ __('Payslips') }} ({{ number_format($reportData->employee_count ?? 0) }} {{ __('employees') }})</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Grand Total --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="padding: 10px 20px;">
                <h6 class="mb-0">{{ __('Summary for') }} {{ $reportData->tax_year->label }} — SARS Tax Year {{ $reportData->tax_year->effective_from->format('d M Y') }} to {{ $reportData->tax_year->effective_to->format('d M Y') }}</h6>
            </div>
            <div class="card-body" style="padding: 10px;">
                <div class="row text-center">
                    <div class="col-3 border-end">
                        <h6 class="text-muted mb-1 text-xs">{{ __('Total Statutory') }}</h6>
                        <h4 class="mb-0">R {{ number_format(($reportData->total_paye ?? 0) + ($reportData->total_uif ?? 0) + ($reportData->total_sdl ?? 0), 2) }}</h4>
                    </div>
                    <div class="col-3 border-end">
                        <h6 class="text-muted mb-1 text-xs">{{ __('Total Net Pay') }}</h6>
                        <h4 class="mb-0">R {{ number_format($reportData->total_net_pay ?? 0, 2) }}</h4>
                    </div>
                    <div class="col-3 border-end">
                        <h6 class="text-muted mb-1 text-xs">{{ __('Avg PAYE per Payslip') }}</h6>
                        <h4 class="mb-0">R {{ $reportData->payslip_count > 0 ? number_format(($reportData->total_paye ?? 0) / $reportData->payslip_count, 2) : '0.00' }}</h4>
                    </div>
                    <div class="col-3">
                        <h6 class="text-muted mb-1 text-xs">{{ __('Unique Employees') }}</h6>
                        <h4 class="mb-0">{{ number_format($reportData->employee_count ?? 0) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly Breakdown Table --}}
    <div class="col-sm-12">
        <x-rc-table title="{{ __('Monthly Breakdown') }}" subtitle="{{ $reportData->tax_year->label }}">
            <x-rc-table.content>
                <table class="rc-table">
                    <thead>
                        <tr>
                            <th>{{ __('Month') }}</th>
                            <th class="text-end">{{ __('Payslips') }}</th>
                            <th class="text-end">{{ __('PAYE') }}</th>
                            <th class="text-end">{{ __('UIF') }}</th>
                            <th class="text-end">{{ __('SDL') }}</th>
                            <th class="text-end">{{ __('Total Statutory') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData->monthly as $row)
                        <tr>
                            <td class="font-style">{{ \Carbon\Carbon::parse($row->salary_month)->format('F Y') }}</td>
                            <td class="text-end">{{ number_format($row->payslip_count) }}</td>
                            <td class="text-end">R {{ number_format($row->total_paye ?? 0, 2) }}</td>
                            <td class="text-end">R {{ number_format($row->total_uif ?? 0, 2) }}</td>
                            <td class="text-end">R {{ number_format($row->total_sdl ?? 0, 2) }}</td>
                            <td class="text-end"><strong>R {{ number_format(($row->total_paye ?? 0) + ($row->total_uif ?? 0) + ($row->total_sdl ?? 0), 2) }}</strong></td>
                        </tr>
                        @empty
                        <x-rc-table.empty :asRow="true" :colspan="6" icon="ti ti-receipt-tax" title="{{ __('No Data') }}" message="{{ __('No payslips found for this tax year. Payslips created before the Dynamic Tax Year feature will not appear here.') }}" />
                        @endforelse
                    </tbody>
                    @if($reportData->monthly->count() > 0)
                    <tfoot>
                        <tr style="font-weight: bold; background: #f9fafb;">
                            <td>{{ __('Total') }}</td>
                            <td class="text-end">{{ number_format($reportData->monthly->sum('payslip_count')) }}</td>
                            <td class="text-end">R {{ number_format($reportData->monthly->sum('total_paye'), 2) }}</td>
                            <td class="text-end">R {{ number_format($reportData->monthly->sum('total_uif'), 2) }}</td>
                            <td class="text-end">R {{ number_format($reportData->monthly->sum('total_sdl'), 2) }}</td>
                            <td class="text-end">R {{ number_format($reportData->monthly->sum('total_paye') + $reportData->monthly->sum('total_uif') + $reportData->monthly->sum('total_sdl'), 2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </x-rc-table.content>
        </x-rc-table>
    </div>
    @elseif($selectedId === null)
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="ti ti-chart-bar" style="font-size: 48px; color: #d1d5db;"></i>
                <h5 class="mt-3 text-muted">{{ __('Select a tax year to generate the summary report') }}</h5>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
