@extends('layouts.main')

@section('page-title')
    {{ __('Payment Cancelled') }}
@endsection

@section('page-breadcrumb')
    {{ __('My Billing') }} / {{ __('Payment Cancelled') }}
@endsection

@push('css')
<style>
.cancel-card {
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    overflow: hidden;
    text-align: center;
    max-width: 500px;
    margin: 0 auto;
}
.cancel-icon {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 50px;
    color: #f59e0b;
}
.cancel-header {
    padding: 40px 30px 20px;
}
.cancel-body {
    padding: 0 30px 30px;
}
.retry-btn {
    background: #3956ca;
    border: none;
    color: white;
    padding: 12px 30px;
    font-size: 16px;
    border-radius: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.retry-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(57, 86, 202, 0.3);
    color: white;
}
.help-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-top: 20px;
}
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="cancel-card card">
            <div class="cancel-header">
                <div class="cancel-icon">
                    <i class="ti ti-x"></i>
                </div>
                <h3 class="mb-2">{{ __('Payment Cancelled') }}</h3>
                <p class="text-muted">{{ __('You have cancelled the payment process.') }}</p>
            </div>
            
            <div class="cancel-body">
                @if(isset($invoice) && $invoice)
                <div class="alert alert-info">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-info-circle me-2" style="font-size: 20px;"></i>
                        <div class="text-start">
                            <strong>{{ __('Invoice #') }}{{ $invoice->invoice_number }}</strong><br>
                            <span class="text-muted">{{ __('Amount Due:') }} R {{ number_format($invoice->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
                
                <p class="text-muted mb-4">
                    {{ __('Your invoice remains unpaid. You can try the payment again or choose an alternative payment method.') }}
                </p>
                
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="{{ route('my-billing.pay', $invoice->id) }}" class="retry-btn">
                        <i class="ti ti-refresh"></i>
                        {{ __('Try Again') }}
                    </a>
                    <a href="{{ route('my-billing.invoices.show', $invoice->id) }}" class="btn btn-rc-outline">
                        <i class="ti ti-file-invoice me-1"></i>
                        {{ __('View Invoice') }}
                    </a>
                </div>
                @else
                <p class="text-muted mb-4">
                    {{ __('No payment was processed. You can return to your invoices to try again.') }}
                </p>
                
                <a href="{{ route('my-billing.invoices') }}" class="retry-btn">
                    <i class="ti ti-arrow-left"></i>
                    {{ __('Back to Invoices') }}
                </a>
                @endif
                
                <!-- Help Section -->
                <div class="help-section text-start">
                    <h6 class="mb-3"><i class="ti ti-help-circle me-1"></i>{{ __('Need Help?') }}</h6>
                    <ul class="mb-0 small text-muted">
                        <li class="mb-2">{{ __('If you experienced issues with the payment, please try again.') }}</li>
                        <li class="mb-2">{{ __('For EFT payments, view the invoice to find banking details.') }}</li>
                        <li>{{ __('Contact support if you continue to experience problems.') }}</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Back Link -->
        <div class="text-center mt-3">
            <a href="{{ route('my-billing.invoices') }}" class="text-muted">
                <i class="ti ti-arrow-left me-1"></i>
                {{ __('Back to Invoices') }}
            </a>
        </div>
    </div>
</div>
@endsection
