@extends('layouts.main')
@push('css')
<style>
    .badge-pending {
        background-color: #ffc107;
        color: #000;
    }

    .badge-reviewed {
        background-color: #28a745;
        color: white;
    }

    .badge-incomplete {
        background-color: #dc3545;
        color: white;
    }

    .stat-card {
        border-radius: 8px;
        padding: 20px;
        text-align: center;
    }

    .stat-card .stat-number {
        font-size: 32px;
        font-weight: 700;
    }

    .stat-card .stat-label {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
    }

    .stat-pending {
        background-color: #fff3cd;
        border: 1px solid #ffc107;
    }

    .stat-pending .stat-number {
        color: #856404;
    }

    .stat-reviewed {
        background-color: #d4edda;
        border: 1px solid #28a745;
    }

    .stat-reviewed .stat-number {
        color: #155724;
    }

    .clock-time {
        font-weight: 600;
        font-size: 14px;
    }

    .clock-in-time {
        color: #28a745;
    }

    .clock-out-time {
        color: #dc3545;
    }

    .missing-clock-out {
        color: #dc3545;
        font-style: italic;
    }

    .employee-name {
        font-weight: 600;
    }

    .employee-branch {
        font-size: 11px;
        color: #6c757d;
    }

    .review-btn {
        padding: 4px 12px;
        font-size: 12px;
    }

    .bulk-actions {
        display: none;
    }

    .bulk-actions.active {
        display: block;
    }
</style>
@endpush

@section('page-title')
{{ __('Attendance Review') }}
@endsection

@section('page-breadcrumb')
{{ __('Attendance') }},{{ __('Review') }}
@endsection

@section('page-action')
<div>
    @if($pendingCount > 0)
    <span class="badge bg-warning text-dark me-2">
        {{ $pendingCount }} {{ __('Pending Reviews') }}
    </span>
    @endif
    <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-rc-icon"
        data-bs-toggle="tooltip" data-bs-original-title="{{ __('Back to Attendance') }}">
        <i class="ti ti-arrow-left text-white"></i>
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <!-- Stats Cards -->
    <div class="col-md-4">
        <div class="stat-card stat-pending">
            <div class="stat-number" id="pending-count">{{ $pendingCount }}</div>
            <div class="stat-label">{{ __('Pending Reviews') }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-reviewed">
            <div class="stat-number" id="reviewed-count">
                {{ \App\Models\Hrm\Attendance::where('workspace', getActiveWorkspace())->whereNotNull('hr_reviewed_at')->where('hr_reviewed_at', '>=', now()->startOfWeek())->count() }}
            </div>
            <div class="stat-label">{{ __('Reviewed This Week') }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="background-color: #f8d7da; border: 1px solid #dc3545;">
            <div class="stat-number" style="color: #721c24;">
                {{ \App\Models\Hrm\Attendance::where('workspace', getActiveWorkspace())->whereDate('date', today())->where(function($q) { $q->whereNull('clock_out')->orWhere('clock_out', '00:00:00'); })->count() }}
            </div>
            <div class="stat-label">{{ __('Incomplete Today') }}</div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-sm-12">
        <x-rc-table title="{{ __('Attendance Records') }}">
            <x-slot name="headerActions">
                <div class="bulk-actions" id="bulk-actions">
                    <button type="button" class="btn btn-sm btn-rc-primary" onclick="bulkReview('use_shift_end')">
                        <i class="ti ti-clock"></i> {{ __('Use Shift End Time') }}
                    </button>
                    <button type="button" class="btn btn-sm btn-rc-primary" data-bs-toggle="modal" data-bs-target="#bulkCustomTimeModal">
                        <i class="ti ti-edit"></i> {{ __('Custom Time') }}
                    </button>
                    <span class="ms-2 text-muted" id="selected-count">0 selected</span>
                </div>
            </x-slot>

            <x-rc-table.filter action="{{ route('attendance.review.index') }}" method="GET" id="attendance_review_filter_form">
                <x-rc-table.filter-group label="{{ __('Status') }}">
                    <select name="status" class="form-control">
                        <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>{{ __('Pending Review') }}</option>
                        <option value="reviewed" {{ $status == 'reviewed' ? 'selected' : '' }}>{{ __('Reviewed') }}</option>
                        <option value="incomplete" {{ $status == 'incomplete' ? 'selected' : '' }}>{{ __('All Incomplete') }}</option>
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>{{ __('All') }}</option>
                    </select>
                </x-rc-table.filter-group>
                <x-rc-table.filter-group label="{{ __('Employee') }}">
                    <select name="employee_id" class="form-control">
                        <option value="">{{ __('All') }}</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </x-rc-table.filter-group>
                <x-rc-table.filter-group label="{{ __('From Date') }}">
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                </x-rc-table.filter-group>
                <x-rc-table.filter-group label="{{ __('To Date') }}">
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                </x-rc-table.filter-group>
            </x-rc-table.filter>

            <x-rc-table.content>
                <table class="rc-table" id="attendance-review">
                    <thead>
                        <tr>
                            <th width="30">
                                <input type="checkbox" class="form-check-input" id="select-all">
                            </th>
                        <th>{{ __('Employee') }}</th>
                        <th class="col-date">{{ __('Date') }}</th>
                        <th class="col-date">{{ __('Clock In') }}</th>
                        <th class="col-date">{{ __('Clock Out') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Reviewed By') }}</th>
                        <th class="col-actions">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $attendance)
                    <tr data-id="{{ $attendance->id }}">
                        <td>
                            @if($attendance->requires_hr_review && !$attendance->hr_reviewed_at)
                            <input type="checkbox" class="form-check-input row-select" value="{{ $attendance->id }}">
                            @endif
                        </td>
                        <td>
                            <div class="employee-name">
                                @if($attendance->employee)
                                {{ $attendance->employee->first_name }} {{ $attendance->employee->last_name }}
                                @else
                                N/A
                                @endif
                            </div>
                            @if($attendance->branch)
                            <div class="employee-branch">{{ $attendance->branch->name }}</div>
                            @endif
                        </td>
                        <td>
                            {{ $attendance->date->format('D') . ', ' . formatDate($attendance->date) }}
                        </td>
                        <td>
                            <span class="clock-time clock-in-time">
                                {{ $attendance->clock_in ? formatTime($attendance->clock_in) : '-' }}
                            </span>
                        </td>
                        <td>
                            @if($attendance->clock_out && $attendance->clock_out != '00:00:00')
                            <span class="clock-time clock-out-time">
                                {{ formatTime($attendance->clock_out) }}
                            </span>
                            @else
                            <span class="missing-clock-out">
                                <i class="ti ti-alert-triangle"></i> {{ __('Missing') }}
                            </span>
                            @endif
                        </td>
                        <td>
                            @if($attendance->hr_reviewed_at)
                            <span class="badge badge-reviewed">
                                <i class="ti ti-check"></i> {{ __('Reviewed') }}
                            </span>
                            @elseif($attendance->requires_hr_review)
                            <span class="badge badge-pending">
                                <i class="ti ti-clock"></i> {{ __('Pending') }}
                            </span>
                            @else
                            <span class="badge badge-incomplete">
                                <i class="ti ti-x"></i> {{ __('Incomplete') }}
                            </span>
                            @endif
                        </td>
                        <td>
                            @if($attendance->hr_reviewed_at)
                            <small>
                                {{ $attendance->hrReviewer?->name ?? 'System' }}<br>
                                <span class="text-muted">{{ formatDateTime($attendance->hr_reviewed_at) }}</span>
                            </small>
                            @else
                            -
                            @endif
                        </td>
                        <td>
                            @if(!$attendance->hr_reviewed_at && ($attendance->requires_hr_review || (!$attendance->clock_out || $attendance->clock_out == '00:00:00')))
                            <button type="button" class="btn btn-sm btn-rc-primary review-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#reviewModal"
                                data-id="{{ $attendance->id }}"
                                data-employee="{{ $attendance->employee ? $attendance->employee->first_name . ' ' . $attendance->employee->last_name : 'N/A' }}"
                                data-date="{{ formatDate($attendance->date) }}"
                                data-clock-in="{{ $attendance->clock_in }}">
                                <i class="ti ti-edit"></i> {{ __('Review') }}
                            </button>
                            @elseif($attendance->hr_reviewed_at)
                            <span class="text-success"><i class="ti ti-check"></i></span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <x-rc-table.empty :asRow="true" :colspan="8" icon="ti ti-mood-smile" title="{{ __('All Clear!') }}" message="{{ __('No attendance records require review') }}" />
                    @endforelse
                </tbody>
                </table>
            </x-rc-table.content>

            <x-rc-table.footer :paginator="$attendances" />
        </x-rc-table>
    </div>
</div>

<!-- Single Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewModalLabel">{{ __('Review Attendance') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="review-form">
                <div class="modal-body">
                    <input type="hidden" id="review-attendance-id" name="attendance_id">

                    <div class="alert alert-info">
                        <strong>{{ __('Employee') }}:</strong> <span id="review-employee"></span><br>
                        <strong>{{ __('Date') }}:</strong> <span id="review-date"></span><br>
                        <strong>{{ __('Clock In') }}:</strong> <span id="review-clock-in"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Clock Out Time') }} <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="review-clock-out" name="clock_out" required>
                        <small class="text-muted">{{ __('Enter the correct clock out time for this record') }}</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('HR Notes') }}</label>
                        <textarea class="form-control" id="review-notes" name="hr_notes" rows="3"
                            placeholder="{{ __('Optional notes explaining the review decision...') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-rc-primary" id="submit-review">
                        <i class="ti ti-check"></i> {{ __('Approve & Update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Custom Time Modal -->
<div class="modal fade" id="bulkCustomTimeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Bulk Review - Custom Time') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('Clock Out Time') }} <span class="text-danger">*</span></label>
                    <input type="time" class="form-control" id="bulk-clock-out" required>
                    <small class="text-muted">{{ __('This time will be applied to all selected records') }}</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('HR Notes') }}</label>
                    <textarea class="form-control" id="bulk-notes" rows="2" placeholder="{{ __('Bulk reviewed by HR') }}"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-rc-primary" onclick="bulkReview('custom_time')">
                    <i class="ti ti-check"></i> {{ __('Apply to Selected') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Select all checkbox
    $('#select-all').change(function() {
        $('.row-select').prop('checked', $(this).prop('checked'));
        updateBulkActions();
    });
    
    // Individual row checkbox
    $(document).on('change', '.row-select', function() {
        updateBulkActions();
    });
    
    // Update bulk actions visibility
    function updateBulkActions() {
        var selected = $('.row-select:checked').length;
        if (selected > 0) {
            $('#bulk-actions').addClass('active');
            $('#selected-count').text(selected + ' selected');
        } else {
            $('#bulk-actions').removeClass('active');
        }
    }
    
    // Review modal - populate data
    $('#reviewModal').on('show.bs.modal', function(e) {
        var button = $(e.relatedTarget);
        $('#review-attendance-id').val(button.data('id'));
        $('#review-employee').text(button.data('employee'));
        $('#review-date').text(button.data('date'));
        $('#review-clock-in').text(button.data('clock-in'));
        $('#review-clock-out').val('18:00');
        $('#review-notes').val('');
    });
    
    // Submit single review
    $('#review-form').submit(function(e) {
        e.preventDefault();
        
        var id = $('#review-attendance-id').val();
        var submitBtn = $('#submit-review');
        submitBtn.prop('disabled', true).html('<i class="ti ti-loader"></i> Processing...');
        
        $.ajax({
            url: '{{ url("attendance-review") }}/' + id,
            method: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                clock_out: $('#review-clock-out').val(),
                hr_notes: $('#review-notes').val()
            },
            success: function(response) {
                if (response.status === 1) {
                    toastrs('Success', response.message, 'success');
                    $('#reviewModal').modal('hide');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    toastrs('Error', response.message, 'error');
                }
            },
            error: function(xhr) {
                var message = xhr.responseJSON?.message || 'An error occurred';
                toastrs('Error', message, 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="ti ti-check"></i> {{ __("Approve & Update") }}');
            }
        });
    });
});

// Bulk review function
function bulkReview(action) {
    var selectedIds = [];
    $('.row-select:checked').each(function() {
        selectedIds.push($(this).val());
    });
    
    if (selectedIds.length === 0) {
        toastrs('Error', '{{ __("Please select at least one record") }}', 'error');
        return;
    }
    
    var data = {
        _token: '{{ csrf_token() }}',
        attendance_ids: selectedIds,
        action: action
    };
    
    if (action === 'custom_time') {
        var customTime = $('#bulk-clock-out').val();
        if (!customTime) {
            toastrs('Error', '{{ __("Please enter a clock out time") }}', 'error');
            return;
        }
        data.custom_time = customTime;
        data.hr_notes = $('#bulk-notes').val();
    }
    
    $.ajax({
        url: '{{ route("attendance.review.bulk") }}',
        method: 'POST',
        data: data,
        success: function(response) {
            if (response.status === 1) {
                toastrs('Success', response.message, 'success');
                $('#bulkCustomTimeModal').modal('hide');
                setTimeout(function() {
                    location.reload();
                }, 1000);
            } else {
                toastrs('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            var message = xhr.responseJSON?.message || 'An error occurred';
            toastrs('Error', message, 'error');
        }
    });
}
</script>
@endpush
