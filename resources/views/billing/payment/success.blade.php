@extends('layouts.main')

@section('page-title')
    {{ __('Payment Successful') }}
@endsection

@section('page-breadcrumb')
    {{ __('My Billing') }} / {{ __('Payment Successful') }}
@endsection

@push('css')
<style>
.success-card {
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    overflow: hidden;
    max-width: 500px;
    margin: 0 auto;
}
.success-header {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 40px 30px;
    text-align: center;
}
.success-icon {
    width: 80px;
    height: 80px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}
.success-icon i {
    font-size: 40px;
}
.success-header h2 {
    color: #ffffff;
    margin-bottom: 5px;
}
.payment-details {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
}
.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px dashed #e0e0e0;
}
.detail-row:last-child {
    border-bottom: none;
}
.detail-row.total {
    font-weight: bold;
    font-size: 1.1rem;
    border-top: 2px solid #10b981;
    padding-top: 15px;
    margin-top: 10px;
}
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="success-card card">
            <div class="success-header">
                <div class="success-icon">
                    <i class="ti ti-check"></i>
                </div>
                <h2>{{ __('Payment Successful!') }}</h2>
                <p class="mb-0 opacity-75">{{ __('Thank you for your payment') }}</p>
            </div>
            
            <div class="card-body p-4">
                <!-- Payment Details -->
                <div class="payment-details">
                    <div class="detail-row">
                        <span>{{ __('Invoice Number') }}</span>
                        <span class="fw-bold">{{ $invoice->invoice_number }}</span>
                    </div>
                    <div class="detail-row">
                        <span>{{ __('Payment Reference') }}</span>
                        <span><code>{{ $payment->gateway_reference ?? $payment->payment_number }}</code></span>
                    </div>
                    <div class="detail-row">
                        <span>{{ __('Payment Date') }}</span>
                        <span>{{ $payment->paid_at ? formatDateTime($payment->paid_at) : formatDateTime(now()) }}</span>
                    </div>
                    <div class="detail-row">
                        <span>{{ __('Payment Method') }}</span>
                        <span>{{ ucfirst($payment->payment_method) }}</span>
                    </div>
                    <div class="detail-row total">
                        <span>{{ __('Amount Paid') }}</span>
                        <span class="text-success">R {{ number_format($payment->amount, 2) }}</span>
                    </div>
                </div>

                <!-- Receipt Info -->
                <div class="alert alert-info">
                    <i class="ti ti-mail me-2"></i>
                    {{ __('A receipt has been sent to your email address.') }}
                </div>

                <!-- Actions -->
                <div class="d-grid gap-2">
                    <a href="{{ route('my-billing.invoices.show', $invoice->id) }}" class="btn btn-rc-primary">
                        <i class="ti ti-file-invoice me-2"></i>{{ __('View Invoice') }}
                    </a>
                    <a href="{{ route('my-billing.invoices.download', $invoice->id) }}" class="btn btn-rc-success">
                        <i class="ti ti-download me-2"></i>{{ __('Download Receipt') }}
                    </a>
                    <a href="{{ route('my-billing.index') }}" class="btn btn-rc-outline">
                        <i class="ti ti-arrow-left me-2"></i>{{ __('Back to Billing Dashboard') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
