@extends('layouts.main')

@section('page-title')
{{ __('Basic Salary - Hourly Pay') }}
@endsection

@section('page-breadcrumb')
{{ __('Employee') }},
{{ __('Payroll') }},
{{ __('Basic Salary') }},
{{ __('Hourly Pay') }}
@endsection

@section('page-action')
<div>
    <a href="{{ route('payroll.index', ['employee_id' => $basicSalary->employee_id, 'term' => $term]) }}" class="btn btn-rc-outline btn-sm">
        <i class="ti ti-arrow-left"></i> {{ __('Back to Payroll') }}
    </a>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
<style>
    .attendance-hours-card {
        border: 2px solid #28a745;
        border-radius: 8px;
        background: linear-gradient(135deg, #f8fff8 0%, #e8f5e9 100%);
    }

    .attendance-hours-card.not-available {
        border-color: #ffc107;
        background: linear-gradient(135deg, #fffef5 0%, #fff8e1 100%);
    }

    .hours-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #28a745;
    }

    .hours-label {
        font-size: 0.85rem;
        color: #6c757d;
        text-transform: uppercase;
    }

    .use-attendance-btn {
        transition: all 0.3s ease;
    }

    .use-attendance-btn:hover {
        transform: scale(1.02);
    }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

<div class="row">
    <div class="col-sm-12">
        <!-- Attendance Based Hours Section -->
        @if(isset($attendanceHours))
        <div class="card mb-4 attendance-hours-card {{ !$attendanceHours['available'] ? 'not-available' : '' }}">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">
                    <i class="ti ti-clock-record me-2"></i>
                    {{ __('Attendance Based Hours') }}
                </h5>
                @if($attendanceHours['available'])
                <span class="badge bg-success">{{ __('Available') }}</span>
                @else
                <span class="badge bg-warning text-dark">{{ __('Not Available') }}</span>
                @endif
            </div>
            <div class="card-body">
                @if($attendanceHours['available'])
                <div class="row text-center mb-3">
                    <div class="col-md-4">
                        <div class="hours-value">{{ number_format($attendanceHours['normal_hours'], 2) }}</div>
                        <div class="hours-label">{{ __('Normal Hours') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="hours-value" style="color: #17a2b8;">{{ number_format($attendanceHours['overtime_hours'], 2) }}</div>
                        <div class="hours-label">{{ __('Overtime Hours') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="hours-value" style="color: #6c757d;">{{ number_format($attendanceHours['total_hours'], 2) }}</div>
                        <div class="hours-label">{{ __('Total Hours') }}</div>
                    </div>
                </div>
                <div class="text-center mb-3">
                    <small class="text-muted">
                        <i class="ti ti-calendar-stats me-1"></i>
                        {{ __('Period') }}: {{ $attendanceHours['period'] }}
                        &nbsp;|&nbsp;
                        <i class="ti ti-list-check me-1"></i>
                        {{ $attendanceHours['message'] }}
                    </small>
                </div>
                <div class="text-center">
                    <button type="button" class="btn btn-rc-primary use-attendance-btn" id="useAttendanceHours">
                        <i class="ti ti-check me-1"></i>
                        {{ __('Use Attendance Hours') }}
                    </button>
                </div>
                @else
                <div class="text-center py-3">
                    <i class="ti ti-info-circle text-warning" style="font-size: 2rem;"></i>
                    <p class="mt-2 mb-0">{{ $attendanceHours['message'] }}</p>
                    <small class="text-muted">{{ __('Please enter hours manually below.') }}</small>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Manual Entry Form -->
        <div class="row">
            <div class="col-sm-12">
                <form action="{{ route('basic-salariess.hourlyPay.store', $basicSalary->id) }}" method="POST">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="ti ti-edit me-2"></i>
                                {{ __('Manual Entry') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3" id="hourlyRateBox">
                                    <label for="normal_hours" class="form-label">{{ __('Normal Hours') }}</label>
                                    <input type="number" step="0.01" class="form-control" id="normal_hours" name="normal_hours"
                                        value="{{ old('normal_hours', $basicSalary->normal_hour_value) }}">
                                    <small class="text-muted">{{ __('Total normal working hours for the period') }}</small>
                                </div>
                                <div class="col-md-6 mb-3" id="fixedSalaryBox">
                                    <label for="ot_hours" class="form-label">{{ __('Overtime Hours') }}</label>
                                    <input type="number" step="0.01" class="form-control" id="ot_hours" name="ot_hours"
                                        value="{{ old('ot_hours', $basicSalary->ot_hour_value) }}">
                                    <small class="text-muted">{{ __('Total overtime hours for the period') }}</small>
                                </div>
                            </div>
                            <input type="hidden" name="term" value="{{ $term }}">

                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <a class="btn btn-rc-outline" href="{{ route('payroll.index', ['employee_id' => $basicSalary->employee_id, 'term' => $term]) }}">
                                    {{ __('Cancel') }}
                                </a>
                                <button type="submit" class="btn btn-rc-primary">
                                    <i class="ti ti-device-floppy me-1"></i>
                                    {{ __('Submit') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle "Use Attendance Hours" button click
    $('#useAttendanceHours').on('click', function() {
        @if(isset($attendanceHours) && $attendanceHours['available'])
        var normalHours = {{ $attendanceHours['normal_hours'] }};
        var overtimeHours = {{ $attendanceHours['overtime_hours'] }};
        
        $('#normal_hours').val(normalHours.toFixed(2));
        $('#ot_hours').val(overtimeHours.toFixed(2));
        
        // Visual feedback
        $(this).removeClass('btn-rc-primary').addClass('btn-secondary')
               .html('<i class="ti ti-check-all me-1"></i> {{ __('Hours Applied!') }}');
        
        // Highlight the fields
        $('#normal_hours, #ot_hours').css('background-color', '#e8f5e9');
        setTimeout(function() {
            $('#normal_hours, #ot_hours').css('background-color', '');
        }, 2000);
        @endif
    });
});
</script>
@endpush