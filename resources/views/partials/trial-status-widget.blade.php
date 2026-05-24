{{-- Trial Status Dashboard Widget --}}
{{-- Include this in your dashboard view with: @include('partials.trial-status-widget') --}}

@php
$user = Auth::user();
$isCompanyUser = $user->type === 'company';

if ($isCompanyUser) {
$billingService = app(\App\Services\BillingService::class);
$billingStatus = $billingService->getBillingStatus($user);
$isOnTrial = $billingStatus['is_in_trial'] ?? false;
$trialExpired = !$isOnTrial && ($user->trial_ends_at || $user->trial_payslips_used > 0);
$hasOverdue = $billingStatus['has_overdue_invoices'] ?? false;
$billingActive = $billingStatus['user_billing_status'] === 'active' && !$hasOverdue;
} else {
$isOnTrial = false;
$trialExpired = false;
$hasOverdue = false;
$billingActive = false;
}
@endphp

@if($isCompanyUser && ($isOnTrial || ($trialExpired && !$billingActive) || $hasOverdue))
<div class="col-12 mb-4">
    @if($isOnTrial)
    {{-- Trial Active Widget --}}
    <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-white text-primary me-2">
                            <i class="ti ti-gift me-1"></i>FREE TRIAL
                        </span>
                    </div>
                    <h4 class="text-white mb-2">{{ __('Welcome to RC ClearPay!') }}</h4>
                    <p class="text-white-50 mb-0">
                        {{ __('You\'re currently on a free trial. Explore all features and see how RC ClearPay can simplify your payroll.') }}
                    </p>
                </div>
                <div class="col-md-4">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="bg-white bg-opacity-20 rounded p-3">
                                <h2 class="mb-0">{{ $billingStatus['trial']['trial_days_remaining'] ?? 0 }}</h2>
                                <small>{{ __('Days Left') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-white bg-opacity-20 rounded p-3">
                                <h2 class="mb-0">{{ $billingStatus['trial']['trial_payslips_remaining'] ?? 0 }}</h2>
                                <small>{{ __('Payslips Left') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Progress Bar --}}
            @php
            $totalDays = $billingStatus['trial']['trial_days_total'] ?? 30;
            $daysUsed = $totalDays - ($billingStatus['trial']['trial_days_remaining'] ?? 0);
            $daysProgress = $totalDays > 0 ? ($daysUsed / $totalDays) * 100 : 0;

            $totalPayslips = $billingStatus['trial']['trial_payslips_limit'] ?? 50;
            $payslipsUsed = $billingStatus['trial']['trial_payslips_used'] ?? 0;
            $payslipsProgress = $totalPayslips > 0 ? ($payslipsUsed / $totalPayslips) * 100 : 0;
            @endphp

            <div class="mt-4">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="d-flex justify-content-between text-white-50 small mb-1">
                            <span>{{ __('Days Used') }}</span>
                            <span>{{ $daysUsed }}/{{ $totalDays }}</span>
                        </div>
                        <div class="progress" style="height: 6px; background: rgba(255,255,255,0.2);">
                            <div class="progress-bar bg-white" style="width: {{ $daysProgress }}%"></div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="d-flex justify-content-between text-white-50 small mb-1">
                            <span>{{ __('Payslips Used') }}</span>
                            <span>{{ $payslipsUsed }}/{{ $totalPayslips }}</span>
                        </div>
                        <div class="progress" style="height: 6px; background: rgba(255,255,255,0.2);">
                            <div class="progress-bar bg-white" style="width: {{ $payslipsProgress }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3 text-end">
                <a href="{{ route('my-billing.pricing') }}" class="btn btn-light btn-sm">
                    <i class="ti ti-calculator me-1"></i>{{ __('View Pricing') }}
                </a>
            </div>
        </div>
    </div>

    @elseif($trialExpired && !$billingActive)
    {{-- Trial Expired Widget --}}
    <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-white text-danger me-2">
                            <i class="ti ti-alert-circle me-1"></i>{{ __('TRIAL ENDED') }}
                        </span>
                    </div>
                    <h4 class="text-white mb-2">{{ __('Your Free Trial Has Ended') }}</h4>
                    <p class="text-white mb-0">
                        {{ __('Upgrade to a paid plan to continue processing payslips. No payment required now - you\'ll be invoiced at the end of the month based on your usage.') }}
                    </p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="bg-white bg-opacity-20 rounded p-3 mb-3">
                        <i class="ti ti-lock" style="font-size: 48px;"></i>
                        <p class="small mb-0 mt-2">{{ __('Payslip generation is paused') }}</p>
                    </div>
                    <div class="d-flex justify-content-center gap-2">
                        <form action="{{ route('my-billing.upgrade-trial') }}" method="POST" class="d-inline" data-confirm-message="Are you sure you want to upgrade to a paid plan? You will start being billed for payslips at the end of each month.">
                            @csrf
                            <button type="submit" class="btn btn-light btn-lg">
                                <i class="ti ti-rocket me-1"></i>{{ __('Upgrade to Paid Plan') }}
                            </button>
                        </form>
                        <a href="{{ route('my-billing.pricing') }}" class="btn btn-outline-light btn-lg">
                            <i class="ti ti-calculator me-1"></i>{{ __('View Pricing') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @elseif($hasOverdue)
    {{-- Overdue Payment Widget --}}
    <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-white text-danger me-2">
                            <i class="ti ti-alert-triangle me-1"></i>{{ __('PAYMENT REQUIRED') }}
                        </span>
                    </div>
                    <h4 class="text-white mb-2">{{ __('You Have Outstanding Invoices') }}</h4>
                    <p class="text-white-50 mb-0">
                        {{ __('Please pay your outstanding invoices to continue using all features without interruption.') }}
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('my-billing.invoices') }}" class="btn btn-light btn-lg">
                        <i class="ti ti-credit-card me-1"></i>{{ __('Pay Now') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endif