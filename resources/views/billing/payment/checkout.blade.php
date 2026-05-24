@extends('layouts.main')

@section('page-title')
    {{ __('Pay Invoice') }}
@endsection

@section('page-breadcrumb')
    {{ __('My Billing') }} / {{ __('Pay Invoice') }}
@endsection

@push('css')
<style>
.payment-card {
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    overflow: hidden;
}
.payment-header {
    background: #3956ca;
    color: white;
    padding: 30px;
}
.payment-header h3 {
    color: #ffffff;
}
.invoice-summary {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
}
.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px dashed #e0e0e0;
}
.summary-row:last-child {
    border-bottom: none;
}
.summary-row.total {
    font-weight: bold;
    font-size: 1.2rem;
    padding-top: 15px;
    border-top: 2px solid #3956ca;
    margin-top: 10px;
}
.payfast-btn {
    background: #3956ca;
    border: none;
    color: white;
    padding: 15px 40px;
    font-size: 18px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}
.payfast-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(11, 179, 110, 0.3);
    color: white;
}
.payfast-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}
.security-badges {
    display: flex;
    gap: 15px;
    justify-content: center;
    align-items: center;
    margin-top: 20px;
}
.security-badge {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #6c757d;
    font-size: 14px;
}
.sandbox-notice {
    background: #fef3c7;
    border: 1px solid #f59e0b;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 20px;
}
.payfast-logo {
    height: 30px;
    margin-top: 15px;
}
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="payment-card card">
            <div class="payment-header text-center">
                <h3 class="mb-2">{{ __('Invoice Payment') }}</h3>
                <p class="mb-0 opacity-75">{{ __('Invoice #') }}{{ $invoice->invoice_number }}</p>
            </div>
            
            <div class="card-body p-4">
                @if(isset($isSandbox) && $isSandbox)
                <!-- Sandbox Mode Notice -->
                <div class="sandbox-notice">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-flask text-warning me-2" style="font-size: 24px;"></i>
                        <div>
                            <strong>{{ __('Sandbox Mode') }}</strong>
                            <p class="mb-0 small text-muted">
                                {{ __('This is a test payment. Use PayFast sandbox credentials to test.') }}<br>
                                <span class="text-dark">{{ __('Email:') }} <code>sbtu01@payfast.io</code> | {{ __('Password:') }} <code>clientpass</code></span>
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Invoice Summary -->
                <div class="invoice-summary mb-4">
                    <h5 class="mb-3"><i class="ti ti-file-invoice me-2"></i>{{ __('Invoice Summary') }}</h5>
                    
                    <div class="summary-row">
                        <span>{{ __('Billing Period') }}</span>
                        <span>
                            @if($invoice->billingCycle && $invoice->billingCycle->start_date && $invoice->billingCycle->end_date)
                                {{ formatDate($invoice->billingCycle->start_date) }} - 
                                {{ formatDate($invoice->billingCycle->end_date) }}
                            @elseif($invoice->period_start && $invoice->period_end)
                                {{ formatDate($invoice->period_start) }} - 
                                {{ formatDate($invoice->period_end) }}
                            @elseif($invoice->created_at)
                                {{ formatShortMonthYear($invoice->created_at) }}
                            @else
                                {{ __('N/A') }}
                            @endif
                        </span>
                    </div>
                    
                    @if($invoice->total_payslips)
                    <div class="summary-row">
                        <span>{{ __('Payslips Processed') }}</span>
                        <span>{{ number_format($invoice->total_payslips) }}</span>
                    </div>
                    @endif
                    
                    <div class="summary-row">
                        <span>{{ __('Subtotal') }}</span>
                        <span>R {{ number_format($invoice->subtotal ?? 0, 2) }}</span>
                    </div>
                    
                    @if($invoice->tax_amount > 0)
                    <div class="summary-row">
                        <span>{{ __('VAT') }} ({{ $invoice->tax_percentage ?? 15 }}%)</span>
                        <span>R {{ number_format($invoice->tax_amount, 2) }}</span>
                    </div>
                    @endif
                    
                    <div class="summary-row total">
                        <span>{{ __('Total Due') }}</span>
                        <span class="text-primary">R {{ number_format($invoice->total_amount, 2) }}</span>
                    </div>
                </div>
                
                <!-- Due Date Warning -->
                @if($invoice->due_date && $invoice->due_date->isPast())
                    <div class="alert alert-danger mb-4">
                        <i class="ti ti-alert-circle me-2"></i>
                        {{ __('This invoice is overdue. Please pay immediately to avoid service interruption.') }}
                    </div>
                @elseif($invoice->due_date && $invoice->due_date->diffInDays(now()) <= 3)
                    <div class="alert alert-warning mb-4">
                        <i class="ti ti-clock me-2"></i>
                        {{ __('This invoice is due on') }} {{ formatDate($invoice->due_date) }}
                    </div>
                @endif
                
                <!-- PayFast Payment Form -->
                <div class="text-center">
                    <form action="{{ $payFastUrl }}" method="POST" id="payfast-form">
                        @foreach($payFastData as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        
                        <button type="submit" class="payfast-btn" id="pay-btn">
                            <i class="ti ti-lock"></i>
                            {{ __('Pay Now with PayFast') }}
                        </button>
                    </form>
                    
                    <!-- Security Info -->
                    <div class="security-badges">
                        <span class="security-badge">
                            <i class="ti ti-shield-check text-success"></i>
                            {{ __('256-bit SSL') }}
                        </span>
                        <span class="security-badge">
                            <i class="ti ti-lock text-success"></i>
                            {{ __('Secure Payment') }}
                        </span>
                        <span class="security-badge">
                            <i class="ti ti-credit-card text-success"></i>
                            {{ __('PCI Compliant') }}
                        </span>
                    </div>
                    
                    <p class="text-muted small mt-3">
                        {{ __('You will be redirected to PayFast to complete your payment securely.') }}
                    </p>

                    <!-- PayFast Logo -->
                    <img src="https://www.payfast.co.za/assets/images/payfast_logo_colour.svg" 
                         alt="PayFast" class="payfast-logo" onerror="this.style.display='none'">
                </div>
                
                <hr class="my-4">
                
                <!-- Alternative Payment -->
                <div class="text-center">
                    <p class="text-muted mb-2">{{ __('Prefer to pay via EFT?') }}</p>
                    <a href="{{ route('my-billing.invoices.show', $invoice->id) }}" class="btn btn-rc-outline btn-sm">
                        <i class="ti ti-building-bank me-1"></i>
                        {{ __('View Banking Details') }}
                    </a>
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

@push('scripts')
<script>
document.getElementById('payfast-form').addEventListener('submit', function(e) {
    var btn = document.getElementById('pay-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="ti ti-loader ti-spin"></i> {{ __("Redirecting to PayFast...") }}';
});
</script>
@endpush
