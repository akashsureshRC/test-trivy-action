@extends('layouts.main')
@section('page-title')
    {{ __('Leave Requests') }}
@endsection
@section('page-breadcrumb')
    {{ __('Leave Requests') }}
@endsection
@section('page-action')
    <div>
        @stack('addButtonHook')
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <x-rc-table>
                <x-rc-table.content>
                    <table class="rc-table" id="assets">
                        <thead>
                            <tr>
                                @if (in_array(\Auth::user()->type, \Auth::user()->not_emp_type))
                                    <th>{{ __('Employee') }}</th>
                                @endif
                                <th>{{ __('Leave Type') }}</th>
                                <th class="col-date">{{ __('Applied On') }}</th>
                                <th class="col-date">{{ __('Start Date') }}</th>
                                <th class="col-date">{{ __('End Date') }}</th>
                                <th>{{ __('Total Days') }}</th>
                                <th>{{ __('Leave Reason') }}</th>
                                <th class="col-status">{{ __('status') }}</th>
                                @if (Laratrust::hasPermission('leave edit') || Laratrust::hasPermission('leave delete'))
                                    <th class="col-actions">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($leaves as $leave)
                                <tr>
                                    @if (in_array(\Auth::user()->type, \Auth::user()->not_emp_type))
                                        <td>
                                            @if(!empty($leave->user_id))
                                                {{ $leave->name }}
                                            @elseif(!empty($leave->employee_id))
                                                @php
                                                    $empProfile = \App\Models\Hrm\Employee::find($leave->employee_id);
                                                @endphp
                                                {{ $empProfile ? $empProfile->first_name . ' ' . $empProfile->last_name : '--' }}
                                            @else
                                                --
                                            @endif
                                            @if(!empty($leave->source) && $leave->source == 'ess')
                                                <span class="rc-status rc-status-info ms-1" style="font-size: 10px;">ESS</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td>
                                        @if(!empty($leave->leave_management_id))
                                            @php
                                                $leaveManagement = \App\Models\Hrm\LeaveManagement::find($leave->leave_management_id);
                                            @endphp
                                            {{ $leaveManagement->leave_name ?? '' }}
                                        @endif
                                    </td>
                                    <td class="col-date">{{ companyDateFormate($leave->applied_on) }}</td>
                                    <td class="col-date">{{ companyDateFormate($leave->start_date) }}</td>
                                    <td class="col-date">{{ companyDateFormate($leave->end_date) }}</td>
                                    <td>{{ $leave->total_leave_days }}</td>
                                    <td>
                                        <p style="white-space: nowrap;
                                            width: 200px;
                                            overflow: hidden;
                                            text-overflow: ellipsis;">
                                            {{ !empty($leave->leave_reason) ? $leave->leave_reason : '' }}
                                        </p>
                                    </td>
                                    <td class="col-status">
                                        @if ($leave->status == 'Pending')
                                            <span class="rc-status rc-status-warning">{{ $leave->status }}</span>
                                        @elseif($leave->status == 'Approved')
                                            <span class="rc-status rc-status-success">{{ $leave->status }}</span>
                                        @elseif($leave->status == 'Rejected')
                                            <span class="rc-status rc-status-danger">{{ $leave->status }}</span>
                                        @endif
                                    </td>
                                    @if (Laratrust::hasPermission('leave edit') || Laratrust::hasPermission('leave delete'))
                                        <td class="col-actions">
                                            <div class="rc-table-actions">
                                                @php
                                                    // Can cancel/delete if: (Pending OR Approved) AND leave hasn't started yet
                                                    $leaveNotStarted = \Carbon\Carbon::parse($leave->start_date)->startOfDay()->gt(\Carbon\Carbon::now()->startOfDay());
                                                    $canModify = in_array($leave->status, ['Pending', 'Approved']) && $leaveNotStarted;
                                                @endphp
                                                @if ($canModify)
                                                    @if ($leave->status == 'Pending')
                                                        @permission('leave edit')
                                                            <a class="rc-table-action rc-table-action-edit"
                                                                data-url="{{ URL::to('leave/' . $leave->id . '/edit') }}"
                                                                data-ajax-popup="true" data-size="md"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-title="{{ __('Edit Leave') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-edit"></i>
                                                            </a>
                                                        @endpermission
                                                    @endif

                                                    @permission('leave delete')
                                                        {{ Form::open(['route' => ['leave.destroy', $leave->id], 'class' => 'm-0 d-inline']) }}
                                                        @method('DELETE')
                                                        <a href="#" class="rc-table-action rc-table-action-delete bs-pass-para show_confirm"
                                                            data-bs-toggle="tooltip" title=""
                                                            data-bs-original-title="Delete" aria-label="Delete"
                                                            data-confirm="{{ __('Are You Sure?') }}"
                                                            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="delete-form-{{ $leave->id }}">
                                                            <i class="ti ti-trash"></i>
                                                        </a>
                                                        {{ Form::close() }}
                                                    @endpermission
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                @php
                                    $colCount = 7; // Base columns
                                    if (in_array(\Auth::user()->type, \Auth::user()->not_emp_type)) $colCount++;
                                    if (Laratrust::hasPermission('leave edit') || Laratrust::hasPermission('leave delete')) $colCount++;
                                @endphp
                                <x-rc-table.empty :asRow="true" :colspan="$colCount" icon="ti ti-calendar-off" title="{{ __('No Leave Requests') }}" message="{{ __('No leave requests have been submitted yet.') }}" />
                            @endforelse
                        </tbody>
                    </table>
                </x-rc-table.content>

                <x-rc-table.footer :paginator="$leaves" />
            </x-rc-table>
        </div>
    </div>
@endsection


@push('scripts')
    <script>
        $(document).on('change', '#employee_id', function() {
            var employee_id = $(this).val();
            $.ajax({
                url: '{{ route('leave.jsoncount') }}',
                type: 'POST',
                data: {
                    "employee_id": employee_id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    var oldval = $('#leave_type_id').val();
                    $('#leave_type_id').empty();
                    $('#leave_type_id').append(
                        '<option value="">{{ __('Select Leave Type') }}</option>');

                    $.each(data, function(key, value) {
                        if (value.total_leave >= value.days) {
                            $('#leave_type_id').append('<option value="' + value.id +
                                '" disabled>' + value.title + '&nbsp(' + value.total_leave +
                                '/' + value.days + ')</option>');
                        } else {
                            $('#leave_type_id').append('<option value="' + value.id + '">' +
                                value.title + '&nbsp(' + value.total_leave + '/' + value
                                .days + ')</option>');
                            if (oldval) {
                                if (oldval == value.id) {
                                    $("#leave_type_id option[value=" + oldval + "]").attr(
                                        "selected", "selected");
                                }
                            }
                        }
                    });

                }
            });
        });
    </script>
@endpush
