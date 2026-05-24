@extends('layouts.main')

@section('page-title')
{{ __('Invoice') }} {{ $invoice->invoice_number }}
@endsection

@section('page-breadcrumb')
{{ __('Billing') }},{{ __('Invoices') }},{{ $invoice->invoice_number }}
@endsection

@push('css')
<style>
    .invoice-header {
        background: #2d47a8;
        color: white;
        border-radius: 12px 12px 0 0;
        padding: 30px;
    }

    .invoice-header h2,
    .invoice-header h4 {
        color: #fff;
    }

    .invoice-status {
        padding: 8px 20px;
        border-radius: 25px;
        font-weight: 600;
        display: inline-block;
    }

    .invoice-status.pending {
        background: #fef3c7;
        color: #d97706;
    }

    .invoice-status.sent {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .invoice-status.paid {
        background: #dcfce7;
        color: #15803d;
    }

    .invoice-status.overdue {
        background: #fecaca;
        color: #dc2626;
    }

    .invoice-status.cancelled {
        background: #e5e7eb;
        color: #6b7280;
    }

    .invoice-status.draft {
        background: #f3f4f6;
        color: #6b7280;
    }

    .invoice-detail-card {
        border: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border-radius: 12px;
        overflow: hidden;
    }

    .invoice-table th {
        background: #f8f9fa;
        font-weight: 600;
    }

    .invoice-table td,
    .invoice-table th {
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

    .tier-badge.tier-1 {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .tier-badge.tier-2 {
        background: #dcfce7;
        color: #15803d;
    }

    .tier-badge.tier-3 {
        background: #fef3c7;
        color: #d97706;
    }

    .tier-badge.tier-4 {
        background: #fce7f3;
        color: #be185d;
    }

    .tier-badge.tier-5 {
        background: #ede9fe;
        color: #7c3aed;
    }

    .customer-info-card {
        background: #f8f9fc;
        border-radius: 10px;
        padding: 20px;
    }

    .payment-row {
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .payment-row:last-child {
        border-bottom: none;
    }

    .admin-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 15px;
        font-size: 11px;
        font-weight: 600;
    }

    .admin-badge.trial {
        background: #fef3c7;
        color: #d97706;
    }

    .admin-badge.active {
        background: #dcfce7;
        color: #15803d;
    }
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
                <!-- Company & Customer Info Row -->
                <div class="row mb-4">
                    <!-- Company Info (From) -->
                    <div class="col-md-6">
                        <div class="customer-info-card h-100">
                            <h6 class="text-muted mb-3">{{ __('From') }}</h6>
                            <h5 class="mb-2">{{ $company['name'] ?? 'RC ClearPay' }}</h5>
                            @if(!empty($company['address']))<p class="mb-1"><i class="ti ti-map-pin me-2 text-muted"></i>{{ $company['address'] }}</p>@endif
                            @if(!empty($company['phone']))<p class="mb-1"><i class="ti ti-phone me-2 text-muted"></i>{{ $company['phone'] }}</p>@endif
                            @if(!empty($company['email']))<p class="mb-1"><i class="ti ti-mail me-2 text-muted"></i>{{ $company['email'] }}</p>@endif
                            @if(!empty($company['vat_number']))<p class="mb-0"><i class="ti ti-receipt-tax me-2 text-muted"></i>VAT: {{ $company['vat_number'] }}</p>@endif
                        </div>
                    </div>

                    <!-- Customer Info (Bill To) -->
                    <div class="col-md-6">
                        <div class="customer-info-card h-100">
                            <h6 class="text-muted mb-3">{{ __('Bill To') }}</h6>
                            @if($invoice->user)
                            <h5 class="mb-2">{{ $invoice->user->name }}</h5>
                            <p class="mb-1"><i class="ti ti-mail me-2 text-muted"></i>{{ $invoice->user->email }}</p>
                            @if($invoice->user->phone)
                            <p class="mb-1"><i class="ti ti-phone me-2 text-muted"></i>{{ $invoice->user->phone }}</p>
                            @endif
                            @if($invoice->user->company_name)
                            <p class="mb-1"><i class="ti ti-building me-2 text-muted"></i>{{ $invoice->user->company_name }}</p>
                            @endif
                            <div class="mt-2 account-status">
                                @if($invoice->user->is_trial ?? false)
                                <span class="admin-badge trial">{{ __('Trial Account') }}</span>
                                @else
                                <span class="admin-badge active">{{ __('Active Account') }}</span>
                                @endif
                            </div>
                            @else
                            <p class="text-muted mb-0">{{ __('User not found') }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Invoice Dates Row -->
                <div class="row mb-4">
                    <div class="col-md-12 dates-info">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <small class="text-muted d-block">{{ __('Issue Date') }}</small>
                                <strong>{{ formatDate($invoice->created_at) }}</strong>
                            </div>
                            <div class="col-md-3 mb-3">
                                <small class="text-muted d-block">{{ __('Due Date') }}</small>
                                @if($invoice->due_date)
                                <strong class="{{ $invoice->isOverdue() ? 'text-danger' : '' }}">
                                    {{ formatDate($invoice->due_date) }}
                                </strong>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </div>
                            <div class="col-md-3 mb-3">
                                <small class="text-muted d-block">{{ __('Billing Period') }}</small>
                                @if($invoice->billingCycle)
                                <strong>{{ $invoice->billingCycle->period_start ? formatDate($invoice->billingCycle->period_start) : 'N/A' }}</strong>
                                <span class="text-muted">to</span>
                                <strong>{{ $invoice->billingCycle->period_end ? formatDate($invoice->billingCycle->period_end) : 'N/A' }}</strong>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </div>
                            <div class="col-md-3 mb-3">
                                <small class="text-muted d-block">{{ __('Payslips Processed') }}</small>
                                <strong>{{ number_format($invoice->total_payslips ?? 0) }}</strong>
                            </div>
                            @if($invoice->paid_at)
                            <div class="col-6 mb-3">
                                <small class="text-muted d-block">{{ __('Paid On') }}</small>
                                <strong>{{ formatDateTime($invoice->paid_at) }}</strong>
                            </div>
                            @endif
                            <div class="col-12">
                                <small class="text-muted d-block">{{ __('Total Amount') }}</small>
                                <h2 class="text-primary mb-0">R{{ number_format($invoice->total_amount, 2) }}</h2>
                                @if($invoice->tax_amount > 0)
                                <small class="text-muted">({{ __('incl. VAT') }} R{{ number_format($invoice->tax_amount, 2) }})</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Items -->
                <x-rc-table title="{{ __('Invoice Items') }}">
                    <x-rc-table.content>
                        <table class="rc-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Description') }}</th>
                                    <th class="text-center">{{ __('Quantity') }}</th>
                                    <th class="col-amount">{{ __('Unit Price') }}</th>
                                    <th class="col-amount">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->items as $item)
                                <tr>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-center">{{ number_format($item->quantity) }}</td>
                                    <td class="col-amount">R{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="col-amount fw-bold">R{{ number_format($item->amount, 2) }}</td>
                                </tr>
                                @empty
                                <x-rc-table.empty :asRow="true" :colspan="4" icon="ti ti-file-off" title="{{ __('No items') }}" message="" />
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end py-0"><strong>{{ __('Subtotal') }}</strong></td>
                                    <td class="col-amount py-0"><strong>R{{ number_format($invoice->subtotal, 2) }}</strong></td>
                                </tr>
                                @if($invoice->tax_amount > 0)
                                <tr>
                                    <td colspan="3" class="text-end py-0">{{ __('VAT') }} ({{ $invoice->tax_percentage ?? 15 }}%)</td>
                                    <td class="col-amount py-0">R{{ number_format($invoice->tax_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="table-primary">
                                    <td colspan="3" class="text-end pt-0"><strong>{{ __('Total') }}</strong></td>
                                    <td class="col-amount pt-0"><strong class="fs-5">R{{ number_format($invoice->total_amount, 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </x-rc-table.content>
                </x-rc-table>

                <!-- Payment Details -->
                <x-rc-table title="{{ __('Payment Details') }}" class="mt-4">
                    <x-slot name="headerActions">
                        @if($invoice->status !== 'paid')
                        <button type="button" class="btn btn-rc-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manualPaymentModal">
                            <i class="ti ti-plus me-1"></i>{{ __('Record Payment') }}
                        </button>
                        @endif
                    </x-slot>
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
                                @forelse($invoice->payments as $payment)
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
                                @empty
                                <x-rc-table.empty :asRow="true" :colspan="5" icon="ti ti-receipt-off" title="{{ __('No payments recorded yet') }}" message="" />
                                @endforelse
                            </tbody>
                        </table>
                    </x-rc-table.content>
                </x-rc-table>

                <!-- Actions -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <a href="{{ route('billing.invoices.index') }}" class="btn btn-rc-outline">
                            <i class="ti ti-arrow-left me-1"></i>{{ __('Back to Invoices') }}
                        </a>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <a href="{{ route('billing.invoices.download', $invoice->id) }}" class="btn btn-rc-success me-2">
                            <i class="ti ti-download me-1"></i>{{ __('Download PDF') }}
                        </a>
                        <button type="button" class="btn btn-rc-outline" onclick="window.print()">
                            <i class="ti ti-printer me-1"></i>{{ __('Print') }}
                        </button>
                        @if($invoice->status !== 'paid')
                        <form action="{{ route('billing.invoices.send-reminder', $invoice->id) }}" method="POST" class="ms-2 d-inline">
                            @csrf
                            <button type="submit" class="btn btn-rc-primary">
                                <i class="ti ti-mail me-1"></i>{{ __('Send Reminder') }}
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                <!-- EFT Proof Submissions Section -->
                @if(isset($eftSubmissions) && $eftSubmissions->count() > 0)
                <hr>
                <x-rc-table title="{{ __('EFT Payment Proof Submissions') }}">
                    <x-slot name="headerActions">
                        <span class="badge bg-info">{{ $eftSubmissions->where('status', 'pending')->count() }} {{ __('Pending') }}</span>
                    </x-slot>
                    <x-rc-table.content>
                        <table class="rc-table">
                            <thead>
                                <tr>
                                    <th class="col-date">{{ __('Date') }}</th>
                                    <th>{{ __('Reference') }}</th>
                                    <th class="col-amount">{{ __('Amount') }}</th>
                                    <th>{{ __('Proof') }}</th>
                                    <th class="col-status">{{ __('Status') }}</th>
                                    <th class="col-actions">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($eftSubmissions as $submission)
                                <tr>
                                    <td class="col-date">{{ formatDate($submission->payment_date) }}</td>
                                    <td><code>{{ $submission->bank_reference }}</code></td>
                                    <td class="col-amount">R{{ number_format($submission->amount, 2) }}</td>
                                    <td>
                                        @if($submission->attachment)
                                        <div class="d-flex align-items-center">
                                            <i class="ti ti-file me-1"></i>
                                            <a href="{{ $submission->attachment_url }}" target="_blank" class="btn btn-link btn-sm p-0">
                                                {{ __('View') }}
                                            </a>
                                        </div>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="col-status">
                                        <span class="rc-status {{ str_replace('bg-', 'rc-status-', $submission->status_badge_class) }}">
                                            {{ ucfirst($submission->status) }}
                                        </span>
                                    </td>
                                    <td class="col-actions">
                                        @if($submission->status === 'pending')
                                        <button type="button" class="rc-table-action rc-table-action-success"
                                            data-bs-toggle="modal"
                                            data-bs-target="#reviewEftModal{{ $submission->id }}" data-title="{{ __('Approve') }}" data-bs-toggle="tooltip"
                                            data-bs-original-title="{{ __('Approve') }}">
                                            <i class="ti ti-check"></i>
                                        </button>
                                        <button type="button" class="rc-table-action rc-table-action-delete"
                                            data-bs-toggle="modal"
                                            data-bs-target="#rejectEftModal{{ $submission->id }}">
                                            <i class="ti ti-x"></i>
                                        </button>
                                        @elseif($submission->status === 'rejected')
                                        <small class="text-muted">{{ Str::limit($submission->rejection_reason, 30) }}</small>
                                        @elseif($submission->status === 'approved')
                                        <small class="text-success"><i class="ti ti-check me-1"></i>{{ __('Approved') }}</small>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-rc-table.content>
                </x-rc-table>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- EFT Review Modals -->
@if(isset($eftSubmissions))
@foreach($eftSubmissions->where('status', 'pending') as $submission)
<!-- Approve Modal -->
<div class="modal fade" id="reviewEftModal{{ $submission->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('billing.invoices.review-eft', $submission->id) }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="approve">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-check me-2"></i>{{ __('Approve EFT Payment') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success">
                        <p class="mb-2"><strong>{{ __('You are about to approve this payment:') }}</strong></p>
                        <ul class="mb-0">
                            <li><strong>{{ __('Reference') }}:</strong> {{ $submission->bank_reference }}</li>
                            <li><strong>{{ __('Amount') }}:</strong> R{{ number_format($submission->amount, 2) }}</li>
                            <li><strong>{{ __('Payment Date') }}:</strong> {{ formatDate($submission->payment_date) }}</li>
                        </ul>
                    </div>
                    @if($submission->attachment)
                    <div class="mb-3">
                        <a href="{{ $submission->attachment_url }}" target="_blank" class="btn btn-rc-outline btn-sm">
                            <i class="ti ti-external-link me-1"></i>{{ __('View Proof of Payment') }}
                        </a>
                    </div>
                    @endif
                    @if($submission->notes)
                    <div class="mb-3">
                        <label class="form-label text-muted">{{ __('Customer Notes') }}</label>
                        <p class="bg-light p-2 rounded">{{ $submission->notes }}</p>
                    </div>
                    @endif
                    <p class="text-muted small mb-0">{{ __('This will mark the invoice as paid and notify the customer.') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-rc-primary">
                        <i class="ti ti-check me-1"></i>{{ __('Approve Payment') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectEftModal{{ $submission->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('billing.invoices.review-eft', $submission->id) }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="reject">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-x me-2"></i>{{ __('Reject EFT Submission') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <p class="mb-2"><strong>{{ __('Submission Details') }}:</strong></p>
                        <ul class="mb-0">
                            <li><strong>{{ __('Reference') }}:</strong> {{ $submission->bank_reference }}</li>
                            <li><strong>{{ __('Amount') }}:</strong> R{{ number_format($submission->amount, 2) }}</li>
                            <li><strong>{{ __('Payment Date') }}:</strong> {{ formatDate($submission->payment_date) }}</li>
                        </ul>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Rejection Reason') }} <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required
                            placeholder="{{ __('Please explain why this submission is being rejected...') }}"></textarea>
                        <small class="text-muted">{{ __('This will be shown to the customer.') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="ti ti-x me-1"></i>{{ __('Reject Submission') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endif

<!-- Manual Payment Modal -->
@if($invoice->status !== 'paid')
<div class="modal fade" id="manualPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('billing.invoices.manual-payment', $invoice->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Record Manual Payment') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <strong>{{ __('Invoice') }}:</strong> {{ $invoice->invoice_number }}<br>
                        <strong>{{ __('Customer') }}:</strong> {{ $invoice->user->name ?? 'N/A' }}<br>
                        <strong>{{ __('Amount Due') }}:</strong> R{{ number_format($invoice->total_amount, 2) }}
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Payment Amount') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">R</span>
                            <input type="number" name="amount" class="form-control" required step="0.01"
                                value="{{ $invoice->total_amount }}" min="0.01">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Payment Method') }} <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="eft">{{ __('EFT / Bank Transfer') }}</option>
                            <option value="cash">{{ __('Cash') }}</option>
                            <option value="cheque">{{ __('Cheque') }}</option>
                            <option value="card">{{ __('Card Payment') }}</option>
                            <option value="payfast">{{ __('PayFast') }}</option>
                            <option value="other">{{ __('Other') }}</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Payment Reference') }} <span class="text-danger">*</span></label>
                        <input type="text" name="payment_reference" class="form-control" required
                            placeholder="{{ __('Bank reference or transaction ID') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Payment Date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" required
                            value="{{ now()->format('Y-m-d') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2"
                            placeholder="{{ __('Optional notes about this payment') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-rc-primary">
                        <i class="ti ti-check me-1"></i>{{ __('Record Payment') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
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
        .modal,
        .row.mt-4,
        .print-hide,
        .ti-receipt,
        .ti-list,
        .ti-building,
        .ti-mail,
        .ti-phone,
        .ti-calendar,
        .ti-calendar-event,
        .ti-clock {
            display: none !important;
            visibility: hidden !important;
        }

        /* Reset body and html */
        html,
        body {
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
        .col-lg-11 {
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
            display: none !important;
        }

        /* Customer & Invoice info row */
        .row.mb-4 {
            margin: 0 0 20px 0 !important;
            padding: 0 !important;
        }

        .row.mb-4>.col-md-6 {
            padding: 0 10px 0 0 !important;
        }

        .row.mb-4>.col-md-6:last-child {
            padding: 0 !important;
        }

        /* Customer info card - light gray */
        .customer-info-card {
            background: #f8f8f8 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            border: 1px solid #ccc !important;
            border-radius: 0 !important;
            padding: 15px !important;
            margin-bottom: 0 !important;
        }

        .customer-info-card h5 {
            margin-bottom: 10px !important;
        }

        .customer-info-card h6 {
            margin-bottom: 10px !important;
        }

        .customer-info-card p {
            margin-bottom: 5px !important;
        }

        /* Invoice details grid */
        .col-6.mb-3 {
            margin-bottom: 10px !important;
            padding: 0 !important;
        }

        .col-6.mb-3 small {
            margin-bottom: 3px !important;
        }

        .col-12 h2 {
            margin: 0 !important;
        }

        /* Admin badges - plain text */
        .admin-badge {
            background: none !important;
            border: none !important;
            border-radius: 0 !important;
            color: #000 !important;
            padding: 0 !important;
            font-weight: bold !important;
        }

        /* HR spacing */
        hr {
            margin: 20px 0 !important;
            border: none !important;
            border-top: 1px solid #ccc !important;
        }

        /* Section headings */
        h6.mb-3,
        h6.mb-0 {
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

        /* No payments message */
        .bg-light.rounded {
            background: #f8f8f8 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            border-radius: 0 !important;
            padding: 20px !important;
        }

        /* All text black */
        .text-primary,
        .text-muted,
        .text-danger,
        .text-success,
        .text-md-end,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        p,
        span,
        strong,
        td,
        th,
        small {
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
            margin: 15mm;
        }

        /* Avoid page breaks */
        .invoice-table,
        .card-body {
            page-break-inside: avoid;
        }

        .dates-info {
            margin-top: 20px;
        }

        .account-status {
            display: none;
        }
    }
</style>
@endpush