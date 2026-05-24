@extends('layouts.main')

@section('page-title')
{{ __('Basic Salary') }}
@endsection

@section('page-breadcrumb')
{{ __('Employee') }},
{{ __('Payroll') }},
{{ __('Basic Salary') }}
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
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <form id="editForm" action="{{ route('basic-salariess.update', $basicSalary) }}" method="POST">
            @csrf
            @method('put')
            <div class="card">
                <div class="card-body">

                    <div class="form-check mb-3">
                        <input type="hidden" name="hourly_paid" value="0">
                        <input type="checkbox" class="form-check-input" id="hourly_paid" name="hourly_paid"
                            value="1"
                            {{ $basicSalary->hourly_paid == 1 ? 'checked' : ''}}>
                        <label class="form-check-label" for="hourly_paid">Hourly Paid</label>
                    </div>

                    <!-- Hourly Rate Input original-->
                    <div class="mb-3" id="hourlyRateBox" style="{{ $basicSalary->hourly_paid == 0 ? 'display: none;' : ''}}">
                        <label for="hourly_rate" class="form-label">Hourly Rate</label>
                        <input type="number" step="0.01" class="form-control" id="hourly_rate" name="hourly_rate" value="{{ $basicSalary->hourly_rate }}">
                    </div>


                    <!-- Don't Auto-Pay Public Holidays -->
                    <div class="form-check mb-3" id="dontAutoPayBox" style="{{ $basicSalary->hourly_paid == 0 ? 'display: none;' : ''}}">
                        <input type="checkbox" class="form-check-input" id="dont_auto_pay_public_holidays"
                            name="dont_auto_pay_public_holidays"
                            value="{{ old('dont_auto_pay_public_holidays',$basicSalary->dont_auto_pay_public_holidays) }}"
                            {{ $basicSalary->dont_auto_pay_public_holidays == 1 ? 'checked' : ''}}>
                        <label class="form-check-label" for="dont_auto_pay_public_holidays">Don't Auto-Pay Public
                            Holidays
                        </label>
                    </div>

                    <p id="payslipPrompt" style="display: none; color: blue;">
                        You will be prompted on every payslip for NormalHours and Overtime Hours.
                    </p>

                    <div class="mb-3" id="fixedSalaryBox" style="{{ $basicSalary->hourly_paid == 1 ? 'display: none;' : ''}}">
                        <label for="fixed_salary" class="form-label">Fixed Salary</label>
                        <input type="number" step="0.01" class="form-control" id="fixed_salary" name="fixed_salary"
                            value="{{ old('fixed_salary', $basicSalary->fixed_salary) }}">
                    </div>

                    <!-- Paid for Additional Hours Checkbox -->
                    <div class="form-check mb-3" id="paidForAdditionalBox" style="{{ $basicSalary->hourly_paid == 1 ? 'display: none;' : ''}}">
                        <input type="checkbox" class="form-check-input" id="paid_for_additional_hours"
                            name="paid_for_additional_hours"
                            value="{{ old('paid_for_additional_hours',$basicSalary->paid_for_additional_hours) }}"
                            {{ $basicSalary->paid_for_additional_hours == 1 ? 'checked' : ''}}>
                        <label class="form-check-label" for="paid_for_additional_hours">Paid for Additional Hours</label>
                    </div>

                    <!-- Override Hourly Rate Checkbox -->
                    <div class="form-check mb-3" id="overrideHourlyRateBox" style="{{ $basicSalary->hourly_paid == 1 ? 'display: none;' : ''}}">
                        <input type="checkbox" class="form-check-input" id="override_hourly_rate" name="override_hourly_rate"
                            value="{{ old('override_hourly_rate',$basicSalary->override_hourly_rate) }}"
                            {{ $basicSalary->override_hourly_rate == 1 ? 'checked' : ''}}>
                        <label class="form-check-label" for="override_hourly_rate">Override Calculated Hourly Rate</label>
                    </div>

                    <!-- Rate Override Input -->
                    <div class="mb-3" id="rateOverrideBox" style="{{ $basicSalary->override_hourly_rate == 0 ? 'display: none;' : ''}}">
                        <label for="rate_override" class="form-label">Rate Override</label>
                        <input type="number" step="0.01" class="form-control" id="rate_override" name="rate_override" value="{{ $basicSalary->rate_override }}">
                    </div>
                    <input type="hidden" name="term" value="{{ request('term') }}">
                    
                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <a class="btn btn-rc-outline"
                        href="{{ route('payroll.index', ['employee_id' => $basicSalary->employee_id]) }}">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn btn-rc-primary" id="">update</button>
                            </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#editForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();
            $.ajax({
                type: 'POST',
                url: '{{ route("basic-salariess.ajax-validate-update", $basicSalary->id) }}',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        form.off('submit');
                        form[0].submit();
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    $('.error-text').remove();
                    $('input, select, textarea').removeClass('is-invalid');
                    $.each(errors, function(field, messages) {
                        var input = $('[name="' + field + '"]');
                        input.addClass('is-invalid');
                        input.after('<span class="text-danger error-text">' + messages[0] + '</span>');
                    });
                }
            });
        });
    });
</script>
@endpush