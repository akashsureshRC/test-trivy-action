@extends('layouts.main')

@section('page-title')
    {{ __('Usage History') }}
@endsection

@section('page-breadcrumb')
    {{ __('My Billing') }},{{ __('Usage History') }}
@endsection

@push('css')
<style>
.tier-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.tier-badge.tier-1 { background: #dbeafe; color: #1d4ed8; }
.tier-badge.tier-2 { background: #dcfce7; color: #15803d; }
.tier-badge.tier-3 { background: #fef3c7; color: #d97706; }
.tier-badge.tier-4 { background: #fce7f3; color: #be185d; }
.tier-badge.tier-5 { background: #ede9fe; color: #7c3aed; }
.filter-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <x-rc-table>
            <x-rc-table.filter action="{{ route('my-billing.usage') }}" method="GET">
                <x-rc-table.filter-group label="{{ __('Filter by Month') }}">
                    <select name="month" class="form-select">
                        <option value="">{{ __('All Months') }}</option>
                        @foreach($months as $month)
                            <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
                                {{ formatMonthYear($month) }}
                            </option>
                        @endforeach
                    </select>
                </x-rc-table.filter-group>
                <x-rc-table.filter-group label="{{ __('Filter by Status') }}">
                    <select name="status" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="invoiced" {{ request('status') == 'invoiced' ? 'selected' : '' }}>{{ __('Invoiced') }}</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                    </select>
                </x-rc-table.filter-group>
            </x-rc-table.filter>

            <x-rc-table.content>
                <table class="rc-table">
                    <thead>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('Workspace') }}</th>
                            <th>{{ __('Month') }}</th>
                            <th>{{ __('Tier') }}</th>
                            <th class="text-end">{{ __('Amount') }}</th>
                            <th class="text-center">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usages as $usage)
                        <tr>
                            <td>{{ formatDateTime($usage->created_at) }}</td>
                            <td>{{ $usage->payslip?->employee_profile?->full_name ?? '-' }}</td>
                            <td>{{ $usage->workspace?->name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ formatShortMonthYear($usage->salary_month) }}
                                </span>
                            </td>
                            <td>
                                @if($usage->tier)
                                    <span class="tier-badge tier-{{ $usage->tier->sort_order }}">
                                        {{ $usage->tier->name }}
                                    </span>
                                @else
                                    <span class="tier-badge tier-1">{{ __('Trial') }}</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold">
                                @if($usage->amount_charged > 0)
                                    R{{ number_format($usage->amount_charged, 2) }}
                                @else
                                    <span class="text-success">{{ __('Free') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($usage->status == 'pending')
                                    <span class="badge bg-warning">{{ __('Pending') }}</span>
                                @elseif($usage->status == 'invoiced')
                                    <span class="badge bg-info">{{ __('Invoiced') }}</span>
                                @else
                                    <span class="badge bg-success">{{ __('Paid') }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                            <x-rc-table.empty :asRow="true" :colspan="7" icon="ti ti-file-invoice" title="{{ __('No usage records found') }}" message="{{ __('Generate payslips to see your usage history here.') }}" />
                        @endforelse
                    </tbody>
                </table>
            </x-rc-table.content>

            <x-rc-table.footer :paginator="$usages" />
        </x-rc-table>
    </div>
</div>
@endsection
