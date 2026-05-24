@extends('layouts.main')

@section('page-title')
    {{ __('Billing Configuration') }}
@endsection

@section('page-breadcrumb')
    {{ __('Billing Configuration') }}
@endsection

@push('css')
<style>
.billing-stat-card {
    border: 1px solid #e6e6e6;
    border-radius: 8px;
    padding: 20px;
    background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
    transition: all 0.3s ease;
}
.billing-stat-card:hover {
    box-shadow: 0 4px 15px rgba(57, 86, 202, 0.1);
}
.billing-stat-card .stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}
.billing-stat-card .stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--rc-primary);
}
.billing-stat-card .stat-label {
    color: #6c757d;
    font-size: 14px;
}
.tier-preview-table {
    font-size: 13px;
}
.tier-preview-table th {
    background: #f4f4f4;
}
</style>
@endpush

@section('content')
<div class="row">
    <!-- Stats Cards -->
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="billing-stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-info bg-opacity-10 text-primary me-3">
                    <i class="ti ti-stack"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $tiers->count() }}</div>
                    <div class="stat-label">{{ __('Pricing Tiers') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="billing-stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="ti ti-coin"></i>
                </div>
                <div>
                    <div class="stat-value">R{{ number_format($settings['base_rate'] ?? 0, 2) }}</div>
                    <div class="stat-label">{{ __('Default Rate/Payslip') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="billing-stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                    <i class="ti ti-clock"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $settings['trial_days'] ?? 14 }}</div>
                    <div class="stat-label">{{ __('Trial Days') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-12 mb-4">
        <div class="billing-stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="ti ti-receipt-2"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $settings['trial_payslips'] ?? 10 }}</div>
                    <div class="stat-label">{{ __('Trial Payslips') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Pricing Tiers Card -->
    <div class="col-lg-12 col-12">
        <x-rc-table title="{{ __('Pricing Tiers') }}" titleIcon="ti ti-layers">
            <x-slot name="headerActions">
                <a href="#!" class="btn btn-sm btn-rc-primary" 
                   data-url="{{ route('billing.tiers.create') }}" data-ajax-popup="true" data-size="lg" data-title="{{ __('Create Pricing Tier') }}">
                    <i class="ti ti-plus me-1"></i>{{ __('Add Tier') }}
                </a>
            </x-slot>
            <x-rc-table.content>
                <table class="rc-table tier-preview-table">
                    <thead>
                        <tr>
                            <th>{{ __('Tier Name') }}</th>
                            <th>{{ __('Range') }}</th>
                            <th class="col-amount">{{ __('Rate/Payslip') }}</th>
                            <th class="col-actions">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tiers as $tier)
                        <tr>
                            <td>
                                <span class="fw-semibold">{{ $tier->name }}</span>
                                @if($tier->description)
                                    <br><small class="text-muted">{{ Str::limit($tier->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <span>
                                    {{ $tier->min_payslips }} - {{ $tier->max_payslips ?? '∞' }}
                                </span>
                            </td>
                            <td class="col-amount">
                                <span class="fw-bold text-success">R{{ number_format($tier->price_per_payslip, 2) }}</span>
                            </td>
                            <td class="col-actions">
                                <a href="#!" 
                                   class="rc-table-action rc-table-action-edit" data-ajax-popup="true" 
                                   data-url="{{ route('billing.tiers.edit', $tier->id) }}" data-size="lg" data-title="{{ __('Edit Tier') }}">
                                    <i class="ti ti-pencil"></i>
                                </a>
                                <form action="{{ route('billing.tiers.destroy', $tier->id) }}" 
                                      method="POST" class="d-inline" 
                                      data-confirm-message="{{ __('Are you sure you want to delete this tier?') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rc-table-action rc-table-action-delete">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <x-rc-table.empty :asRow="true" :colspan="5" icon="ti ti-layers" title="{{ __('No Pricing Tiers') }}" message="{{ __('No pricing tiers configured yet.') }}" />
                        @endforelse
                    </tbody>
                </table>
            </x-rc-table.content>
        </x-rc-table>
    </div>
</div>
@endsection


