@extends('layouts.main')

@section('page-title')
{{ __('Invoices') }}
@endsection

@section('page-breadcrumb')
{{ __('Billing') }}, {{ __('Invoices') }}
@endsection

@section('content')
<div class="row stat-cards">
    <!-- Stats Cards -->
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-info bg-opacity-10 text-primary me-3">
                    <i class="ti ti-file-invoice"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ number_format($stats['total']) }}</h3>
                    <p class="text-muted mb-0">{{ __('Total Invoices') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="ti ti-clock"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ number_format($stats['pending']) }}</h3>
                    <p class="text-muted mb-0">{{ __('Pending') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                    <i class="ti ti-alert-triangle"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ number_format($stats['overdue']) }}</h3>
                    <p class="text-muted mb-0">{{ __('Overdue') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="ti ti-coin"></i>
                </div>
                <div>
                    <h3 class="mb-0">R {{ number_format($stats['revenue_this_month'], 0) }}</h3>
                    <p class="text-muted mb-0">{{ __('This Month') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <x-rc-table title="{{ __('Invoices') }}">
            <x-slot name="headerActions">
                @if(Auth::user()->type === 'super admin')
                <form action="{{ route('billing.invoices.generate') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-rc-primary btn-sm show_confirm" data-confirm="{{ __('Are you sure?') }}" data-text="{{ __('Generate invoices for all due billing cycles?') }}">
                        <i class="ti ti-plus me-1"></i>{{ __('Generate Invoices') }}
                    </button>
                </form>
                @endif
                <a href="{{ route('billing.invoices.export', request()->all()) }}" class="btn btn-rc-outline btn-sm">
                    <i class="ti ti-download me-1"></i>{{ __('Export CSV') }}
                </a>
            </x-slot>

            {{-- Filters --}}
            <x-rc-table.filter action="{{ route('billing.invoices.index') }}" method="GET">
                <x-rc-table.filter-group label="{{ __('Customer') }}" wide>
                    <select name="user_id" class="rc-filter-select">
                        <option value="">{{ __('All') }}</option>
                        @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ request('user_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }} ({{ $customer->email }})
                        </option>
                        @endforeach
                    </select>
                </x-rc-table.filter-group>

                <x-rc-table.filter-group label="{{ __('Status') }}">
                    <select name="status" class="rc-filter-select">
                        <option value="">{{ __('All') }}</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                    </select>
                </x-rc-table.filter-group>

                <x-rc-table.filter-group label="{{ __('From Date') }}" narrow>
                    <input type="date" name="from_date" class="rc-filter-input" value="{{ request('from_date') }}">
                </x-rc-table.filter-group>

                <x-rc-table.filter-group label="{{ __('To Date') }}" narrow>
                    <input type="date" name="to_date" class="rc-filter-input" value="{{ request('to_date') }}">
                </x-rc-table.filter-group>

                <x-slot name="additionalFilters">
                    <div class="form-check">
                        <input type="checkbox" name="overdue" value="1" id="overdue_filter" class="form-check-input" {{ request('overdue') ? 'checked' : '' }}>
                        <label class="form-check-label" for="overdue_filter">{{ __('Overdue Only') }}</label>
                    </div>
                </x-slot>
            </x-rc-table.filter>

            <x-rc-table.content>
                <table class="rc-table">
                    <thead>
                        <tr>
                            <th>{{ __('Invoice #') }}</th>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Payslips') }}</th>
                            <th class="col-amount">{{ __('Amount') }}</th>
                            <th class="col-date">{{ __('Issue Date') }}</th>
                            <th class="col-date">{{ __('Due Date') }}</th>
                            <th class="col-status">{{ __('Status') }}</th>
                            <th class="text-center">{{ __('EFT') }}</th>
                            <th class="col-actions">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                        <tr>
                            <td>
                                <a href="{{ route('billing.invoices.show', $invoice->id) }}" class="fw-medium">
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td>
                                @if($invoice->user)
                                <span class="fw-medium">{{ $invoice->user->name }}</span>
                                <br>
                                <small class="text-muted">{{ $invoice->user->email }}</small>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ number_format($invoice->total_payslips ?? 0) }}</td>
                            <td class="col-amount">R {{ number_format($invoice->total_amount, 2) }}</td>
                            <td class="col-date">{{ formatDate($invoice->created_at) }}</td>
                            <td class="col-date">
                                @if($invoice->due_date)
                                <span class="{{ $invoice->due_date->isPast() && $invoice->status !== 'paid' ? 'text-danger fw-bold' : '' }}">
                                    {{ formatDate($invoice->due_date) }}
                                </span>
                                @else
                                -
                                @endif
                            </td>
                            <td class="col-status">
                                <span class="rc-status rc-status-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status_display === 'past due' ? 'danger' : ($invoice->status === 'pending' ? 'warning' : 'secondary')) }}">
                                    <i class="ti ti-{{ $invoice->status === 'paid' ? 'check' : ($invoice->status_display === 'past due' ? 'alert-triangle' : ($invoice->status === 'pending' ? 'clock' : 'x')) }}"></i>
                                    {{ ucfirst($invoice->status_display) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($invoice->pending_eft_count > 0)
                                <a href="{{ route('billing.invoices.show', $invoice->id) }}" class="badge bg-info text-white" title="{{ __('Pending EFT Proof Submissions') }}">
                                    <i class="ti ti-file-upload me-1"></i>{{ $invoice->pending_eft_count }}
                                </a>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="col-actions">
                                <div class="dropdown">
                                    <button class="rc-table-action rc-table-action-neutral grid-actions-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="feather icon-more-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('billing.invoices.show', $invoice->id) }}">
                                                <i class="ti ti-eye me-2"></i>{{ __('View') }}
                                                @if($invoice->pending_eft_count > 0)
                                                <span class="badge bg-info ms-2">{{ $invoice->pending_eft_count }} EFT</span>
                                                @endif
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('billing.invoices.download', $invoice->id) }}">
                                                <i class="ti ti-download me-2"></i>{{ __('Download PDF') }}
                                            </a>
                                        </li>
                                        @if($invoice->status !== 'paid')
                                        @if($invoice->pending_eft_count > 0)
                                        <li>
                                            <a class="dropdown-item" href="{{ route('billing.invoices.show', $invoice->id) }}">
                                                <i class="ti ti-file-upload me-2 text-info"></i>{{ __('Review EFT Proof') }}
                                                <span class="badge bg-info ms-2">{{ $invoice->pending_eft_count }}</span>
                                            </a>
                                        </li>
                                        @else
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#manualPaymentModal-{{ $invoice->id }}">
                                                <i class="ti ti-cash me-2"></i>{{ __('Record Payment') }}
                                            </a>
                                        </li>
                                        @endif
                                        <li>
                                            <form action="{{ route('billing.invoices.send-reminder', $invoice->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="ti ti-mail me-2"></i>{{ __('Send Reminder') }}
                                                </button>
                                            </form>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- Manual Payment Modal -->
                        @if($invoice->status !== 'paid')
                        <div class="modal fade" id="manualPaymentModal-{{ $invoice->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('billing.invoices.manual-payment', $invoice->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ __('Record Manual Payment') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="text-muted mb-3">
                                                {{ __('Recording payment for Invoice') }} <strong>{{ $invoice->invoice_number }}</strong>
                                                <br>
                                                {{ __('Amount:') }} <strong>R {{ number_format($invoice->total_amount, 2) }}</strong>
                                            </p>

                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Payment Method') }} <span class="text-danger">*</span></label>
                                                <select name="payment_method" class="form-select" required>
                                                    <option value="eft">{{ __('EFT / Bank Transfer') }}</option>
                                                    <option value="cash">{{ __('Cash') }}</option>
                                                    <option value="cheque">{{ __('Cheque') }}</option>
                                                    <option value="other">{{ __('Other') }}</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Payment Reference') }} <span class="text-danger">*</span></label>
                                                <input type="text" name="payment_reference" class="form-control" required placeholder="{{ __('Bank reference or transaction ID') }}">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Payment Date') }} <span class="text-danger">*</span></label>
                                                <input type="date" name="payment_date" class="form-control" required value="{{ now()->format('Y-m-d') }}">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Notes') }}</label>
                                                <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('Optional notes about this payment') }}"></textarea>
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
                        @empty
                        <x-rc-table.empty :asRow="true" :colspan="9" icon="ti ti-file-invoice" title="{{ __('No Invoices Found') }}" message="{{ __('There are no invoices to display. Invoices will appear here once billing cycles are processed.') }}" />
                        @endforelse
                    </tbody>
                </table>
            </x-rc-table.content>

            <x-rc-table.footer :paginator="$invoices" />
        </x-rc-table>
    </div>
</div>
@endsection