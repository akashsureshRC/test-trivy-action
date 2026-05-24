@extends('layouts.main')

@section('page-title')
    {{ __('Payment Processing') }}
@endsection

@section('page-breadcrumb')
    {{ __('My Billing') }} / {{ __('Payment') }}
@endsection

@push('css')
<style>
.return-card {
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    overflow: hidden;
    max-width: 500px;
    margin: 0 auto;
}
.return-header {
    padding: 40px 30px;
    text-align: center;
    color: white;
}
.return-header.pending {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}
.return-header.success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}
.return-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 40px;
}
.status-pending {
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="return-card card">
            @if($payment->status === 'completed')
                <div class="return-header success">
                    <div class="return-icon">
                        <i class="ti ti-check"></i>
                    </div>
                    <h3 class="mb-2">{{ __('Payment Successful!') }}</h3>
                    <p class="mb-0 opacity-75">{{ __('Thank you for your payment') }}</p>
                </div>
                
                <div class="card-body p-4 text-center">
                    <div class="mb-4">
                        <p class="text-muted mb-1">{{ __('Invoice Number') }}</p>
                        <h5>{{ $invoice->invoice_number }}</h5>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-muted mb-1">{{ __('Amount Paid') }}</p>
                        <h4 class="text-success">R {{ number_format($payment->amount, 2) }}</h4>
                    </div>
                    
                    @if($payment->payment_reference)
                    <div class="mb-4">
                        <p class="text-muted mb-1">{{ __('Payment Reference') }}</p>
                        <p class="mb-0"><code>{{ $payment->payment_reference }}</code></p>
                    </div>
                    @endif
                    
                    <div class="alert alert-success">
                        <i class="ti ti-check me-2"></i>
                        {{ __('Your account is now in good standing.') }}
                    </div>
                    
                    <a href="{{ route('my-billing.invoices.show', $invoice->id) }}" class="btn btn-rc-primary">
                        <i class="ti ti-file-invoice me-1"></i>
                        {{ __('View Invoice') }}
                    </a>
                </div>
            @else
                <div class="return-header pending">
                    <div class="return-icon status-pending">
                        <i class="ti ti-loader"></i>
                    </div>
                    <h3 class="mb-2">{{ __('Processing Payment') }}</h3>
                    <p class="mb-0 opacity-75">{{ __('Please wait while we confirm your payment') }}</p>
                </div>
                
                <div class="card-body p-4 text-center">
                    <div class="mb-4">
                        <p class="text-muted mb-1">{{ __('Invoice Number') }}</p>
                        <h5>{{ $invoice->invoice_number }}</h5>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-muted mb-1">{{ __('Amount') }}</p>
                        <h4>R {{ number_format($invoice->total_amount, 2) }}</h4>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="ti ti-info-circle me-2"></i>
                        {{ __('Your payment is being processed by PayFast. This usually takes a few seconds.') }}
                    </div>
                    
                    <p class="text-muted small mb-4">
                        {{ __('If your payment was successful, this page will update automatically. You can also check your invoice status.') }}
                    </p>
                    
                    <a href="{{ route('my-billing.invoices.show', $invoice->id) }}" class="btn btn-rc-primary">
                        <i class="ti ti-file-invoice me-1"></i>
                        {{ __('Check Invoice Status') }}
                    </a>
                </div>
            @endif
        </div>
        
        <div class="text-center mt-3">
            <a href="{{ route('my-billing.index') }}" class="text-muted">
                <i class="ti ti-arrow-left me-1"></i>
                {{ __('Back to Billing Dashboard') }}
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($payment->status !== 'completed')
<script>
    // Auto-refresh to check payment status
    setTimeout(function() {
        window.location.reload();
    }, 5000);
</script>
@endif
@endpush
