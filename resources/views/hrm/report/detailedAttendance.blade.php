@extends('layouts.main')
@section('page-title')
{{ __('Attendance Report') }}
@endsection
@section('page-breadcrumb')
{{ __('Attendance Report') }}
@endsection
@section('page-action')
<div>
    <a href="{{ route('report.detailed.attendance.export', request()->all()) }}"
        class="btn btn-sm btn-rc-icon"
        data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Export to CSV') }}">
        <i class="ti ti-file-export text-white"></i>
    </a>
</div>
@endsection
@php
$company_settings = getCompanyAllSetting();
@endphp
@section('content')
<div class="row">
    <div class="col-sm-12">
        {{-- Filter Section --}}
        <x-rc-table class="mb-4">
            <x-rc-table.filter action="{{ route('report.detailed.attendance') }}" method="GET" id="report_detailed_attendance">
                <x-rc-table.filter-group label="{{ __('Start Date') }}">
                    {{ Form::date('start_date', $filterData['start_date'], ['class' => 'form-control']) }}
                </x-rc-table.filter-group>
                <x-rc-table.filter-group label="{{ __('End Date') }}">
                    {{ Form::date('end_date', $filterData['end_date'], ['class' => 'form-control']) }}
                </x-rc-table.filter-group>
                <x-rc-table.filter-group label="{{ !empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch') }}">
                    {{ Form::select('branch_id', $branch, $filterData['branch_id'], ['class' => 'form-control', 'id' => 'branch_id', 'placeholder' => __('Select Branch')]) }}
                </x-rc-table.filter-group>
                <x-rc-table.filter-group label="{{ !empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department') }}">
                    <select class="form-control department_id" name="department_id" id="department_id">
                        <option value="">{{ __('Select Department') }}</option>
                    </select>
                </x-rc-table.filter-group>
                <x-rc-table.filter-group label="{{ __('Employee') }}">
                    <select class="form-control employee_id" name="employee_id" id="employee_id">
                        <option value="">{{ __('All') }}</option>
                    </select>
                </x-rc-table.filter-group>
            </x-rc-table.filter>
        </x-rc-table>

        {{-- Summary Stat Cards --}}
        <div class="row mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="ti ti-calendar-check"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $totals['present_days'] }}<small class="text-muted fs-6">/{{ $totals['total_days'] }}</small></h3>
                            <p class="text-muted mb-0">{{ __('Present Days') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                            <i class="ti ti-clock-pause"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ floor($totals['late_minutes'] / 60) }}h {{ $totals['late_minutes'] % 60 }}m</h3>
                            <p class="text-muted mb-0">{{ __('Total Late') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                            <i class="ti ti-door-exit"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ floor($totals['early_leaving_minutes'] / 60) }}h {{ $totals['early_leaving_minutes'] % 60 }}m</h3>
                            <p class="text-muted mb-0">{{ __('Early Leaving') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                            <i class="ti ti-clock-plus"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ floor($totals['overtime_minutes'] / 60) }}h {{ $totals['overtime_minutes'] % 60 }}m</h3>
                            <p class="text-muted mb-0">{{ __('Total Overtime') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hours Summary -->
        <div class="col-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-1">{{ __('Expected Hours') }}</h6>
                            <h4 class="mb-0">{{ floor($totals['expected_minutes'] / 60) }}h {{ $totals['expected_minutes'] % 60 }}m</h4>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-1">{{ __('Actual Worked') }}</h6>
                            <h4 class="mb-0 text-success">{{ floor($totals['worked_minutes'] / 60) }}h {{ $totals['worked_minutes'] % 60 }}m</h4>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-1">{{ __('Difference') }}</h6>
                            @php
                            $diff = $totals['worked_minutes'] - $totals['expected_minutes'];
                            $diffClass = $diff >= 0 ? 'text-success' : 'text-danger';
                            $diffSign = $diff >= 0 ? '+' : '-';
                            @endphp
                            <h4 class="mb-0 {{ $diffClass }}">{{ $diffSign }}{{ floor(abs($diff) / 60) }}h {{ abs($diff) % 60 }}m</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Employee Details Accordion --}}
        <x-rc-table title="{{ __('Employee Attendance Details') }}">
            <x-rc-table.content class="p-4">
                @forelse($employeeReports as $index => $report)
                <div class="accordion employee-accordion mb-3" id="accordion{{ $index }}">
                    <div class="accordion-item border rounded">
                        <h2 class="accordion-header" id="heading{{ $index }}">
                            <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}"
                                aria-expanded="{{ $index == 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $index }}">
                                <div class="d-flex justify-content-between align-items-center w-100 pe-3">
                                    <div>
                                        <span class="fw-bold">{{ $report['employee']->name }}</span>
                                        @if($report['employee']->employee_id)
                                        <span class="text-muted ms-2">({{ $report['employee']->employee_id }})</span>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <span class="badge bg-success-subtle text-success">
                                            {{ $report['summary']['present_days'] }}/{{ $report['summary']['total_working_days'] }} {{ __('Days') }}
                                        </span>
                                        <span class="badge bg-primary-subtle text-primary">
                                            {{ floor($report['summary']['worked_minutes'] / 60) }}h {{ $report['summary']['worked_minutes'] % 60 }}m {{ __('Worked') }}
                                        </span>
                                        @if($report['summary']['late_minutes'] > 0)
                                        <span class="badge bg-danger-subtle text-danger">
                                            {{ floor($report['summary']['late_minutes'] / 60) }}h {{ $report['summary']['late_minutes'] % 60 }}m {{ __('Late') }}
                                        </span>
                                        @endif
                                        @if($report['summary']['overtime_minutes'] > 0)
                                        <span class="badge bg-info-subtle text-info">
                                            {{ floor($report['summary']['overtime_minutes'] / 60) }}h {{ $report['summary']['overtime_minutes'] % 60 }}m {{ __('OT') }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}"
                            aria-labelledby="heading{{ $index }}" data-bs-parent="#accordion{{ $index }}">
                            <div class="accordion-body p-0">
                                <div class="table-responsive">
                                    <table class="rc-table mb-0">
                                        <thead>
                                            <tr>
                                                <th class="col-date">{{ __('Date') }}</th>
                                                <th>{{ __('Day') }}</th>
                                                <th class="col-status">{{ __('Status') }}</th>
                                                <th>{{ __('Working Hours') }}</th>
                                                <th>{{ __('Clock In') }}</th>
                                                <th>{{ __('Clock Out') }}</th>
                                                <th>{{ __('Worked') }}</th>
                                                <th>{{ __('Rest') }}</th>
                                                <th>{{ __('Late') }}</th>
                                                <th>{{ __('Early') }}</th>
                                                <th>{{ __('Overtime') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($report['daily'] as $day)
                                            <tr>
                                                <td class="col-date">{{ formatDate($day['date']) }}</td>
                                                <td>{{ $day['day_name'] }}</td>
                                                <td class="col-status">
                                                    @if($day['status'] == 'Present')
                                                    <span class="badge bg-success-subtle text-success"><i class="ti ti-check me-1"></i>{{ __('Present') }}</span>
                                                    @elseif($day['status'] == 'Absent')
                                                    <span class="badge bg-danger-subtle text-danger"><i class="ti ti-x me-1"></i>{{ __('Absent') }}</span>
                                                    @elseif($day['status'] == 'Leave')
                                                    <span class="badge bg-warning-subtle text-warning"><i class="ti ti-calendar-off me-1"></i>{{ __('Leave') }}</span>
                                                    @elseif(!$day['is_working_day'])
                                                    <span class="badge bg-light text-muted">{{ __('Off Day') }}</span>
                                                    @else
                                                    <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($day['expected_start'] && $day['expected_end'])
                                                    {{ formatTime($day['expected_start']) }} -
                                                    {{ formatTime($day['expected_end']) }}
                                                    @else
                                                    -
                                                    @endif
                                                </td>
                                                <td>{{ $day['clock_in'] ? formatTime($day['clock_in']) : '-' }}</td>
                                                <td>{{ $day['clock_out'] ? formatTime($day['clock_out']) : '-' }}</td>
                                                <td>
                                                    @if($day['worked_minutes'] > 0)
                                                    {{ floor($day['worked_minutes'] / 60) }}h {{ $day['worked_minutes'] % 60 }}m
                                                    @else
                                                    -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(($day['rest_minutes'] ?? 0) > 0)
                                                    <span class="text-muted">{{ floor($day['rest_minutes'] / 60) }}h {{ $day['rest_minutes'] % 60 }}m</span>
                                                    @else
                                                    -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($day['late_minutes'] > 0)
                                                    <span class="text-danger fw-medium">{{ floor($day['late_minutes'] / 60) }}h {{ $day['late_minutes'] % 60 }}m</span>
                                                    @else
                                                    -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($day['early_leaving_minutes'] > 0)
                                                    <span class="text-warning fw-medium">{{ floor($day['early_leaving_minutes'] / 60) }}h {{ $day['early_leaving_minutes'] % 60 }}m</span>
                                                    @else
                                                    -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($day['overtime_minutes'] > 0)
                                                    <span class="text-info fw-medium">{{ floor($day['overtime_minutes'] / 60) }}h {{ $day['overtime_minutes'] % 60 }}m</span>
                                                    @else
                                                    -
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <x-rc-table.empty icon="ti ti-users" title="{{ __('No Employees Found') }}" message="{{ __('No attendance data found for the selected filters.') }}" />
                @endforelse
            </x-rc-table.content>
        </x-rc-table>
    </div>
</div>
@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        var branch_id = $('#branch_id').val();
        if (branch_id) {
            getDepartment(branch_id);
        }

        var department_id = '{{ $filterData['department_id'] }}';
        var employee_id = '{{ $filterData['employee_id'] }}';

        // Store for later use
        window.selectedDepartment = department_id;
        window.selectedEmployee = employee_id;
    });

    $(document).on('change', '#branch_id', function() {
        var branch_id = $(this).val();
        getDepartment(branch_id);
    });

    function getDepartment(branch_id) {
        if (!branch_id) {
            $('#department_id').empty().append('<option value="">{{ __('All Departments') }}</option>');
            $('#employee_id').empty().append('<option value="">{{ __('All Employees') }}</option>');
            return;
        }

        $.ajax({
            url: '{{ route('report.getdepartment') }}',
            type: 'POST',
            data: {
                "branch_id": branch_id,
                "_token": "{{ csrf_token() }}",
            },
            success: function(data) {
                $('#department_id').empty();
                $('#department_id').append('<option value="">{{ __('All Departments') }}</option>');
                $.each(data, function(key, value) {
                    var selected = (window.selectedDepartment == key) ? 'selected' : '';
                    $('#department_id').append('<option value="' + key + '" ' + selected + '>' + value + '</option>');
                });

                // Trigger employee load if department was selected
                if (window.selectedDepartment) {
                    getEmployee(window.selectedDepartment);
                }
            }
        });
    }

    $(document).on('change', '#department_id', function() {
        var department_id = $(this).val();
        getEmployee(department_id);
    });

    function getEmployee(department_id) {
        $.ajax({
            url: '{{ route('report.getemployee') }}',
            type: 'POST',
            data: {
                "department_id": department_id,
                "_token": "{{ csrf_token() }}",
            },
            success: function(data) {
                $('#employee_id').empty();
                $('#employee_id').append('<option value="">{{ __('All Employees') }}</option>');
                $.each(data, function(key, value) {
                    var selected = (window.selectedEmployee == key) ? 'selected' : '';
                    $('#employee_id').append('<option value="' + key + '" ' + selected + '>' + value + '</option>');
                });
            }
        });
    }
</script>
@endpush