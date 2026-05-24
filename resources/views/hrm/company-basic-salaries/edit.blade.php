@extends('layouts.main')
@section('page-title')
    {{ __('Basic Pay') }}
@endsection
@section('page-breadcrumb')
    {{ __('Payroll') }},
    {{ __('Basic Pay') }}
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp

@section('content')
<form id="editForm" action="{{ route('company-basic-salaries.update',$companybasicSalary->id) }}" class="mt-3" method="post">
    @csrf
    @method('put')
    <div class="" style="margin-top:40px">
        <div class="card py-4 px-4 ">
            <div class="row py-4 align-items-center">
                <div class="col-xxl:4 col-xl:4 col-md-4">
                    <label class="form-label require">{{ __('Hourly Rate') }}</label>
                    <input type="number" name="hourly_rate"
                        class="form-control @error('hourly_rate') is-invalid @enderror"
                        value="{{ old('hourly_rate',$companybasicSalary->hourly_rate) }}">
                    @error('hourly_rate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <!--<div class="col-xxl:4 col-xl:4 col-md-4">
                    <input class="form-control" />
                </div>-->
            </div>

            <div class="row py-2 mt-6">
                <div class="col-xxl-4 col-xl-4 col-md-4 d-flex align-items-center gap-6">
                    <input type="checkbox" class="form-check-input" id="dont_auto_pay_holidays" name="dont_auto_pay_holidays" value="Don't auto-pay public holidays" />
                    <label for="dont_auto_pay_holidays" class="fs-6" style="padding-left:6px"> Don't auto-pay public holidays </label>
                </div>
            </div>
            <div class="row">
                <div class="col-xxl-4 col-xl-4 col-md-4 d-flex align-items-center gap-6">
                    <input type="checkbox" class="form-check-input" id="enable_shifts" name="enable_shifts" value="Enable input of number of shifts"
                        onclick="toggleRow('publicRow')" />
                    <label for="enable_shifts" class="fs-6" style="padding-left:6px"> Enable input of number of shifts </label>
                </div>
            </div>
            <div class="row" id="publicRow" style="display: none; margin-top: 10px;">
                <div class="col-xxl-8 col-xl-8 col-md-8">
                    <p>You will be prompted on every payslip for the Shifts Worked</p>
                </div>
            </div>
        </div>

        <div class="row py-2">
            <div class="col-xxl:6 col-xl:-6 col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="" style="color: #1d64ae;">
                            Public Holiday Pay
                        </h4>
                    </div>
                    <div class="card-body">
                        <h6 class="pb-4" style="color: #b1376a;">
                            If Employee Normally Works
                        </h6>
                        <div class="row align-items-center">
                            <div class="col-xxl:8 col-xl:8 col-md-8 ">
                                <!--<h6 class="" style="font-weight:400">
                                    Minimum Pay
                                </h6>-->
                                <label class="form-label require">{{ __(' Minimum Pay') }}</label>
                                <select name="employee_minimum_pay" class="form-control @error('employee_minimum_pay') is-invalid @enderror">
                                    <option value="">Select SDL Registration</option>
                                    <option value=" none" {{ old('employee_minimum_pay',$companybasicSalary->employee_minimum_pay) == 'none' ? 'selected' : '' }}> none</option>
                                    <option value="  Normal Day's Wage"  {{ old('employee_minimum_pay',$companybasicSalary->employee_minimum_pay) == 'Normal Days Wage' ? 'selected' : '' }}>  Normal Day's Wage</option>
                                    <option value=" Double normal Day's Wage" {{ old('employee_minimum_pay',$companybasicSalary->employee_minimum_pay) == ' Double normal Days Wage' ? 'selected' : '' }}> Double normal Day's Wage
                                    </option>
                                    
                                </select>
                                @error('employee_minimum_pay')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                           <!-- <div class="col-xxl:8 col-xl:8 col-md-8 ">
                                <select class="form-select">
                                    <option class="">
                                        none
                                    </option>
                                    <option class="">
                                        Normal Day's Wage
                                    </option>
                                    <option class="">
                                        Double normal Day's Wage
                                    </option>
                                </select>
                            </div>-->
                        </div>
                        <div class="row align-items-center py-4">
                            <div class="col-xxl:3 col-xl:3 col-md-3">
                                <h6 class="" style="font-weight:400">
                                    Fixed Component
                                </h6>
                            </div>
                            <div class="col-xxl:8 col-xl:8 col-md-8 ">
                            
                           <!-- <label class="form-label require">{{ __(' Minimum Pay') }}</label>-->
                            <select name="employee_fixed_component" class="form-control @error('employee_fixed_component') is-invalid @enderror">
                                <option value="">Select SDL Registration</option>
                                <option value=" none" {{ old('employee_fixed_component',$companybasicSalary->employee_fixed_component) == ' none' ? 'selected' : '' }}> none</option>
                                <option value="  Normal Day's Wage" {{ old('employee_fixed_component',$companybasicSalary->employee_fixed_component) == '  normal Days Wage' ? 'selected' : '' }}>  Normal Day's Wage</option>
                               
                                </option>
                                
                            </select>
                            @error('employee_fixed_component')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            </div>
                        </div>
                        <h6 class="pb-4" style="color:#b1376a">
                            If Employee Normally Off
                        </h6>
                        <div class="row align-items-center">
                            <div class="col-xxl:3 col-xl:3 col-md-3">
                                <h6 class="" style="font-weight:400">
                                    Minimum Pay
                                </h6>
                            </div>
                            <div class="col-xxl:8 col-xl:8 col-md-8 ">
                               <!-- <label class="form-label require">{{ __(' Minimum Pay') }}</label>-->
                            <select name="work_minimum_pay" class="form-control @error('work_minimum_pay') is-invalid @enderror">
                                <option value="">Select SDL Registration</option>
                                <option value=" none" {{ old('work_minimum_pay',$companybasicSalary->work_minimum_pay) == 'none' ? 'selected' : '' }}> none</option>
                                <option value="  Normal Day's Wage" {{ old('work_minimum_pay',$companybasicSalary->work_minimum_pay) == '  normal Days Wage' ? 'selected' : '' }}>  Normal Day's Wage</option>
                               
                                </option>
                                
                            </select>
                            @error('work_minimum_pay')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            </div>
                        </div>
                        <div class="row align-items-center py-4">
                            <div class="col-xxl:3 col-xl:3 col-md-3">
                                <h6 class="" style="font-weight:400">
                                    Fixed Component
                                </h6>
                            </div>
                            <div class="col-xxl:8 col-xl:8 col-md-8 ">
                                <select name="work_fixed_component" class="form-control @error('work_fixed_component') is-invalid @enderror">
                                    <option value="">Select SDL Registration</option>
                                    <option value="none" {{ old('work_fixed_component',$companybasicSalary->work_fixed_component) == 'none' ? 'selected' : '' }}> none</option>
                                    <option value="Normal Day's Wage" {{ old('work_fixed_component',$companybasicSalary->work_fixed_component) == 'Normal Days Wage' ? 'selected' : '' }}>  Normal Day's Wage</option>
                                   
                                    </option>
                                    
                                </select>
                                @error('work_fixed_component')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row py-4">
                            <div class="col-xxl-8 col-xl-8 col-md-8 d-flex align-items-center gap-6">
                                <input type="checkbox" class="form-check-input" id="override_holiday_pay_rates" name="override_holiday_pay_rates" value="Override Holiday Pay Rates"
                                    onclick="toggleRow('RatesRow')" />
                                <label for="override_holiday_pay_rates" class="fs-6" style="padding-left:6px"> Override Holiday Pay Rates
                                </label>
                            </div>
                        </div>
                        <div class="row" id="RatesRow" style="display: none; margin-top: 10px;">
                            <div class="row align-items-center">
                                <div class="col-xxl:3 col-xl:3 col-md-3">
                                    <h6 class="" style="font-weight:400">
                                        Holiday normal multiplier
                                    </h6>
                                </div>
                                <div class="col-xxl:8 col-xl:8 col-md-8 ">
                                    <input type="number" name="holiday_normal_multiplier"
                                    class="form-control @error('holiday_normal_multiplier') is-invalid @enderror"
                                    value="{{ old('holiday_normal_multiplier', $companybasicSalary->holiday_normal_multiplier) }}">
                                @error('holiday_normal_multiplier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                </div>
                            </div>
                            <div class="row align-items-center py-4">
                                <div class="col-xxl:3 col-xl:3 col-md-3">
                                    <h6 class="" style="font-weight:400">
                                        Holiday overtime multiplier
                                    </h6>
                                </div>
                                <div class="col-xxl:8 col-xl:8 col-md-8 ">
                                    <input type="number" name="holiday_overtime_multiplier"
                                    class="form-control @error('holiday_overtime_multiplier') is-invalid @enderror"
                                    value="{{ old('holiday_overtime_multiplier',$companybasicSalary->holiday_overtime_multiplier) }}">
                                @error('holiday_overtime_multiplier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl:6 col-xl:-6 col-md-6">
                <div class="card">
                    <div class="card-header" style="">
                        <h4 class="" style="color: #1d64ae;">
                            Sunday Pay
                        </h4>
                    </div>
                    <div class="card-body">

                        <div class="row align-items-center" style="margin-bottom:20px">
                            <div class="col-xxl:3 col-xl:3 col-md-3">
                                <h6 class="" style="font-weight:400">
                                    Minimum Pay
                                </h6>
                            </div>
                            <div class="col-xxl:8 col-xl:8 col-md-8 ">
                                <!-- <label class="form-label require">{{ __(' Minimum Pay') }}</label>-->
                            <select name="minimum_pay" class="form-control @error('minimum_pay') is-invalid @enderror">
                                <option value="">Select </option>
                                <option value=" none" {{ old('minimum_pay',$companybasicSalary->minimum_pay) == 'none' ? 'selected' : '' }}> none</option>
                                <option value="  Normal Day's Wage" {{ old('minimum_pay',$companybasicSalary->minimum_pay) == 'Normal Days Wage' ? 'selected' : '' }}>  Normal Day's Wage</option>
                               
                                </option>
                                
                            </select>
                            @error('minimum_pay')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            </div>
                        </div>




                        <div class="row ">
                            <div class="col-xxl-8 col-xl-8 col-md-8 d-flex align-items-center gap-6">
                                <input type="checkbox" class="form-check-input" id="sunday" name="sunday" value="Override Sunday Pay Rates"
                                    onclick="toggleRow('SundayRow')" />
                                <label for="sunday" class="fs-6" style="padding-left:6px"> Override Sunday Pay Rates
                                </label>
                            </div>
                        </div>
                        <div class="row" id="SundayRow" style="display: none; margin-top: 10px;">
                            <div class="row align-items-center">
                                <div class="col-xxl:3 col-xl:3 col-md-3">
                                    <h6 class="" style="font-weight:400">
                                        Normally Works (default: 1.5x)
                                    </h6>
                                </div>
                                <div class="col-xxl:8 col-xl:8 col-md-8 ">
                                    <input type="number" name="normally_works_multiplier"
                                    class="form-control @error('normally_works_multiplier') is-invalid @enderror"
                                    value="{{ old('normally_works_multiplier',$companybasicSalary->normally_works_multiplier) }}">
                                @error('normally_works_multiplier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                </div>
                            </div>
                            <div class="row align-items-center py-4">
                                <div class="col-xxl:3 col-xl:3 col-md-3">
                                    <h6 class="" style="font-weight:400">
                                        Normally Off (default: 2.0x)
                                    </h6>
                                </div>
                                <div class="col-xxl:8 col-xl:8 col-md-8 ">
                                    <input type="number" name="normally_off_multiplier"
                                    class="form-control @error('normally_off_multiplier') is-invalid @enderror"
                                    value="{{ old('normally_off_multiplier',$companybasicSalary->normally_off_multiplier) }}">
                                @error('normally_off_multiplier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row py-2 ">
                            <div class="col-xxl-8 col-xl-8 col-md-8 d-flex align-items-center gap-6">
                                <input type="checkbox" class="form-check-input" id="separate_overtime_hours" name="separate_overtime_hours"
                                    value="Separate input for overtime hours (paid @ 2x)" />
                                <label for="separate_overtime_hours" class="fs-6" style="padding-left:6px"> Separate input for overtime
                                    hours (paid @ 2x) </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-start " style="margin-bottom: 20px;">
        <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">Cancel</button>
        <input class="btn btn-rc-primary" type="submit" value="Save">
    </div>
        </div>
    </div>
</form>
    <script>
        function toggleRow(rowId) {
            const row = document.getElementById(rowId);
            row.style.display = row.style.display === "none" ? "flex" : "none";
        }
    </script>
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
                url: '{{ route("company-basic-salaries.ajax-validate-update", $companybasicSalary->id) }}',
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