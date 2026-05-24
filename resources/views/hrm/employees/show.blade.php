@extends('layouts.main')
@section('page-title')
{{ $employee->first_name }} {{ $employee->last_name }}
@endsection
@section('page-breadcrumb')
{{ __('Employees') }},{{ $employee->first_name }} {{ $employee->last_name }}
@endsection

@section('page-action')
<div>
    @permission('employee edit')
    <a href="{{ route('employees.modify', $employee->id) }}" class="btn btn-sm btn-rc-icon" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
        <i class="ti ti-pencil text-white"></i>
    </a>
    @endpermission
    <a href="{{ route('employees.grid') }}" class="btn btn-sm btn-rc-icon" data-bs-toggle="tooltip" title="{{ __('Back') }}">
        <i class="ti ti-arrow-left text-white"></i>
    </a>
</div>
@endsection

@section('content')
<div class="row">
    {{-- Employee Profile Header --}}
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-4 flex-wrap">
                    <div class="employee-avatar-wrapper" style="width: 80px; height: 80px; flex-shrink: 0;">
                        @php
                        $initials = strtoupper(substr($employee->first_name ?? '', 0, 1) . substr($employee->last_name ?? '', 0, 1));
                        @endphp
                        @if (!empty($employee->profile_pic_path))
                        <img src="{{ asset('uploads/' . $employee->profile_pic_path) }}" alt="{{ $employee->first_name }}" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                        @else
                        <div class="employee-avatar-placeholder" style="width: 80px; height: 80px; font-size: 28px;">{{ $initials }}</div>
                        @endif
                    </div>
                    <div>
                        <h4 class="mb-1" style="color: var(--rc-gray-800);">
                            {{ $employee->salutation ? $employee->salutation . ' ' : '' }}{{ $employee->first_name }} {{ $employee->last_name }}
                        </h4>
                        @if ($employee->designation)
                        <p class="text-muted mb-1">{{ $employee->designation->name }}</p>
                        @endif
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            @if ($employee->employee_id)
                            <span class="employee-grid-id">{{ App\Models\Hrm\Employee::employeeIdFormat($employee->employee_id) }}</span>
                            @endif
                            <span class="badge {{ $employee->status == 'Active' ? 'bg-success' : 'bg-danger' }} rounded-pill">{{ $employee->status ?? 'Active' }}</span>
                            <span class="badge {{ $employee->ess_enabled ? 'bg-info' : 'bg-secondary' }} rounded-pill">
                                {{ $employee->ess_enabled ? __('ESS Enabled') : __('ESS Disabled') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="col-md-12 d-flex align-items-center justify-content-between mb-2">
            <ul class="nav nav-pills nav-fill cust-nav information-tab" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ request('tab') != 'payslip' && request('tab') != 'company' ? 'active' : '' }}" id="personal-details" data-bs-toggle="pill"
                        data-bs-target="#personal-details-tab" type="button">{{ __('Personal Details') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ request('tab') == 'company' ? 'active' : '' }}" id="company" data-bs-toggle="pill" data-bs-target="#company-tab"
                        type="button">{{ __('Company Details') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ request('tab') == 'payslip' ? 'active' : '' }}" id="payslip" data-bs-toggle="pill" data-bs-target="#payslip-tab"
                        type="button">{{ __('Payslip Details') }}</button>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="tab-content" id="pills-tabContent">

            {{-- ============ TAB 1: PERSONAL DETAILS ============ --}}
            <div class="tab-pane fade {{ request('tab') != 'payslip' && request('tab') != 'company' ? 'show active' : '' }}" id="personal-details-tab" role="tabpanel">
                <div class="row">
                    {{-- Personal Info --}}
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="ti ti-user me-2"></i>{{ __('Personal Information') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('First Name') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->first_name ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Last Name') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->last_name ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Email') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->email ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Phone') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->phone_number ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Date of Birth') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->date_of_birth ? formatDate($employee->date_of_birth) : '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Gender') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->gender ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Identification Type') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->identification_type ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('ID Number') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->id_number ?? '-' }}</p>
                                        </div>
                                    </div>
                                    @if ($employee->identification_type == 'Passport')
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Passport Country') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->passport_country ?? '-' }}</p>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Tax Reference Number') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->tax_reference_number ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Emergency Contact --}}
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="ti ti-emergency-bed me-2"></i>{{ __('Emergency Contact') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Contact Name') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->emergency_contact_name ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Contact Phone') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->emergency_contact_phone ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Address Details --}}
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="ti ti-map-pin me-2"></i>{{ __('Address Details') }}</h5>
                            </div>
                            <div class="card-body">
                                <h6 class="mb-3 text-primary">{{ __('Permanent Address') }}</h6>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Street') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->street ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Flat / Unit No') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->flat_no ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('City') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->city ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('State / Province') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->state ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Postal Code') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->pincode ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Country') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->country ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>

                                @if ($employee->temp_street || $employee->temp_city || $employee->temp_state)
                                <hr>
                                <h6 class="mb-3 text-primary">{{ __('Temporary Address') }}</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Street') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->temp_street ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Flat / Unit No') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->temp_flat_no ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('City') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->temp_city ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('State / Province') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->temp_state ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Postal Code') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->temp_pincode ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Country') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->temp_country ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ TAB 2: COMPANY DETAILS ============ --}}
            <div class="tab-pane fade {{ request('tab') == 'company' ? 'show active' : '' }}" id="company-tab" role="tabpanel">
                <div class="row">
                    {{-- Company Info --}}
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="ti ti-building me-2"></i>{{ __('Company Details') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Employee ID') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->employee_id ? App\Models\Hrm\Employee::employeeIdFormat($employee->employee_id) : '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Branch') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->branch->name ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Department') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->department->name ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Designation') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->designation->name ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Date of Appointment') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->date_of_appointment ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Pay Frequency') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->payFrequency->pay_frequency ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Status') }}</strong>
                                            <p class="mb-0">
                                                <span class="badge {{ $employee->status == 'Active' ? 'bg-success' : 'bg-danger' }} rounded-pill">{{ $employee->status ?? '-' }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Banking Details --}}
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="ti ti-building-bank me-2"></i>{{ __('Banking Details') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Bank Name') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->bank ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Account Number') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->account_number ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Account Type') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->account_type ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Branch Name') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->branch_name ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Branch Code') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->branch_code ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Account Holder Relationship') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->holder_relationship ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Attendance & Working Hours --}}
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="ti ti-clock me-2"></i>{{ __('Attendance & Working Hours') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Attendance Tracking') }}</strong>
                                            <p class="mb-0">
                                                @if ($employee->attendance_enabled)
                                                <span class="badge bg-success rounded-pill">{{ __('Enabled') }}</span>
                                                @else
                                                <span class="badge bg-secondary rounded-pill">{{ __('Disabled') }}</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Working Hours') }}</strong>
                                            <p class="mb-0">
                                                @if ($employee->use_custom_working_hours)
                                                <span class="badge bg-info rounded-pill">{{ __('Custom') }}</span>
                                                @else
                                                <span class="badge bg-secondary rounded-pill">{{ __('Branch Default') }}</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info font-style mb-3">
                                            <strong>{{ __('Branch') }}</strong>
                                            <p class="text-muted mb-0">{{ $employee->branch->name ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>

                                @if ($employee->use_custom_working_hours && $employee->workingHours->count() > 0)
                                <x-rc-table title="{{ __('Weekly Schedule') }}" titleIcon="ti ti-calendar-week" class="mt-3">
                                    <x-rc-table.content>
                                        <table class="rc-table">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Day') }}</th>
                                                    <th>{{ __('Working') }}</th>
                                                    <th>{{ __('Start') }}</th>
                                                    <th>{{ __('End') }}</th>
                                                    <th>{{ __('Lunch') }}</th>
                                                    <th>{{ __('Hours') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                $dayOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                                $sortedHours = $employee->workingHours->sortBy(function($wh) use ($dayOrder) {
                                                return array_search(strtolower($wh->day), $dayOrder);
                                                });
                                                $totalWeeklyMinutes = 0;
                                                @endphp
                                                @foreach ($sortedHours as $wh)
                                                @php
                                                $netMinutes = 0;
                                                if ($wh->is_working_day && $wh->start_time && $wh->end_time) {
                                                $workMin = \Carbon\Carbon::parse($wh->start_time)->diffInMinutes(\Carbon\Carbon::parse($wh->end_time));
                                                $lunchMin = ($wh->lunch_start_time && $wh->lunch_end_time)
                                                ? \Carbon\Carbon::parse($wh->lunch_start_time)->diffInMinutes(\Carbon\Carbon::parse($wh->lunch_end_time))
                                                : 0;
                                                $netMinutes = $workMin - $lunchMin;
                                                $totalWeeklyMinutes += $netMinutes;
                                                }
                                                @endphp
                                                <tr>
                                                    <td class="text-primary-cell"><strong>{{ ucfirst($wh->day) }}</strong></td>
                                                    <td class="text-secondary-cell">
                                                        @if ($wh->is_working_day)
                                                        <span class="badge bg-success rounded-pill">{{ __('Yes') }}</span>
                                                        @else
                                                        <span class="badge bg-secondary rounded-pill">{{ __('No') }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-secondary-cell">{{ $wh->is_working_day && $wh->start_time ? formatTime($wh->start_time) : '-' }}</td>
                                                    <td class="text-secondary-cell">{{ $wh->is_working_day && $wh->end_time ? formatTime($wh->end_time) : '-' }}</td>
                                                    <td class="text-secondary-cell">
                                                        @if ($wh->is_working_day && $wh->lunch_start_time && $wh->lunch_end_time)
                                                        {{ formatTime($wh->lunch_start_time) }} - {{ formatTime($wh->lunch_end_time) }}
                                                        @else
                                                        -
                                                        @endif
                                                    </td>
                                                    <td class="text-secondary-cell">{{ $wh->is_working_day ? number_format($netMinutes / 60, 1) . 'h' : '-' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr style="background-color: var(--rc-gray-100);">
                                                    <td colspan="5" class="text-primary-cell"><strong>{{ __('Total Weekly Hours') }}</strong></td>
                                                    <td class="text-primary-cell"><strong>{{ number_format($totalWeeklyMinutes / 60, 1) }}h</strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </x-rc-table.content>
                                </x-rc-table>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ TAB 3: PAYSLIP DETAILS ============ --}}
            <div class="tab-pane fade {{ request('tab') == 'payslip' ? 'show active' : '' }}" id="payslip-tab" role="tabpanel">
                <div class="row">
                    <div class="col-sm-12">
                        <x-rc-table title="{{ __('Payslip Details') }}" titleIcon="ti ti-file-invoice">
                            <x-rc-table.content>
                                <table class="rc-table" id="payslip-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Period') }}</th>
                                            <th>{{ __('Basic Salary') }}</th>
                                            <th>{{ __('Net Salary') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th class="text-end">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($payslips as $payslip)
                                        @php
                                        $periodFormatted = '-';
                                        if (!empty($payslip->salary_month)) {
                                        try {
                                        $periodFormatted = formatMonthYear($payslip->salary_month);
                                        } catch (\Exception $e) {
                                        $periodFormatted = $payslip->salary_month;
                                        }
                                        }
                                        @endphp
                                        <tr>
                                            <td class="text-primary-cell">{{ $periodFormatted }}</td>
                                            <td class="text-secondary-cell">{{ $payslip->basic_salary ? number_format($payslip->basic_salary, 2) : '-' }}</td>
                                            <td class="text-secondary-cell">{{ $payslip->net_payble ? number_format($payslip->net_payble, 2) : '-' }}</td>
                                            <td>
                                                @if ($payslip->status == 2)
                                                <span class="badge bg-success rounded-pill">{{ __('Paid') }}</span>
                                                @elseif ($payslip->status == 1)
                                                <span class="badge bg-info rounded-pill">{{ __('Finalized') }}</span>
                                                @else
                                                <span class="badge bg-warning rounded-pill">{{ __('Draft') }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('payslip.preview', [$payslip->employee_id, $payslip->salary_month]) }}"
                                                    class="rc-table-action" target="_blank"
                                                    data-bs-toggle="tooltip" title="{{ __('Download PDF') }}">
                                                    <i class="ti ti-download"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <x-rc-table.empty
                                            icon="ti ti-file-off"
                                            title="{{ __('No Payslips Found') }}"
                                            message="{{ __('There are no payslips generated for this employee yet.') }}"
                                            :colspan="5"
                                            :asRow="true" />
                                        @endforelse
                                    </tbody>
                                </table>
                            </x-rc-table.content>
                            <x-rc-table.footer :paginator="$payslips" />
                        </x-rc-table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection