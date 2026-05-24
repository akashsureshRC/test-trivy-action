@extends('layouts.main')

@section('page-title')
{{ __('My Billing') }}
@endsection

@section('page-breadcrumb')
{{ __('My Billing') }}
@endsection

@push('css')
<style>
    .billing-dashboard .card {
        margin-bottom: 0;
    }

    .billing-status-card {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .billing-status-header {
        padding: 20px;
        color: white;
    }

    .billing-status-header.trial {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .billing-status-header.active {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .billing-status-header.overdue {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .tier-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    /* Celebration Modal Styles */
    .celebration-modal .modal-content {
        border: none;
        border-radius: 20px;
        overflow: hidden;
    }

    .celebration-modal .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 30px;
        text-align: center;
    }

    .celebration-modal .celebration-icon {
        font-size: 80px;
        margin-bottom: 20px;
        animation: bounce 1s infinite;
    }

    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }

    .celebration-modal .modal-body {
        padding: 40px;
        text-align: center;
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

    .progress-ring {
        position: relative;
        display: inline-block;
    }

    .progress-ring .progress-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-weight: bold;
        font-size: 14px;
    }

    .usage-table td,
    .usage-table th {
        vertical-align: middle;
    }
</style>
@endpush

@section('content')
<div class="row billing-dashboard">
    <!-- Billing Status Card -->
    <div class="col-12 mb-4">
        @php
        $statusClass = 'active';
        if ($billingStatus['is_in_trial']) {
        $statusClass = 'trial';
        } elseif ($billingStatus['has_overdue_invoices']) {
        $statusClass = 'overdue';
        }
        @endphp
        <div class="card billing-status-card">
            <div class="billing-status-header {{ $statusClass }}">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="text-white mb-2">
                            @if($billingStatus['is_in_trial'])
                            <i class="ti ti-gift me-2"></i>{{ __('Trial Period Active') }}
                            @elseif($billingStatus['has_overdue_invoices'])
                            <i class="ti ti-alert-triangle me-2"></i>{{ __('Payment Required') }}
                            @else
                            <i class="ti ti-check me-2"></i>{{ __('Account Active') }}
                            @endif
                        </h4>
                        <p class="mb-0" style="color: #fff !important;">
                            @if($billingStatus['is_in_trial'])
                            {{ __('You have') }} <strong>{{ $billingStatus['trial']['trial_days_remaining'] }} {{ __('days') }}</strong>
                            {{ __('and') }} <strong>{{ $billingStatus['trial']['trial_payslips_remaining'] }} {{ __('payslips') }}</strong>
                            {{ __('remaining in your trial') }}
                            @elseif($billingStatus['has_overdue_invoices'])
                            {{ __('You have overdue invoices. Please make a payment to continue generating payslips.') }}
                            @else
                            {{ __('Your account is in good standing.') }}
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        @if($billingStatus['is_in_trial'])
                        <div class="d-inline-block text-center">
                            <div class="text-white fw-bold fs-2">
                                {{ $billingStatus['trial']['trial_payslips_used'] }}/{{ $billingStatus['trial']['trial_payslips_limit'] }}
                            </div>
                            <div class="text-white-70 small">{{ __('Trial Payslips Used') }}</div>
                        </div>
                        @elseif($billingStatus['current_cycle']['has_active_cycle'])
                        <div class="d-inline-block text-center">
                            <div class="text-white fw-bold fs-2">
                                {{ $billingStatus['current_cycle']['total_payslips'] }}
                            </div>
                            <div class="text-white-70 small">{{ __('Payslips This Cycle') }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @if($billingStatus['current_cycle']['has_active_cycle'])
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">{{ __('Current Billing Cycle') }}</h6>
                        <p class="mb-1"><i class="ti ti-calendar me-2"></i>{{ $billingStatus['current_cycle']['period_label'] }}</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h6 class="text-muted mb-3">{{ __('Current Amount Due') }}</h6>
                        <h3 class="text-primary mb-0">R{{ number_format($billingStatus['current_cycle']['total_amount'], 2) }}</h3>
                        @if($billingStatus['current_cycle']['tax_amount'] > 0)
                        <small class="text-muted">({{ __('incl. VAT') }} R{{ number_format($billingStatus['current_cycle']['tax_amount'], 2) }})</small>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="ti ti-file-invoice"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $billingStatus['total_payslips_generated'] }}</h3>
                    <p class="text-muted mb-0">{{ __('Total Payslips Generated') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="ti ti-currency-dollar"></i>
                </div>
                <div>
                    <h3 class="mb-0">R{{ number_format($billingStatus['current_cycle']['subtotal'] ?? 0, 2) }}</h3>
                    <p class="text-muted mb-0">{{ __('Current Cycle Charges') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                    <i class="ti ti-receipt-2"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $billingStatus['current_cycle']['total_payslips'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">{{ __('Payslips This Cycle') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="ti ti-receipt"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $invoices->count() }}</h3>
                    <p class="text-muted mb-0">{{ __('Recent Invoices') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pricing Tiers -->
    <div class="col-lg-6 mb-4">
        <x-rc-table title="{{ __('Pricing Tiers') }}" class="h-100">
            <x-slot name="headerActions">
                <a href="{{ route('my-billing.pricing') }}" class="btn btn-rc-outline btn-sm">
                    <i class="ti ti-calculator me-1"></i>{{ __('Price Calculator') }}
                </a>
            </x-slot>
            <x-slot name="subtitle">
                <p class="text-muted small mb-3">
                    {{ __('Pricing is cumulative - pay less per payslip as your volume increases.') }}
                </p>
            </x-slot>
            <x-rc-table.content>
                <table class="rc-table">
                    <thead>
                        <tr>
                            <th>{{ __('Tier') }}</th>
                            <th>{{ __('Payslip Range') }}</th>
                            <th class="col-amount">{{ __('Rate') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tiers as $index => $tier)
                        <tr>
                            <td>
                                <span class="tier-badge tier-{{ $index + 1 }}">{{ $tier->name }}</span>
                            </td>
                            <td>
                                @if($tier->max_payslips)
                                {{ $tier->min_payslips }} - {{ $tier->max_payslips }}
                                @else
                                {{ $tier->min_payslips }}+
                                @endif
                            </td>
                            <td class="col-amount fw-bold">R{{ number_format($tier->price_per_payslip, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-rc-table.content>
        </x-rc-table>
    </div>

    <!-- Current Cycle Breakdown -->
    <div class="col-lg-6 mb-4">
        <x-rc-table title="{{ __('Current Cycle Breakdown') }}" class="h-100">
            <x-rc-table.content>
                @if(!empty($billingStatus['current_cycle']['breakdown']))
                <table class="rc-table">
                    <thead>
                        <tr>
                            <th>{{ __('Tier') }}</th>
                            <th class="text-center">{{ __('Qty') }}</th>
                            <th class="col-amount">{{ __('Unit') }}</th>
                            <th class="col-amount">{{ __('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($billingStatus['current_cycle']['breakdown'] as $item)
                        <tr>
                            <td>{{ $item['tier_name'] }}</td>
                            <td class="text-center">{{ $item['quantity'] }}</td>
                            <td class="col-amount">R{{ number_format($item['unit_price'], 2) }}</td>
                            <td class="col-amount fw-bold">R{{ number_format($item['amount'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end py-0"><strong>{{ __('Subtotal') }}</strong></td>
                            <td class="col-amount pt-0"><strong>R{{ number_format($billingStatus['current_cycle']['subtotal'], 2) }}</strong></td>
                        </tr>
                        @if($billingStatus['current_cycle']['tax_amount'] > 0)
                        <tr>
                            <td colspan="3" class="text-end py-0">{{ __('VAT') }} (15%)</td>
                            <td class="col-amount pt-0">R{{ number_format($billingStatus['current_cycle']['tax_amount'], 2) }}</td>
                        </tr>
                        <tr class="rc-table-total">
                            <td colspan="3" class="text-end pt-0"><strong>{{ __('Total') }}</strong></td>
                            <td class="col-amount pt-0"><strong>R{{ number_format($billingStatus['current_cycle']['total_amount'], 2) }}</strong></td>
                        </tr>
                        @endif
                    </tfoot>
                </table>
                @else
                <x-rc-table.empty :asRow="false" icon="ti ti-chart-pie" title="{{ __('No Usage Yet') }}" message="{{ __('No usage recorded in this billing cycle yet.') }}" />
                @endif
            </x-rc-table.content>
        </x-rc-table>
    </div>

    <!-- Recent Usage -->
    <div class="col-12 mb-4">
        <x-rc-table title="{{ __('Recent Usage') }}">
            <x-slot name="headerActions">
                <a href="{{ route('my-billing.usage') }}" class="btn btn-rc-outline btn-sm">
                    {{ __('View All') }}
                </a>
            </x-slot>
            <x-rc-table.content>
                <table class="rc-table">
                    <thead>
                        <tr>
                            <th class="col-date">{{ __('Date') }}</th>
                            <th>{{ __('Workspace') }}</th>
                            <th>{{ __('Month') }}</th>
                            <th>{{ __('Tier') }}</th>
                            <th class="col-amount">{{ __('Amount') }}</th>
                            <th class="col-status">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentUsage as $usage)
                        <tr>
                            <td class="col-date">{{ formatDate($usage['created_at']) }}</td>
                            <td>{{ $usage['workspace_name'] }}</td>
                            <td>{{ $usage['salary_month'] }}</td>
                            <td>
                                <span class="tier-badge tier-{{ $loop->iteration <= 5 ? $loop->iteration : 5 }}">
                                    {{ $usage['tier_name'] }}
                                </span>
                            </td>
                            <td class="col-amount">R{{ number_format($usage['amount_charged'], 2) }}</td>
                            <td class="col-status">
                                @if($usage['status'] == 'pending')
                                <span class="rc-status rc-status-warning">{{ __('Pending') }}</span>
                                @elseif($usage['status'] == 'invoiced')
                                <span class="rc-status rc-status-info">{{ __('Invoiced') }}</span>
                                @else
                                <span class="rc-status rc-status-success">{{ __('Paid') }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <x-rc-table.empty :asRow="true" :colspan="6" icon="ti ti-file-invoice" title="{{ __('No Usage History') }}" message="{{ __('No usage history yet. Generate your first payslip to see usage tracking.') }}" />
                        @endforelse
                    </tbody>
                </table>
            </x-rc-table.content>
        </x-rc-table>
    </div>

    <!-- Recent Invoices -->
    @if($invoices->count() > 0)
    <div class="col-12">
        <x-rc-table title="{{ __('Recent Invoices') }}">
            <x-slot name="headerActions">
                <a href="{{ route('my-billing.invoices') }}" class="btn btn-rc-outline btn-sm">
                    {{ __('View All') }}
                </a>
            </x-slot>
            <x-rc-table.content>
                <table class="rc-table">
                    <thead>
                        <tr>
                            <th>{{ __('Invoice #') }}</th>
                            <th class="col-date">{{ __('Date') }}</th>
                            <th class="col-date">{{ __('Due Date') }}</th>
                            <th>{{ __('Period') }}</th>
                            <th class="col-amount">{{ __('Amount') }}</th>
                            <th class="col-status">{{ __('Status') }}</th>
                            <th class="col-actions">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                        @php
                            $isPastDue = $invoice->due_date && $invoice->due_date->isPast() && $invoice->status !== 'paid';
                        @endphp
                        <tr>
                            <td><strong>{{ $invoice->invoice_number }}</strong></td>
                            <td class="col-date">{{ formatDate($invoice->created_at) }}</td>
                            <td class="col-date">
                                @if($invoice->due_date)
                                <span class="{{ $isPastDue ? 'text-danger fw-bold' : '' }}">{{ formatDate($invoice->due_date) }}</span>
                                @else
                                -
                                @endif
                            </td>
                            <td>{{ $invoice->billingCycle?->period_label ?? '-' }}</td>
                            <td class="col-amount">R{{ number_format($invoice->total_amount, 2) }}</td>
                            <td class="col-status">
                                @if($isPastDue)
                                <span class="rc-status rc-status-danger">{{ __('Past Due') }}</span>
                                @elseif($invoice->status == 'pending')
                                <span class="rc-status rc-status-warning">{{ __('Pending') }}</span>
                                @elseif($invoice->status == 'paid')
                                <span class="rc-status rc-status-success">{{ __('Paid') }}</span>
                                @elseif($invoice->status == 'overdue')
                                <span class="rc-status rc-status-danger">{{ __('Overdue') }}</span>
                                @else
                                <span class="rc-status rc-status-secondary">{{ $invoice->status }}</span>
                                @endif
                            </td>
                            <td class="col-actions">
                                <a href="{{ route('my-billing.invoices.show', $invoice->id) }}" class="rc-table-action rc-table-action-view" title="{{ __('View') }}">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-rc-table.content>
        </x-rc-table>
    </div>
    @endif
</div>

<!-- Celebration Modal -->
@if(session('upgrade_celebration'))
<div class="modal fade celebration-modal" id="celebrationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="w-100 text-white">
                    <div class="celebration-icon">🎉</div>
                    <h2 class="mb-0 text-white">{{ __('Congratulations!') }}</h2>
                </div>
            </div>
            <div class="modal-body">
                <h4 class="mb-3">{{ __('Welcome to RC ClearPay Pro!') }}</h4>
                <p class="text-muted mb-4">
                    {{ __('Your account has been successfully upgraded to a paid plan. You now have unlimited access to all features!') }}
                </p>
                <div class="alert alert-info mb-4">
                    <i class="ti ti-info-circle me-2"></i>
                    {{ __('You will be invoiced at the end of each month based on your payslip usage.') }}
                </div>
                <div class="d-grid gap-2">
                    <a href="{{ route('payrun.index') }}" class="btn btn-rc-primary btn-lg">
                        <i class="ti ti-rocket me-2"></i>{{ __('Start Creating Payslips') }}
                    </a>
                    <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">
                        {{ __('Continue to Dashboard') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
@if(session('upgrade_celebration'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show celebration modal
    var celebrationModal = new bootstrap.Modal(document.getElementById('celebrationModal'));
    celebrationModal.show();
    
    // Confetti animation
    var duration = 5 * 1000;
    var animationEnd = Date.now() + duration;
    var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 9999 };

    function randomInRange(min, max) {
        return Math.random() * (max - min) + min;
    }

    var interval = setInterval(function() {
        var timeLeft = animationEnd - Date.now();

        if (timeLeft <= 0) {
            return clearInterval(interval);
        }

        var particleCount = 50 * (timeLeft / duration);
        
        // Fire confetti from different positions
        confetti(Object.assign({}, defaults, { 
            particleCount, 
            origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } 
        }));
        confetti(Object.assign({}, defaults, { 
            particleCount, 
            origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } 
        }));
    }, 250);

    // Extra burst when modal is shown
    setTimeout(function() {
        confetti({
            particleCount: 100,
            spread: 70,
            origin: { y: 0.6 }
        });
    }, 200);
});
</script>
@endif
@endpush
@endsection