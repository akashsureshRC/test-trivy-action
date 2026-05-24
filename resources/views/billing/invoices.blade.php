@extends('layouts.main')

@section('page-title')
    {{ __('Invoices') }}
@endsection

@section('page-breadcrumb')
    {{ __('My Billing') }},{{ __('Invoices') }}
@endsection

@push('css')
<style>
/* Billing-specific stat card styles */
.billing-stat-card {
    border: 1px solid #e6e6e6;
    border-radius: 8px;
    padding: 20px;
    background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
    transition: all 0.3s ease;
    height: 100%;
}
.billing-stat-card:hover {
    box-shadow: 0 4px 15px rgba(57, 86, 202, 0.1);
}
.billing-stat-card .stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}
.billing-stat-card .stat-value {
    font-size: 24px;
    font-weight: 700;
    color: var(--rc-primary);
}
.billing-stat-card .stat-label {
    color: #6c757d;
    font-size: 14px;
}
</style>
@endpush

@section('content')
<div class="row">
    <!-- Summary Stats -->
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="billing-stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-info bg-opacity-10 text-primary me-3">
                    <i class="ti ti-file-invoice"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $invoices->total() }}</div>
                    <div class="stat-label">{{ __('Total Invoices') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="billing-stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="ti ti-clock"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $invoices->where('status', 'pending')->count() }}</div>
                    <div class="stat-label">{{ __('Pending') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="billing-stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="ti ti-check"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $invoices->where('status', 'paid')->count() }}</div>
                    <div class="stat-label">{{ __('Paid') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="billing-stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                    <i class="ti ti-alert-triangle"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $invoices->filter(function($inv) { return $inv->status === 'pending' && $inv->due_date && $inv->due_date->isPast(); })->count() }}</div>
                    <div class="stat-label">{{ __('Overdue') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices List -->
    <div class="col-12">
        <x-rc-table>
            <x-rc-table.content>
                <table class="rc-table">
                    <thead>
                        <tr>
                            <th>{{ __('Invoice #') }}</th>
                            <th class="col-date">{{ __('Date') }}</th>
                            <th>{{ __('Billing Period') }}</th>
                            <th>{{ __('Payslips') }}</th>
                            <th class="col-amount">{{ __('Amount') }}</th>
                            <th class="col-status">{{ __('Status') }}</th>
                            <th class="col-date">{{ __('Due Date') }}</th>
                            <th class="col-actions">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                        @php
                            $isPastDue = $invoice->due_date && $invoice->due_date->isPast() && $invoice->status !== 'paid';
                            $statusClass = $isPastDue
                                ? 'rc-status-danger'
                                : match($invoice->status) {
                                    'paid' => 'rc-status-success',
                                    'pending' => 'rc-status-warning',
                                    'cancelled' => 'rc-status-secondary',
                                    default => 'rc-status-info'
                                };
                            $statusLabel = $isPastDue ? __('Past Due') : \Illuminate\Support\Str::title($invoice->status_display);
                        @endphp
                        <tr>
                            <td class="col-sno">
                                <a href="{{ route('my-billing.invoices.show', $invoice->id) }}" class="fw-bold text-primary">
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td class="col-date">{{ formatDate($invoice->created_at) }}</td>
                            <td>
                                @if($invoice->billingCycle)
                                    {{ $invoice->billingCycle->period_label }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ $invoice->total_payslips ?? $invoice->items->sum('quantity') }}
                                </span>
                            </td>
                            <td class="col-amount">
                                <span class="fw-bold">R{{ number_format($invoice->total_amount, 2) }}</span>
                            </td>
                            <td class="col-status">
                                <span class="rc-status {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="col-date">
                                @if($invoice->due_date)
                                    @if($isPastDue)
                                        <span class="text-danger fw-bold">{{ formatDate($invoice->due_date) }}</span>
                                    @else
                                        {{ formatDate($invoice->due_date) }}
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="col-actions">
                                <a href="{{ route('my-billing.invoices.show', $invoice->id) }}" class="rc-table-action rc-table-action-view" title="{{ __('View') }}">
                                    <i class="ti ti-eye"></i>
                                </a>
                                <a href="{{ route('my-billing.invoices.download', $invoice->id) }}" class="rc-table-action rc-table-action-success" title="{{ __('Download PDF') }}">
                                    <i class="ti ti-download"></i>
                                </a>
                                @if($invoice->status == 'pending' || $invoice->status == 'overdue')
                                    <a href="{{ route('my-billing.pay', $invoice->id) }}" class="rc-table-action rc-table-action-primary" title="{{ __('Pay Now') }}">
                                        <i class="ti ti-credit-card"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <x-rc-table.empty :asRow="true" :colspan="8" icon="ti ti-file-dollar" title="{{ __('No invoices yet') }}" message="{{ __('Invoices will appear here at the end of each billing cycle.') }}" />
                        @endforelse
                    </tbody>
                </table>
            </x-rc-table.content>

            <!-- Pagination -->
            @if($invoices->hasPages())
            <div class="mt-4 px-3 pb-3">
                {{ $invoices->links() }}
            </div>
            @endif
        </x-rc-table>
    </div>
</div>
@endsection
