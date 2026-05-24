@extends('layouts.main')
@section('page-title')
{{ __('Bulk Attendance') }}
@endsection
@section('page-breadcrumb')
{{ __('Bulk Attendance') }}
@endsection
@php
$company_settings = getCompanyAllSetting();
@endphp
@push('css')
<style>
    .timepicker {
        padding: 0 0.5rem;
        font-size: 12px;
    }
</style>
@endpush
@section('content')
<div class="row">
    <div class="col-sm-12">
        <x-rc-table>
            <x-rc-table.filter action="{{ route('attendance.bulkattendance') }}" method="GET" id="bulkattendance_filter">
                <x-rc-table.filter-group label="{{ __('Date') }}">
                    {!! Form::date('date', isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'), [
                    'class' => 'form-control',
                    'max'=>date('Y-m-d')
                    ]) !!}
                </x-rc-table.filter-group>
                <x-rc-table.filter-group label="{{ !empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch') }}">
                    {{ Form::select('branch', $branch, isset($_GET['branch']) ? $_GET['branch'] : '', ['class' => 'form-control', 'id' => 'branch']) }}
                </x-rc-table.filter-group>
                <x-rc-table.filter-group label="{{ !empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department') }}">
                    {{ Form::select('department', $department, isset($_GET['department']) ? $_GET['department'] : '', ['class' => 'form-control', 'id' => 'department']) }}
                </x-rc-table.filter-group>
            </x-rc-table.filter>

            {{ Form::open(['route' => ['attendance.bulkattendance'], 'method' => 'post', 'id' => 'bulk-attendance-form']) }}
            <input type="hidden" value="{{ isset($_GET['date']) ? $_GET['date'] : date('Y-m-d') }}" name="date">
            <input type="hidden" value="{{ isset($_GET['branch']) ? $_GET['branch'] : '' }}" name="branch">
            <input type="hidden" value="{{ isset($_GET['department']) ? $_GET['department'] : '' }}" name="department">

            <x-rc-table.content>
                <table class="rc-table">
                    <thead>
                        <tr>
                            <th class="col-id">{{ __('Employee Id') }}</th>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ !empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch') }}</th>
                            <th>{{ !empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department') }}</th>
                            <th>
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="form-group my-auto">
                                        <div class="custom-control d-flex align-items-center">
                                            <input class="form-check-input" type="checkbox" name="present_all"
                                                id="present_all" {{ old('remember') ? 'checked' : '' }}>
                                            <label class="custom-control-label px-2" for="present_all">
                                                {{ __('Attendance') }}</label>
                                        </div>
                                    </div>
                                    @if(count($employees) > 0)
                                    <button type="submit" class="btn btn-sm btn-rc-primary">
                                        <i class="ti ti-check"></i> {{ __('Update') }}
                                    </button>
                                    @endif
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                        @php
                        $attendance = App\Models\Hrm\Attendance::where('employee_id', $employee->id)->where('date', isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'))->first();
                        @endphp
                        <tr>
                            <td class="Id">
                                <input type="hidden" value="{{ $employee->id }}" name="employee_id[]">
                                @if (!empty($employee->employee_id))
                                @permission('employee show')
                                <a class=""
                                    href="{{ route('employees.show', $employee->id) }}">{{ $employee->employee_id }}</a>
                                @else
                                <a class="btn btn-outline-primary">{{ $employee->employee_id }}</a>
                                @endpermission
                                @endif
                            </td>
                            <td>{{ $employee->full_name }}</td>
                            <td>
                                {{ !empty($employee->department) && !empty($employee->department->branch) ? $employee->department->branch->name : '--' }}
                            </td>
                            <td>
                                {{ !empty($employee->department) ? $employee->department->name : '--' }}
                            </td>
                            <td width="30%">
                                <div class="row">
                                    <div class="col-md-1 d-flex align-items-center">
                                        <div>
                                            <div class="custom-control custom-checkbox">
                                                <input class="form-check-input present" type="checkbox"
                                                    name="present-{{ $employee->id }}"
                                                    id="present{{ $employee->id }}"
                                                    {{ !empty($attendance) && $attendance->status == 'Present' ? 'checked' : '' }}>
                                                <label class="custom-control-label"
                                                    for="present{{ $employee->id }}"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="col-md-11 present_check_in {{ empty($attendance) ? 'd-none' : '' }} ">
                                        <div class="row">
                                            <label class="col-md-1 control-label d-flex align-items-center">{{ __('In') }}</label>
                                            <div class="col-md-5">
                                                <input type="time" class="form-control timepicker"
                                                    name="in-{{ $employee->id }}"
                                                    value="{{ !empty($attendance) && $attendance->clock_in != '00:00:00' ? substr($attendance->clock_in, 0, 5) : ($company_settings['company_start_time'] ?? '09:00') }}">
                                            </div>

                                            <label for="inputValue"
                                                class="col-md-1 control-label d-flex align-items-center">{{ __('Out') }}</label>
                                            <div class="col-md-5">
                                                <input type="time" class="form-control timepicker"
                                                    name="out-{{ $employee->id }}"
                                                    value="{{ !empty($attendance) && $attendance->clock_out != '00:00:00' ? substr($attendance->clock_out, 0, 5) : ($company_settings['company_end_time'] ?? '17:00') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>

                        </tr>
                        @empty
                        <x-rc-table.empty :asRow="true" :colspan="5" icon="ti ti-users-off" title="{{ __('No Employees Found') }}" message="{{ __('No employees found for the selected filters.') }}" />
                        @endforelse
                    </tbody>
                </table>
            </x-rc-table.content>
            {{ Form::close() }}

            <x-rc-table.footer :paginator="$employees" />
        </x-rc-table>
    </div>
</div>
@endsection
@push('scripts')
<script>
        $(document).ready(function() {
            if ($('.daterangepicker').length > 0) {
                $('.daterangepicker').daterangepicker({
                    format: 'yyyy-mm-dd',
                    locale: {
                        format: 'YYYY-MM-DD'
                    },
                });
            }
        });
    </script>
    <script>
        $('#present_all').click(function(event) {
            if (this.checked) {
                $('.present').each(function() {
                    this.checked = true;
                });

                $('.present_check_in').removeClass('d-none');
                $('.present_check_in').addClass('d-block');

            } else {
                $('.present').each(function() {
                    this.checked = false;
                });
                $('.present_check_in').removeClass('d-block');
                $('.present_check_in').addClass('d-none');
            }
        });
        $('.present').click(function(event) {
            var div = $(this).parent().parent().parent().parent().find('.present_check_in');

            if (this.checked) {
                div.removeClass('d-none');
                div.addClass('d-block');

            } else {
                div.removeClass('d-block');
                div.addClass('d-none');
            }
        });
    </script>
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
                    $('#department').append(
                    '<option value="" disabled>{{ __('Select Department') }}</option>');

                    $.each(data, function(key, value) {
                        $('#department').append('<option value="' + key + '">' + value + '</option>');
                    });
                    $('#department').val('');
                }
            });
        }
    </script>
@endpush