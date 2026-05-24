@extends('layouts.main')

@section('page-title')
    {{ __('Invoice') }} {{ $invoice->invoice_number }}
@endsection

@section('page-breadcrumb')
    {{ __('My Billing') }},{{ __('Invoices') }},{{ $invoice->invoice_number }}
@endsection

@push('css')
<style>
.invoice-header {
    background: #3956ca;
    color: white;
    border-radius: 12px 12px 0 0;
    padding: 30px;
}
.invoice-header h2, .invoice-header h4 {
    color: #fff;
}
.invoice-status {
    padding: 8px 20px;
    border-radius: 25px;
    font-weight: 600;
    display: inline-block;
}
.invoice-status.pending { background: #fef3c7; color: #d97706; }
.invoice-status.paid { background: #dcfce7; color: #15803d; }
.invoice-status.overdue { background: #fecaca; color: #dc2626; }
.invoice-status.cancelled { background: #e5e7eb; color: #6b7280; }
.invoice-detail-card {
    border: none;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border-radius: 12px;
    overflow: hidden;
}
.invoice-table th {
    background: #f8f9fa;
    font-weight: 600;
}
.invoice-table td, .invoice-table th {
    padding: 15px;
}
.invoice-table td {
    padding-left: 0.75rem !important;
    padding-right: 0.75rem !important;
}
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
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div>
        <div class="card invoice-detail-card table-card">
            <!-- Invoice Header -->
            <div class="invoice-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h2 class="mb-1">{{ __('Invoice') }}</h2>
                        <h4 class="mb-0">{{ $invoice->invoice_number }}</h4>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        @php
                            $isPastDue = $invoice->due_date && \Carbon\Carbon::parse($invoice->due_date)->isPast() && $invoice->status !== 'paid';
                            $statusKey = $isPastDue ? 'overdue' : $invoice->status;
                            $statusLabel = $isPastDue ? __('Past Due') : \Illuminate\Support\Str::title($invoice->status_display);
                        @endphp
                        <span class="invoice-status {{ $statusKey }}">
                            <i class="ti ti-{{ $statusKey === 'paid' ? 'check' : ($statusKey === 'overdue' ? 'alert-triangle' : 'clock') }} me-1"></i>
                            {{ $statusLabel }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Invoice Body -->
            <div class="card-body p-4">
                <!-- Company and Bill To Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">{{ __('From') }}</h6>
                        <p class="mb-1 fw-bold">{{ $company['name'] ?? 'RC ClearPay' }}</p>
                        @if(!empty($company['address']))<p class="mb-1 text-muted small">{{ $company['address'] }}</p>@endif
                        @if(!empty($company['phone']))<p class="mb-1 text-muted small"><i class="ti ti-phone me-1"></i>{{ $company['phone'] }}</p>@endif
                        @if(!empty($company['email']))<p class="mb-1 text-muted small"><i class="ti ti-mail me-1"></i>{{ $company['email'] }}</p>@endif
                        @if(!empty($company['vat_number']))<p class="mb-0 text-muted small">VAT: {{ $company['vat_number'] }}</p>@endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">{{ __('Bill To') }}</h6>
                        <p class="mb-1 fw-bold">{{ Auth::user()->name }}</p>
                        <p class="mb-1 text-muted small"><i class="ti ti-mail me-1"></i>{{ Auth::user()->email }}</p>
                    </div>
                </div>

                <hr>

                <!-- Invoice Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">{{ __('Billing Period') }}</h6>
                        @if($invoice->billingCycle)
                            <p class="mb-1"><i class="ti ti-calendar me-2"></i>{{ $invoice->billingCycle->period_label }}</p>
                        @endif
                        <p class="mb-1"><i class="ti ti-clock me-2"></i>{{ __('Issue Date') }}: {{ formatDate($invoice->created_at) }}</p>
                        @if($invoice->due_date)
                            <p class="mb-0">
                                <i class="ti ti-calendar-event me-2"></i>{{ __('Due Date') }}: 
                                <span class="{{ $isPastDue ? 'text-danger fw-bold' : '' }}">
                                    {{ formatDate($invoice->due_date) }}
                                </span>
                            </p>
                        @endif
                    </div>
                    <div class="col-md-6 text-md-end total-amount-section">
                        <h6 class="text-muted mb-3">{{ __('Total Amount') }}</h6>
                        <h2 class="text-primary mb-0">R{{ number_format($invoice->total_amount, 2) }}</h2>
                        @if($invoice->tax_amount > 0)
                            <small class="text-muted">({{ __('incl. VAT') }} R{{ number_format($invoice->tax_amount, 2) }})</small>
                        @endif
                    </div>
                </div>

                <!-- Invoice Items -->
                <x-rc-table title="{{ __('Invoice Items') }}">
                    <x-rc-table.content>
                        <table class="rc-table invoice-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Description') }}</th>
                                    <th class="text-center">{{ __('Quantity') }}</th>
                                    <th class="text-end col-amount">{{ __('Unit Price') }}</th>
                                    <th class="text-end col-amount">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->items as $item)
                                <tr>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end col-amount">R{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end col-amount fw-bold">R{{ number_format($item->amount, 2) }}</td>
                                </tr>
                                @empty
                                <x-rc-table.empty :asRow="true" :colspan="4" icon="ti ti-file-invoice" title="{{ __('No items') }}" message="{{ __('This invoice has no line items.') }}" />
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end py-0"><strong>{{ __('Subtotal') }}</strong></td>
                                    <td class="text-end col-amount py-0">R{{ number_format($invoice->subtotal, 2) }}</td>
                                </tr>
                                @if($invoice->tax_amount > 0)
                                <tr>
                                    <td colspan="3" class="text-end py-0">{{ __('VAT') }} ({{ $invoice->tax_percentage ?? 15 }}%)</td>
                                    <td class="text-end col-amount py-0">R{{ number_format($invoice->tax_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="table-primary">
                                    <td colspan="3" class="text-end pt-0"><strong>{{ __('Total') }}</strong></td>
                                    <td class="text-end col-amount pt-0"><strong class="fs-5">R{{ number_format($invoice->total_amount, 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </x-rc-table.content>
                </x-rc-table>

                <!-- Payment Details -->
                @if($invoice->payments && $invoice->payments->count() > 0)
                <x-rc-table title="{{ __('Payment Details') }}" class="mt-4">
                    <x-rc-table.content>
                        <table class="rc-table">
                            <thead>
                                <tr>
                                    <th class="col-date">{{ __('Date') }}</th>
                                    <th>{{ __('Method') }}</th>
                                    <th>{{ __('Reference') }}</th>
                                    <th class="text-end col-amount">{{ __('Amount') }}</th>
                                    <th class="text-end col-status">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->payments as $payment)
                                <tr>
                                    <td class="col-date">{{ formatDateTime($payment->created_at) }}</td>
                                    <td>{{ \App\Models\Billing\BillingPayment::getMethodDisplayName($payment->payment_method) }}</td>
                                    <td>{{ $payment->gateway_reference ?? $payment->payment_reference ?? '-' }}</td>
                                    <td class="text-end col-amount">R{{ number_format($payment->amount, 2) }}</td>
                                    <td class="text-end col-status">
                                        @if($payment->status == 'completed')
                                            <span class="rc-status rc-status-success">{{ __('Completed') }}</span>
                                        @elseif($payment->status == 'pending')
                                            <span class="rc-status rc-status-warning">{{ __('Pending') }}</span>
                                        @else
                                            <span class="rc-status rc-status-danger">{{ __('Failed') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-rc-table.content>
                </x-rc-table>
                @endif

                <!-- Bank Details (for unpaid invoices) -->
                @if($invoice->status !== 'paid' && (!empty($company['bank_name']) || !empty($company['bank_account_number'])))
                <hr>
                <h6 class="mb-3"><i class="ti ti-building-bank me-2"></i>{{ __('Bank Details for EFT Payments') }}</h6>
                <div class="bg-light p-3 rounded">
                    <div class="row">
                        <div class="col-md-6">
                            @if(!empty($company['bank_name']))
                            <p class="mb-1"><strong>{{ __('Bank') }}:</strong> {{ $company['bank_name'] }}</p>
                            @endif
                            @if(!empty($company['bank_account_name']))
                            <p class="mb-1"><strong>{{ __('Account Name') }}:</strong> {{ $company['bank_account_name'] }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if(!empty($company['bank_account_number']))
                            <p class="mb-1"><strong>{{ __('Account Number') }}:</strong> {{ $company['bank_account_number'] }}</p>
                            @endif
                            @if(!empty($company['bank_branch_code']))
                            <p class="mb-1"><strong>{{ __('Branch Code') }}:</strong> {{ $company['bank_branch_code'] }}</p>
                            @endif
                        </div>
                    </div>
                    <p class="mb-0 mt-2 text-muted small"><strong>{{ __('Reference') }}:</strong> {{ $invoice->invoice_number }}</p>
                </div>

                <!-- EFT Proof Submission Section -->
                <div class="mt-4 p-3 border rounded">
                    <h6 class="mb-3"><i class="ti ti-upload me-2"></i>{{ __('Already Paid via EFT?') }}</h6>
                    <p class="text-muted small mb-3">{{ __('If you have already made an EFT payment, please upload your proof of payment below for faster processing.') }}</p>
                    
                    @if(isset($eftSubmissions) && $eftSubmissions->count() > 0)
                    <div class="mb-3">
                        <h6 class="small text-muted">{{ __('Previous Submissions') }}</h6>
                        @foreach($eftSubmissions as $submission)
                        <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded mb-2">
                            <div>
                                <span class="fw-bold">{{ $submission->bank_reference }}</span>
                                <small class="text-muted d-block">R{{ number_format($submission->amount, 2) }} - {{ formatDate($submission->payment_date) }}</small>
                            </div>
                            <span class="badge {{ $submission->status_badge_class }}">
                                {{ ucfirst($submission->status) }}
                            </span>
                        </div>
                        @if($submission->status === 'rejected' && $submission->rejection_reason)
                        <div class="alert alert-danger small py-2 mb-2">
                            <strong>{{ __('Rejection Reason') }}:</strong> {{ $submission->rejection_reason }}
                        </div>
                        @endif
                        @endforeach
                    </div>
                    @endif

                    <button type="button" class="btn btn-rc-outline btn-sm" data-bs-toggle="modal" data-bs-target="#eftProofModal">
                        <i class="ti ti-upload me-1"></i>{{ __('Submit Payment Proof') }}
                    </button>
                </div>
                @endif

                <!-- Actions -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <a href="{{ route('my-billing.invoices') }}" class="btn btn-rc-outline">
                            <i class="ti ti-arrow-left me-1"></i>{{ __('Back to Invoices') }}
                        </a>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <a href="{{ route('my-billing.invoices.download', $invoice->id) }}" class="btn btn-rc-success me-2">
                            <i class="ti ti-download me-1"></i>{{ __('Download PDF') }}
                        </a>
                        <button type="button" class="btn btn-rc-outline me-2" onclick="window.print()">
                            <i class="ti ti-printer me-1"></i>{{ __('Print') }}
                        </button>
                        @if($invoice->status == 'pending' || $invoice->status == 'overdue')
                            <a href="{{ route('my-billing.pay', $invoice->id) }}" class="btn btn-rc-primary">
                                <i class="ti ti-credit-card me-1"></i>{{ __('Pay Now') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
@media print {
    /* Hide everything not needed for print */
    .loader-bg,
    .dash-sidebar,
    .sidebar,
    nav.dash-sidebar,
    .dash-header,
    header,
    .page-header,
    .page-block,
    .breadcrumb,
    .btn,
    .footer,
    footer,
    .row.mt-4,
    .print-hide,
    .ti-calendar,
    .ti-calendar-event,
    .ti-clock {
        display: none !important;
        visibility: hidden !important;
    }
    
    /* Reset body and html */
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
        font-size: 12px !important;
        color: #000 !important;
    }
    
    /* Reset container */
    .dash-container,
    .dash-content,
    section.dash-container {
        margin: 0 !important;
        padding: 0 !important;
        margin-left: 0 !important;
        width: 100% !important;
    }
    
    /* Full width columns */
    .row.justify-content-center,
    .col-lg-10 {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    /* Card styling - plain */
    .card,
    .invoice-detail-card {
        box-shadow: none !important;
        border: none !important;
        border-radius: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    /* Card body consistent padding */
    .card-body {
        padding: 0 !important;
    }
    
    /* Header - plain black text on white */
    .invoice-header {
        background: white !important;
        color: #000 !important;
        border-radius: 0 !important;
        padding: 0 0 15px 0 !important;
        margin-bottom: 20px !important;
        border-bottom: 2px solid #000 !important;
    }
    
    .invoice-header .row {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .invoice-header h2,
    .invoice-header h4 {
        color: #000 !important;
        margin: 0 !important;
    }
    
    .invoice-header h2 {
        margin-bottom: 5px !important;
    }
    
    /* Status - plain text */
    .invoice-status {
        background: none !important;
        border: none !important;
        border-radius: 0 !important;
        color: #000 !important;
        padding: 0 !important;
        font-weight: bold !important;
    }
    
    /* Invoice info section */
    .row.mb-4 {
        margin: 0 0 20px 0 !important;
        padding: 0 !important;
    }
    
    .row.mb-4 .col-md-6 {
        padding: 0 !important;
    }
    
    .row.mb-4 h6 {
        margin-bottom: 10px !important;
    }
    
    .row.mb-4 p {
        margin-bottom: 5px !important;
    }
    
    .row.mb-4 h2 {
        margin: 0 !important;
    }
    
    /* HR spacing */
    hr {
        margin: 20px 0 !important;
        border: none !important;
        border-top: 1px solid #ccc !important;
    }
    
    /* Section headings */
    h6.mb-3 {
        margin: 0 0 15px 0 !important;
        font-size: 14px !important;
        font-weight: bold !important;
    }
    
    /* Table styling - gray shades */
    .table-responsive {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .invoice-table,
    .table {
        border-collapse: collapse !important;
        width: 100% !important;
        margin: 0 0 20px 0 !important;
    }
    
    .invoice-table th,
    .table th {
        background: #f0f0f0 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        border: 1px solid #ccc !important;
        color: #000 !important;
        font-weight: bold !important;
        padding: 10px !important;
    }
    
    .invoice-table td,
    .table td {
        border: 1px solid #ccc !important;
        color: #000 !important;
        padding: 10px !important;
    }
    
    .invoice-table tfoot td {
        padding: 8px 10px !important;
    }
    
    .table-primary,
    .table-primary td {
        background: #e8e8e8 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        font-weight: bold !important;
    }
    
    .table-light,
    .table-light th {
        background: #f5f5f5 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    /* Payment badges - plain text */
    .badge {
        background: none !important;
        border: none !important;
        border-radius: 0 !important;
        color: #000 !important;
        padding: 0 !important;
        font-weight: bold !important;
    }
    
    /* All text black */
    .text-primary,
    .text-muted,
    .text-danger,
    .text-success,
    .text-md-end,
    h1, h2, h3, h4, h5, h6,
    p, span, strong, td, th, small {
        color: #000 !important;
    }
    
    /* Links as plain text */
    a {
        color: #000 !important;
        text-decoration: none !important;
    }
    
    /* Page settings */
    @page {
        size: A4 portrait;
        margin: 5mm 10mm;
    }
    
    /* Avoid page breaks */
    .invoice-table,
    .card-body {
        page-break-inside: avoid;
    }

    .total-amount-section {
        margin-top: 16px !important;
    }
}
</style>
@endpush

<!-- EFT Proof Submission Modal -->
@if($invoice->status !== 'paid')
<div class="modal fade" id="eftProofModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('my-billing.submit-eft-proof', $invoice->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-upload me-2"></i>{{ __('Submit EFT Payment Proof') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <strong>{{ __('Invoice') }}:</strong> {{ $invoice->invoice_number }}<br>
                        <strong>{{ __('Amount Due') }}:</strong> R{{ number_format($invoice->total_amount, 2) }}
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Bank Reference / Transaction ID') }} <span class="text-danger">*</span></label>
                        <input type="text" name="bank_reference" class="form-control" required
                            placeholder="{{ __('Enter your bank reference number') }}">
                        <small class="text-muted">{{ __('This is the reference shown on your bank statement or payment confirmation') }}</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Payment Date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" required
                            value="{{ now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Amount Paid') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">R</span>
                            <input type="number" name="amount" class="form-control" required step="0.01"
                                value="{{ $invoice->total_amount }}" min="0.01">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Proof of Payment') }} <span class="text-danger">*</span></label>
                        <input type="file" name="attachment" class="form-control" required
                            accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted">{{ __('Upload a screenshot or PDF of your payment confirmation. Max 5MB. Allowed: JPG, PNG, PDF') }}</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Additional Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2"
                            placeholder="{{ __('Any additional information about your payment') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-rc-primary">
                        <i class="ti ti-upload me-1"></i>{{ __('Submit Proof') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
