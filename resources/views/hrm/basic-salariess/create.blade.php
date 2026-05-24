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
    <a href="{{ route('payroll.index', ['employee_id' => request()->get('employee_id'), 'term' => $term]) }}" class="btn btn-rc-outline btn-sm">
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
        <form id="createForm" action="{{ route('basic-salariess.store') }}" method="POST">

            @csrf
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input type="hidden" name="hourly_paid" value="0">
                                <input type="checkbox" class="form-check-input" id="hourly_paid" name="hourly_paid" value="1">
                                <label class="form-check-label" for="hourly_paid">{{ __('Hourly Paid') }}</label>
                            </div>

                            <!-- Hourly Rate Input -->
                            <div class="mb-3" id="hourlyRateBox" style="display: none;">
                                <label for="hourly_rate" class="form-label">{{ __('Hourly Rate') }}</label>
                                <input type="number" step="0.01" class="form-control" id="hourly_rate" name="hourly_rate">
                            </div>

                            <div class="form-check mb-3" id="dontAutoPayBox" style="display: none;">
                                <input type="checkbox" class="form-check-input" id="dont_auto_pay_public_holidays" name="dont_auto_pay_public_holidays" value="1">
                                <label class="form-check-label" for="dont_auto_pay_public_holidays">{{ __("Don't Auto-Pay Public Holidays") }}</label>
                            </div>

                            <p id="payslipPrompt" style="display: none; color: #3b82f6;">{{ __('You will be prompted on every payslip for Normal Hours and Overtime Hours.') }}</p>

                            <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">

                            <div class="mb-3" id="fixedSalaryBox">
                                <label for="fixed_salary" class="form-label">{{ __('Fixed Salary') }}</label>
                                <input type="number" step="0.01" class="form-control" id="fixed_salary" name="fixed_salary">
                            </div>

                            <div class="form-check mb-3" id="paidForAdditionalBox">
                                <input type="checkbox" class="form-check-input" id="paid_for_additional_hours" name="paid_for_additional_hours" value="1">
                                <label class="form-check-label" for="paid_for_additional_hours">{{ __('Paid for Additional Hours') }}</label>
                            </div>

                            <div class="form-check mb-3" id="overrideHourlyRateBox">
                                <input type="checkbox" class="form-check-input" id="override_hourly_rate" name="override_hourly_rate" value="1">
                                <label class="form-check-label" for="override_hourly_rate">{{ __('Override Calculated Hourly Rate') }}</label>
                            </div>

                            <div class="mb-3" id="rateOverrideBox" style="display: none;">
                                <label for="rate_override" class="form-label">{{ __('Rate Override') }}</label>
                                <input type="number" step="0.01" class="form-control" id="rate_override" name="rate_override">
                            </div>

                            <input type="hidden" name="term" value="{{ $term }}">

                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <a class="btn btn-rc-outline" href="{{ route('payroll.index', ['employee_id' => request()->get('employee_id'), 'term' => $term]) }}">
                                    {{ __('Cancel') }}
                                </a>
                                <button type="submit" class="btn btn-rc-primary">{{ __('Submit') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hourlyPaid = document.getElementById('hourly_paid');
        const hourlyRateBox = document.getElementById('hourlyRateBox');
        const dontAutoPayBox = document.getElementById('dontAutoPayBox');
        const payslipPrompt = document.getElementById('payslipPrompt');
        const fixedSalaryBox = document.getElementById('fixedSalaryBox');
        const paidForAdditionalBox = document.getElementById('paidForAdditionalBox');
        const overrideHourlyRateBox = document.getElementById('overrideHourlyRateBox');
        const overrideHourlyRate = document.getElementById('override_hourly_rate');
        const rateOverrideBox = document.getElementById('rateOverrideBox');

        hourlyPaid.addEventListener('change', function() {
            if (this.checked) {
                hourlyRateBox.style.display = 'block';
                dontAutoPayBox.style.display = 'block';
                payslipPrompt.style.display = 'block';
                fixedSalaryBox.style.display = 'none';
                paidForAdditionalBox.style.display = 'none';
                overrideHourlyRateBox.style.display = 'none';
            } else {
                hourlyRateBox.style.display = 'none';
                dontAutoPayBox.style.display = 'none';
                payslipPrompt.style.display = 'none';
                fixedSalaryBox.style.display = 'block';
                paidForAdditionalBox.style.display = 'block';
                overrideHourlyRateBox.style.display = 'block';
            }
        });

        overrideHourlyRate.addEventListener('change', function() {
            rateOverrideBox.style.display = this.checked ? 'block' : 'none';
        });
    });
</script>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#createForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();
            $.ajax({
                type: 'POST',
                url: '{{ route("basic-salariess.ajax-validate-store") }}',
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