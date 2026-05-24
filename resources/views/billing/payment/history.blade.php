@extends('layouts.main')

@section('page-title')
    {{ __('Payment History') }}
@endsection

@section('page-breadcrumb')
    {{ __('My Billing') }} / {{ __('Payment History') }}
@endsection

@push('css')
<style>
.payment-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}
.payment-status.completed {
    background: #d1fae5;
    color: #065f46;
}
.payment-status.pending {
    background: #fef3c7;
    color: #92400e;
}
.payment-status.failed {
    background: #fee2e2;
    color: #991b1b;
}
.method-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    background: #f3f4f6;
    color: #4b5563;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <x-rc-table title="{{ __('Payment History') }}" titleIcon="ti ti-history">
            <x-slot name="headerActions">
                <a href="{{ route('my-billing.invoices') }}" class="btn btn-rc-outline btn-sm">
                    <i class="ti ti-file-invoice me-1"></i>{{ __('View Invoices') }}
                </a>
            </x-slot>
            
            <x-rc-table.content>
                <table class="rc-table">
                    <thead>
                        <tr>
                            <th class="col-date">{{ __('Date') }}</th>
                            <th>{{ __('Invoice') }}</th>
                            <th class="col-amount">{{ __('Amount') }}</th>
                            <th>{{ __('Method') }}</th>
                            <th>{{ __('Reference') }}</th>
                            <th class="col-status">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td class="col-date">
                                <span class="fw-medium">{{ formatDate($payment->created_at) }}</span>
                                <br>
                                <small class="text-muted">{{ formatTime($payment->created_at) }}</small>
                            </td>
                            <td>
                                @if($payment->invoice)
                                    <a href="{{ route('my-billing.invoices.show', $payment->invoice_id) }}">
                                        {{ $payment->invoice->invoice_number }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="col-amount fw-bold">R {{ number_format($payment->amount, 2) }}</td>
                            <td>
                                <span class="method-badge">
                                    @if($payment->payment_method === 'payfast')
                                        <i class="ti ti-credit-card"></i> PayFast
                                    @elseif($payment->payment_method === 'eft')
                                        <i class="ti ti-building-bank"></i> EFT
                                    @elseif($payment->payment_method === 'manual')
                                        <i class="ti ti-edit"></i> Manual
                                    @else
                                        <i class="ti ti-wallet"></i> {{ ucfirst($payment->payment_method) }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if($payment->payment_reference)
                                    <code class="small">{{ Str::limit($payment->payment_reference, 20) }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="col-status">
                                @if($payment->status === 'completed')
                                    <span class="rc-status rc-status-success">
                                        <i class="ti ti-check"></i> {{ __('Completed') }}
                                    </span>
                                @elseif($payment->status === 'pending')
                                    <span class="rc-status rc-status-warning">
                                        <i class="ti ti-clock"></i> {{ __('Pending') }}
                                    </span>
                                @else
                                    <span class="rc-status rc-status-danger">
                                        <i class="ti ti-x"></i> {{ __('Failed') }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <x-rc-table.empty 
                            :asRow="true" 
                            :colspan="6" 
                            icon="ti ti-receipt-off" 
                            title="{{ __('No payment history yet') }}" 
                            message="{{ __('Your payment transactions will appear here') }}" 
                        />
                        @endforelse
                    </tbody>
                </table>
            </x-rc-table.content>
            
            @if($payments->count() > 0)
            <x-slot name="footer">
                <div class="d-flex justify-content-center">
                    {{ $payments->links() }}
                </div>
            </x-slot>
            @endif
        </x-rc-table>
    </div>
</div>
@endsection
