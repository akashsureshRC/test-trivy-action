@extends('layouts.main')

@section('page-title')
    {{ __('Payment Processing') }}
@endsection

@section('page-breadcrumb')
    {{ __('My Billing') }} / {{ __('Payment Processing') }}
@endsection

@push('css')
<style>
.pending-card {
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    overflow: hidden;
    max-width: 500px;
    margin: 0 auto;
}
.pending-header {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    padding: 40px 30px;
    text-align: center;
}
.pending-icon {
    width: 80px;
    height: 80px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}
.pending-icon i {
    font-size: 40px;
}
.pending-header h2 {
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
    border-top: 2px solid #f59e0b;
    padding-top: 15px;
    margin-top: 10px;
}
.spinner-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 15px;
    background: #fef3c7;
    border-radius: 8px;
    margin-bottom: 20px;
}
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="pending-card card">
            <div class="pending-header">
                <div class="pending-icon">
                    <i class="ti ti-clock"></i>
                </div>
                <h2>{{ __('Payment Processing') }}</h2>
                <p class="mb-0 opacity-75">{{ __('Please wait while we confirm your payment') }}</p>
            </div>
            
            <div class="card-body p-4">
                <!-- Processing Status -->
                <div class="spinner-container">
                    <div class="spinner-border spinner-border-sm text-warning" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="text-warning fw-bold">{{ __('Confirming payment with PayFast...') }}</span>
                </div>

                <!-- Payment Details -->
                <div class="payment-details">
                    <div class="detail-row">
                        <span>{{ __('Invoice Number') }}</span>
                        <span class="fw-bold">{{ $invoice->invoice_number }}</span>
                    </div>
                    <div class="detail-row">
                        <span>{{ __('Payment Reference') }}</span>
                        <span><code>{{ $payment->payment_number }}</code></span>
                    </div>
                    <div class="detail-row">
                        <span>{{ __('Status') }}</span>
                        <span class="badge bg-warning">{{ __('Pending Confirmation') }}</span>
                    </div>
                    <div class="detail-row total">
                        <span>{{ __('Amount') }}</span>
                        <span class="text-primary">R {{ number_format($payment->amount, 2) }}</span>
                    </div>
                </div>

                <!-- Info Message -->
                <div class="alert alert-info">
                    <i class="ti ti-info-circle me-2"></i>
                    {{ __('Your payment is being processed. This usually takes a few seconds. You will receive an email confirmation once the payment is complete.') }}
                </div>

                <!-- Note about page refresh -->
                <p class="text-muted small text-center mb-4">
                    {{ __('This page will automatically refresh to check payment status.') }}
                </p>

                <!-- Actions -->
                <div class="d-grid gap-2">
                    <a href="{{ route('my-billing.invoices.show', $invoice->id) }}" class="btn btn-rc-primary">
                        <i class="ti ti-file-invoice me-2"></i>{{ __('View Invoice') }}
                    </a>
                    <a href="{{ route('my-billing.invoices') }}" class="btn btn-rc-outline">
                        <i class="ti ti-arrow-left me-2"></i>{{ __('Back to Invoices') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-refresh page to check payment status
setTimeout(function() {
    window.location.href = '{{ route('my-billing.invoices.show', $invoice->id) }}';
}, 10000); // Redirect after 10 seconds

// Also check payment status via AJAX
let checkCount = 0;
const maxChecks = 6;

function checkPaymentStatus() {
    if (checkCount >= maxChecks) {
        window.location.href = '{{ route('my-billing.invoices.show', $invoice->id) }}';
        return;
    }
    
    fetch('{{ route('my-billing.invoices.show', $invoice->id) }}', {
        method: 'HEAD'
    }).then(function() {
        // Redirect to invoice page to see updated status
        window.location.href = '{{ route('my-billing.invoices.show', $invoice->id) }}';
    }).catch(function() {
        checkCount++;
        setTimeout(checkPaymentStatus, 5000);
    });
}

// Start checking after 5 seconds
setTimeout(checkPaymentStatus, 5000);
</script>
@endpush
