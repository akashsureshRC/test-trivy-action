@extends('layouts.main')
@section('page-title')
{{ __('Payment Runs') }}
@endsection
@section('page-breadcrumb')
{{ __('Payroll') }},
{{ __('Payment Runs') }}
@endsection
@php
$company_settings = getCompanyAllSetting();
@endphp

@section('content')
<div class="payrun-page row">
    {{-- Summary Stats --}}
    @php
    $totalPending = count($pending_payruns);
    $totalCompleted = count($all_payruns);
    $totalPayslips = 0;
    $totalAmount = 0;
    foreach($all_payruns as $payrun) {
    $totalPayslips += $payrun['payslips'];
    $totalAmount += floatval(str_replace(',', '', $payrun['total_netpay']));
    }
    @endphp

    <div class="col-sm-12">
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                            <i class="ti ti-clock-play"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $totalPending }}</h3>
                            <p class="text-muted mb-0">{{ __('Pending Runs') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="ti ti-check"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $totalCompleted }}</h3>
                            <p class="text-muted mb-0">{{ __('Completed Runs') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                            <i class="ti ti-file-invoice"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $totalPayslips }}</h3>
                            <p class="text-muted mb-0">{{ __('Total Payslips') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                            <i class="ti ti-cash"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">R {{ number_format($totalAmount, 2) }}</h3>
                            <p class="text-muted mb-0">{{ __('Total Paid') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending Pay Runs Section --}}
        <x-rc-table title="Pending Pay Runs">
            <x-rc-table.content>
                @if(count($pending_payruns) > 0)
                <div class="payrun-pending-list">
                    @foreach ($pending_payruns as $pending_payrun)
                    <div class="payrun-pending-item">
                        <div class="payrun-pending-header">
                            <div class="payrun-pending-info">
                                <h5 class="mb-1">
                                    Month ending {{ formatDate(Carbon\Carbon::parse($pending_payrun['term'])->endOfMonth()) }}
                                </h5>
                                <p class="text-muted mb-0">Payslips need to be finalised before creating the pay run.</p>
                            </div>
                            @if ($pending_payrun['finalized'] > 0)
                            <button type="button" class="btn btn-rc-primary btn-sm" data-bs-toggle="modal" data-bs-target="#payRunModal{{ $pending_payrun['term'] }}">
                                <i class="ti ti-plus me-1"></i> Create Pay Run
                            </button>
                            @endif
                        </div>

                        <div class="payrun-status-grid">
                            <div class="payrun-status-item">
                                <span class="payrun-status-label">Total</span>
                                <span class="payrun-status-value">{{ $pending_payrun['total'] }}</span>
                            </div>
                            <div class="payrun-status-item payrun-status-success">
                                <span class="payrun-status-label">Finalised</span>
                                <span class="payrun-status-value">{{ $pending_payrun['finalized'] }}</span>
                                @if ($pending_payrun['finalized'] > 0)
                                <div class="payrun-status-actions">
                                    <a class="rc-table-action rc-table-action-view" target="_blank" href="{{ route('payrun.finalized.pdf', $pending_payrun['term']) }}" data-bs-toggle="tooltip" data-bs-original-title="Preview PDF">
                                        <i class="ti ti-file-type-pdf"></i>
                                    </a>
                                    {!! Form::open(['route' => ['payrun.bulkUnFinalisation', $pending_payrun['term']], 'method' => 'POST', 'style' => 'display:inline;']) !!}
                                    <a href="#" class="btn btn-sm btn-outline-danger show_confirm" data-confirm-message="Are you sure you want to unfinalise this pay run? {{ $pending_payrun['finalized'] }} payslips will be unfinalised." data-bs-toggle="tooltip" data-bs-original-title="Unfinalise">
                                        <i class="ti ti-arrow-back-up me-1"></i> Unfinalise
                                    </a>
                                    {!! Form::close() !!}
                                </div>
                                @endif
                            </div>
                            <div class="payrun-status-item payrun-status-warning">
                                <span class="payrun-status-label">Pending</span>
                                <span class="payrun-status-value">{{ $pending_payrun['pending'] }}</span>
                                @if ($pending_payrun['pending'] > 0)
                                <div class="payrun-status-actions">
                                    <a class="rc-table-action rc-table-action-view" target="_blank" href="{{ route('payrun.pending.pdf', $pending_payrun['term']) }}" data-bs-toggle="tooltip" data-bs-original-title="Preview PDF">
                                        <i class="ti ti-file-type-pdf"></i>
                                    </a>
                                    <a class="rc-table-action rc-table-action-success" href="{{ route('payrun.bulkFinalisation', $pending_payrun['term']) }}" data-bs-toggle="tooltip" data-bs-original-title="Finalise All">
                                        <i class="ti ti-check"></i>
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Create Pay Run Modal --}}
                    <div class="modal fade" id="payRunModal{{ $pending_payrun['term'] }}" tabindex="-1" aria-labelledby="payRunModalLabel{{ $pending_payrun['term'] }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" style="max-width: 313px;">
                            <form method="POST" action="{{ route('payrun.store') }}" id="payrunForm{{ $pending_payrun['term'] }}">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="payRunModalLabel{{ $pending_payrun['term'] }}">
                                            Create Pay Run: {{ formatShortMonthYear(Carbon\Carbon::parse($pending_payrun['term'])->endOfMonth()) }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        @if ($pending_payrun['pending'] > 0)
                                        <div class="alert alert-warning d-flex align-items-start">
                                            <i class="ti ti-alert-triangle me-2 mt-1"></i>
                                            <div>
                                                <strong>Warning:</strong> {{ $pending_payrun['pending'] }} pending payslips will be excluded from this pay run.
                                                You'll need to create another pay run later after finalizing them.
                                            </div>
                                        </div>
                                        @endif

                                        <div class="mb-3">
                                            <label class="form-label required">{{ __('Payment Method') }}</label>
                                            <select class="form-select" id="payment_method_{{ $pending_payrun['term'] }}" name="payment_method" required>
                                                <option value="">Select Payment Method</option>
                                                <option value="EFT">EFT</option>
                                                <option value="Cheque">Cheque</option>
                                                <option value="Cash">Cash</option>
                                            </select>
                                            <span class="text-danger payment-method-error" id="payment_method_error_{{ $pending_payrun['term'] }}" style="display: none;">*Payment method is required</span>
                                        </div>
                                        <input type="hidden" value="{{ $pending_payrun['term'] }}" name="term">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-rc-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-rc-primary" onclick="validatePayrunForm('{{ $pending_payrun['term'] }}')">
                                            <i class="ti ti-check me-1"></i> Create Pay Run
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <x-rc-table.empty icon="ti ti-check-all" title="No Pending Pay Runs" message="All pay runs have been processed." />
                @endif
            </x-rc-table.content>
        </x-rc-table>

        {{-- Completed Payment Runs Section --}}
        <div class="mt-4">
            <x-rc-table title="Payment History">
                <x-rc-table.content>
                    @if($payruns->total() > 0)
                    <table class="rc-table">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th class="text-center">Payslips</th>
                                <th class="text-center">All</th>
                                <th class="text-center">EFT</th>
                                <th class="text-center">Cheque</th>
                                <th class="text-center">Cash</th>
                                <th class="text-end">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payruns as $payrun)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="payrun-period-icon me-2">
                                            <i class="ti ti-calendar-month"></i>
                                        </div>
                                        <span class="fw-medium">{{ formatMonthYear($payrun['term']) }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('payrun.payslips.pdf', $payrun['term']) }}" target="_blank" class="badge bg-primary-subtle text-primary" data-bs-toggle="tooltip" data-bs-original-title="View Payslips">
                                        {{ $payrun['payslips'] }}
                                    </a>
                                </td>
                                <td class="text-center">
                                    @if($payrun['all'] > 0)
                                    <a class="rc-table-action rc-table-action-view" target="_blank" href="{{ route('payrun.pdf', ['term' => $payrun['term'], 'type' => 'all']) }}" data-bs-toggle="tooltip" data-bs-original-title="All Payments PDF">
                                        <i class="ti ti-file-type-pdf"></i>
                                    </a>
                                    <span class="badge bg-light text-dark">{{ $payrun['all'] }}</span>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($payrun['eft'] > 0)
                                    <a class="rc-table-action rc-table-action-view" target="_blank" href="{{ route('payrun.pdf', ['term' => $payrun['term'], 'type' => 'EFT']) }}" data-bs-toggle="tooltip" data-bs-original-title="EFT Payments PDF">
                                        <i class="ti ti-file-type-pdf"></i>
                                    </a>
                                    <span class="badge bg-light text-dark">{{ $payrun['eft'] }}</span>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($payrun['cheque'] > 0)
                                    <a class="rc-table-action rc-table-action-view" target="_blank" href="{{ route('payrun.pdf', ['term' => $payrun['term'], 'type' => 'Cheque']) }}" data-bs-toggle="tooltip" data-bs-original-title="Cheque Payments PDF">
                                        <i class="ti ti-file-type-pdf"></i>
                                    </a>
                                    <span class="badge bg-light text-dark">{{ $payrun['cheque'] }}</span>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($payrun['cash'] > 0)
                                    <a class="rc-table-action rc-table-action-view" target="_blank" href="{{ route('payrun.pdf', ['term' => $payrun['term'], 'type' => 'Cash']) }}" data-bs-toggle="tooltip" data-bs-original-title="Cash Payments PDF">
                                        <i class="ti ti-file-type-pdf"></i>
                                    </a>
                                    <span class="badge bg-light text-dark">{{ $payrun['cash'] }}</span>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="fw-semibold">R {{ $payrun['total_netpay'] }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <x-rc-table.empty icon="ti ti-cash-off" title="No Payment Runs" message="No payment runs have been created yet." />
                @endif
                </x-rc-table.content>
                <x-rc-table.footer :paginator="$payruns" />
            </x-rc-table>
        </div>
    </div>
</div>

<script>
    function validatePayrunForm(term) {
        const paymentMethod = document.getElementById('payment_method_' + term);
        const paymentMethodError = document.getElementById('payment_method_error_' + term);

        if (!paymentMethod || !paymentMethodError) {
            console.error("Element not found! Check term value:", term);
            return;
        }

        paymentMethodError.style.display = 'none';

        if (paymentMethod.value.trim() === '') {
            paymentMethodError.style.display = 'block';
        } else {
            document.getElementById('payrunForm' + term).submit();
        }
    }

    // Hide error message when user selects a value
    document.querySelectorAll('.payrun-page select').forEach(select => {
        select.addEventListener('change', function() {
            const errorSpan = this.closest('.mb-3').querySelector('.payment-method-error');
            if (errorSpan) {
                errorSpan.style.display = 'none';
            }
        });
    });
</script>
@endsection