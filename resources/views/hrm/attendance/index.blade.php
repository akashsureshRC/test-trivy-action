@extends('layouts.main')
@push('css')
<style>
    .badge-app {
        background-color: #28a745;
        color: white;
    }

    .badge-hr {
        background-color: #6c757d;
        color: white;
    }

    .badge-geofence {
        background-color: #17a2b8;
        color: white;
        font-size: 10px;
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

    .duration-badge {
        background-color: #e9ecef;
        color: #495057;
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 4px;
    }

    .geolocation-info {
        font-size: 11px;
        color: #6c757d;
    }

    .geolocation-info i {
        color: #17a2b8;
    }
</style>
@endpush
@section('page-title')
{{ __('Attendance') }}
@endsection
@section('page-breadcrumb')
{{ __('Attendance') }}
@endsection
@php
$company_settings = getCompanyAllSetting();
$pendingReviewCount = \App\Models\Hrm\Attendance::where('workspace', getActiveWorkspace())
->where('requires_hr_review', true)
->whereNull('hr_reviewed_at')
->count();
@endphp
@section('page-action')
<div>
    @permission('attendance manage')
    <a href="{{ route('attendance.review.index') }}" class="btn btn-sm btn-rc-icon position-relative"
        style="background-color: {{ $pendingReviewCount > 0 ? '#dc3545' : '#6c757d' }}"
        data-bs-toggle="tooltip" data-bs-original-title="{{ __('HR Review') }}">
        <i class="ti ti-clipboard-check text-white"></i>
        @if($pendingReviewCount > 0)
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark" style="font-size: 10px;">
            {{ $pendingReviewCount }}
        </span>
        @endif
    </a>
    @endpermission
    @permission('attendance import')
    <a href="#" class="btn btn-sm btn-rc-icon" data-ajax-popup="true" data-title="{{ __('Import') }}"
        data-url="{{ route('attendance.file.import') }}" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Import') }}">
        <i class="ti ti-file-import text-white"></i>
    </a>
    @endpermission
    @permission('attendance create')
    <a href="#" class="btn btn-sm btn-rc-icon" data-ajax-popup="true" data-size="md"
        data-title="{{ __('Mark Attendance') }}" data-url="{{ route('attendance.create') }}"
        data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}">
        <i class="ti ti-plus text-white"></i>
    </a>
    @endpermission
</div>
@endsection
@section('content')
<div class="row">
    <div class="col-sm-12">
        <x-rc-table title="{{ __('Attendance Logs') }}">
            <x-rc-table.filter action="{{ route('attendance.index') }}" method="GET" id="attendance_filter">
                @if(in_array(Auth::user()->type, Auth::user()->not_emp_type))
                <x-rc-table.filter-group label="{{ !empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch') }}">
                    {{ Form::select('branch', $branch, isset($_GET['branch']) ? $_GET['branch'] : '', array('class' => 'form-control', 'id' => 'branch')) }}
                </x-rc-table.filter-group>
                <x-rc-table.filter-group label="{{ !empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department') }}">
                    {{ Form::select('department', $department, isset($_GET['department']) ? $_GET['department'] : '', array('class' => 'form-control select', 'id' => 'department')) }}
                </x-rc-table.filter-group>
                <x-rc-table.filter-group label="{{ __('Employee') }}">
                    {{ Form::select('employee_id', $employees, isset($_GET['employee_id']) ? $_GET['employee_id'] : '', array('class' => 'form-control', 'id' => 'employee_id')) }}
                </x-rc-table.filter-group>
                @endif
                <x-rc-table.filter-group label="{{ __('From Date') }}">
                    <input type="date" name="date_from" class="form-control" value="{{ isset($_GET['date_from']) ? $_GET['date_from'] : '' }}">
                </x-rc-table.filter-group>
                <x-rc-table.filter-group label="{{ __('To Date') }}">
                    <input type="date" name="date_to" class="form-control" value="{{ isset($_GET['date_to']) ? $_GET['date_to'] : '' }}">
                </x-rc-table.filter-group>
            </x-rc-table.filter>

            <x-rc-table.content>
                <table class="rc-table" id="assets">
                    <thead>
                        <tr>
                            @if (Laratrust::hasPermission('attendance create') || Laratrust::hasPermission('attendance edit'))
                            <th>{{ __('Employee') }}</th>
                            @endif
                            <th class="col-date">{{ __('Date') }}</th>
                            <th class="col-date">{{ __('Clock In') }}</th>
                            <th class="col-date">{{ __('Clock Out') }}</th>
                            <th>{{ __('Duration') }}</th>
                            <th>{{ __('Marked By') }}</th>
                            @if (Laratrust::hasPermission('attendance edit') || Laratrust::hasPermission('attendance delete'))
                            <th class="col-actions" width="120px">{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attendances as $attendance)
                        <tr>
                            @if (Laratrust::hasPermission('attendance create') || Laratrust::hasPermission('attendance edit'))
                            <td>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <span class="fw-bold">{{ !empty($attendance->employee) ? $attendance->employee->first_name . ' ' . $attendance->employee->last_name : '-' }}</span>
                                        @if(!empty($attendance->employee))
                                        <br><small class="text-muted">{{ $attendance->employee->employee_id }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            @endif
                            <td>
                                <span class="fw-bold">{{ companyDateFormate($attendance->date) }}</span>
                                <br><small class="text-muted">{{ \Carbon\Carbon::parse($attendance->date)->format('l') }}</small>
                            </td>
                            <td>
                                @if($attendance->clock_in && $attendance->clock_in != '00:00:00')
                                <span class="clock-time clock-in-time">
                                    <i class="ti ti-login"></i> {{ formatTime($attendance->clock_in) }}
                                </span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($attendance->clock_out && $attendance->clock_out != '00:00:00')
                                <span class="clock-time clock-out-time">
                                    <i class="ti ti-logout"></i> {{ formatTime($attendance->clock_out) }}
                                </span>
                                @else
                                <span class="rc-status rc-status-warning">
                                    <i class="ti ti-clock"></i> {{ __('Active') }}
                                </span>
                                @endif
                            </td>
                            <td>
                                @php
                                $duration = 0;
                                if($attendance->clock_in && $attendance->clock_out && $attendance->clock_out != '00:00:00') {
                                $clockIn = \Carbon\Carbon::parse($attendance->clock_in);
                                $clockOut = \Carbon\Carbon::parse($attendance->clock_out);
                                $duration = $clockOut->diffInMinutes($clockIn);
                                }
                                @endphp
                                @if($duration > 0)
                                <span class="duration-badge">
                                    {{ floor($duration / 60) }}h {{ $duration % 60 }}m
                                </span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($attendance->marked_by == \App\Models\Hrm\Attendance::MARKED_BY_EMPLOYEE)
                                <span class="badge badge-app">
                                    <i class="ti ti-device-mobile"></i> {{ __('App') }}
                                </span>
                                @else
                                <span class="badge badge-hr">
                                    <i class="ti ti-user"></i> {{ __('HR') }}
                                </span>
                                @endif
                            </td>
                            <td class="col-actions">
                                @permission('attendance edit')
                                <a href="#" class="rc-table-action rc-table-action-edit"
                                    data-url="{{ URL::to('attendance/' . $attendance->id . '/edit') }}"
                                    data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                    data-title="{{ __('Edit Attendance') }}"
                                    data-bs-original-title="{{ __('Edit') }}">
                                    <i class="ti ti-edit"></i>
                                </a>
                                @endpermission

                                @permission('attendance delete')
                                {!! Form::open(['route' => ['attendance.destroy', $attendance->id], 'method' => 'DELETE', 'id' => 'delete-form-' . $attendance->id, 'style' => 'display:inline']) !!}
                                <a href="#" class="rc-table-action rc-table-action-delete show_confirm"
                                    data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('Delete') }}">
                                    <i class="ti ti-trash"></i>
                                </a>
                                {!! Form::close() !!}
                                @endpermission
                            </td>
                        </tr>
                        @empty
                        <x-rc-table.empty :asRow="true" :colspan="8" icon="ti ti-calendar-off" title="{{ __('No Records') }}" message="{{ __('No attendance logs found for the selected period.') }}" />
                        @endforelse
                    </tbody>
                </table>
            </x-rc-table.content>

            <x-rc-table.footer :paginator="$attendances" />
        </x-rc-table>
    </div>
</div>
@endsection
@push('scripts')
    <script type="text/javascript">
        $(document).on('change', '#branch', function() {
            var branch_id = $(this).val();
            getDepartment(branch_id);
        });

        function getDepartment(branch_id) {
            var data = {
                "branch_id": branch_id,
                "_token": "{{ csrf_token() }}",
            }
            $.ajax({
                url: '{{ route('employee.getdepartments') }}',
                method: 'POST',
                data: data,
                success: function(data) {
                    $('#department').empty();
                    $('#department').append('<option value="">{{ __('All') }}</option>');

                    $.each(data, function(key, value) {
                        $('#department').append('<option value="' + key + '">' + value + '</option>');
                    });
                    $('#department').val('');
                }
            });
        }
    </script>
@endpush