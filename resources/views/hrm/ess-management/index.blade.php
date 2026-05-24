@extends('layouts.main')

@section('page-title')
    {{ __('ESS Management') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee Self-Service') }}
@endsection

@section('page-action')
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-rc-primary" data-bs-toggle="modal" data-bs-target="#bulkInviteModal">
            <i class="ti ti-send me-1"></i> {{ __('Bulk Invite') }}
        </button>
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Stats Cards -->
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="ti ti-users"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    <p class="text-muted mb-0">{{ __('Total Employees') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="ti ti-check"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $stats['enabled'] }}</h3>
                    <p class="text-muted mb-0">{{ __('Active') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="ti ti-clock"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                    <p class="text-muted mb-0">{{ __('Pending Setup') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                    <i class="ti ti-mail-off"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $stats['not_invited'] }}</h3>
                    <p class="text-muted mb-0">{{ __('Not Invited') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <x-rc-table title="{{ __('Employee Self-Service Access') }}">
            <x-rc-table.content>
                <table class="rc-table" id="ess-table">
                        <thead>
                            <tr>
                                <th class="col-checkbox">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
                                <th>{{ __('Employee') }}</th>
                                <th class="col-id">{{ __('Employee ID') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th class="col-status">{{ __('ESS Status') }}</th>
                                <th class="col-date">{{ __('Last Login') }}</th>
                                <th class="col-actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $employee)
                            <tr>
                                <td class="col-checkbox">
                                    <input type="checkbox" class="form-check-input employee-checkbox" name="employee_ids[]" value="{{ $employee->id }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h6 class="mb-0">{{ $employee->first_name }} {{ $employee->last_name }}</h6>
                                            <small class="text-muted">{{ $employee->designation->name ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="col-id">{{ $employee->employee_id }}</td>
                                <td>
                                    @if($employee->email)
                                        {{ $employee->email }}
                                    @else
                                        <span class="text-danger">{{ __('No email') }}</span>
                                    @endif
                                </td>
                                <td class="col-status">
                                    <span class="rc-status rc-status-{{ $employee->ess_status['class'] }}">
                                        {{ $employee->ess_status['label'] }}
                                    </span>
                                </td>
                                <td class="col-date">
                                    @if($employee->ess_last_login_at)
                                        {{ $employee->ess_last_login_at->diffForHumans() }}
                                    @else
                                        <span class="text-muted">{{ __('Never') }}</span>
                                    @endif
                                </td>
                                <td class="col-actions">
                                    @if($employee->email)
                                        @if($employee->ess_status['label'] === 'Active')
                                            <form action="{{ route('ess-management.disable', $employee->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger show_confirm" data-confirm="{{ __('Are you sure?') }}" data-text="{{ __('Are you sure you want to disable ESS access for this employee?') }}">
                                                    <i class="ti ti-lock"></i> {{ __('Disable') }}
                                                </button>
                                            </form>
                                        @elseif($employee->ess_status['label'] === 'Disabled')
                                            <form action="{{ route('ess-management.enable', $employee->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    <i class="ti ti-lock-open"></i> {{ __('Enable') }}
                                                </button>
                                            </form>
                                        @elseif($employee->ess_status['label'] === 'Pending Setup' || $employee->ess_status['label'] === 'Invite Expired')
                                            <form action="{{ route('ess-management.resend-invitation', $employee->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                                    <i class="ti ti-refresh"></i> {{ __('Resend') }}
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('ess-management.send-invitation', $employee->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    <i class="ti ti-send"></i> {{ __('Send Invite') }}
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <span class="text-muted small">{{ __('Add email first') }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <x-rc-table.empty :asRow="true" :colspan="7" icon="ti ti-users" title="{{ __('No employees found') }}" message="{{ __('There are no employees to display.') }}" />
                            @endforelse
                        </tbody>
                    </table>
            </x-rc-table.content>

            <x-rc-table.footer :paginator="$employees" />
        </x-rc-table>
    </div>
</div>

<!-- Bulk Invite Modal -->
<div class="modal fade" id="bulkInviteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('ess-management.bulk-invitations') }}" method="POST" id="bulkInviteForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Send Bulk Invitations') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Select employees from the table below and click "Send Invitations" to invite them to the Employee Self-Service portal.') }}</p>
                    <div id="selectedEmployees"></div>
                    <p class="text-muted small mb-0">
                        <i class="ti ti-info-circle me-1"></i>
                        {{ __('Employees who already have ESS access will be skipped.') }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-rc-primary" id="sendBulkBtn" disabled>
                        <i class="ti ti-send me-1"></i> {{ __('Send Invitations') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.employee-checkbox');
    const bulkForm = document.getElementById('bulkInviteForm');
    const selectedDiv = document.getElementById('selectedEmployees');
    const sendBulkBtn = document.getElementById('sendBulkBtn');

    // Select all functionality
    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateSelectedCount();
    });

    // Individual checkbox change
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });

    function updateSelectedCount() {
        const selected = document.querySelectorAll('.employee-checkbox:checked');
        const count = selected.length;
        
        // Update the form with selected IDs
        const existingInputs = bulkForm.querySelectorAll('input[name="employee_ids[]"]');
        existingInputs.forEach(input => input.remove());
        
        selected.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'employee_ids[]';
            input.value = cb.value;
            bulkForm.appendChild(input);
        });

        // Update UI
        if (count > 0) {
            selectedDiv.innerHTML = `<div class="alert alert-info"><strong>${count}</strong> employee(s) selected</div>`;
            sendBulkBtn.disabled = false;
        } else {
            selectedDiv.innerHTML = '<div class="alert alert-warning">No employees selected. Please select employees from the table first.</div>';
            sendBulkBtn.disabled = true;
        }
    }

    // Initialize
    updateSelectedCount();
});
</script>
@endpush
