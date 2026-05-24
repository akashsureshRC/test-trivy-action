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
    <div class="" style="margin-top:40px">
        <div class="card py-4 px-4 ">
            <div class="row py-4 align-items-center">
                <div class="col-xxl:2 col-xl:2 col-md-2">
                    <h5 class="">
                        Hourly Rate
                    </h5>
                </div>
                <div class="col-xxl:4 col-xl:4 col-md-4">
                    <input class="form-control" />
                </div>
            </div>

            <div class="row py-2 mt-6">
                <div class="col-xxl-4 col-xl-4 col-md-4 d-flex align-items-center gap-6">
                    <input type="checkbox" id="public" name="public" value="Don't auto-pay public holidays" />
                    <label for="public" class="fs-6" style="padding-left:6px"> Don't auto-pay public holidays </label>
                </div>
            </div>
            <div class="row">
                <div class="col-xxl-4 col-xl-4 col-md-4 d-flex align-items-center gap-6">
                    <input type="checkbox" id="shifts" name="shifts" value="Enable input of number of shifts"
                        onclick="toggleRow('publicRow')" />
                    <label for="shifts" class="fs-6" style="padding-left:6px"> Enable input of number of shifts </label>
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
                            <div class="col-xxl:3 col-xl:3 col-md-3">
                                <h6 class="" style="font-weight:400">
                                    Minimum Pay
                                </h6>
                            </div>
                            <div class="col-xxl:8 col-xl:8 col-md-8 ">
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
                            </div>
                        </div>
                        <div class="row align-items-center py-4">
                            <div class="col-xxl:3 col-xl:3 col-md-3">
                                <h6 class="" style="font-weight:400">
                                    Fixed Component
                                </h6>
                            </div>
                            <div class="col-xxl:8 col-xl:8 col-md-8 ">
                                <select class="form-select">
                                    <option class="">
                                        none
                                    </option>
                                    <option class="">
                                        Normal Day's Wage
                                    </option>
                                </select>
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
                                <select class="form-select">
                                    <option class="">
                                        none
                                    </option>
                                    <option class="">
                                        Normal Day's Wage
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="row align-items-center py-4">
                            <div class="col-xxl:3 col-xl:3 col-md-3">
                                <h6 class="" style="font-weight:400">
                                    Fixed Component
                                </h6>
                            </div>
                            <div class="col-xxl:8 col-xl:8 col-md-8 ">
                                <select class="form-select">
                                    <option class="">
                                        none
                                    </option>
                                    <option class="">
                                        Normal Day's Wage
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="row py-4">
                            <div class="col-xxl-8 col-xl-8 col-md-8 d-flex align-items-center gap-6">
                                <input type="checkbox" id="Rates" name="Rates" value="Override Holiday Pay Rates"
                                    onclick="toggleRow('RatesRow')" />
                                <label for="Rates" class="fs-6" style="padding-left:6px"> Override Holiday Pay Rates
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
                                    <input class="form-control" />
                                </div>
                            </div>
                            <div class="row align-items-center py-4">
                                <div class="col-xxl:3 col-xl:3 col-md-3">
                                    <h6 class="" style="font-weight:400">
                                        Holiday overtime multiplier
                                    </h6>
                                </div>
                                <div class="col-xxl:8 col-xl:8 col-md-8 ">
                                    <input class="form-control" />
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
                            </div>
                        </div>




                        <div class="row ">
                            <div class="col-xxl-8 col-xl-8 col-md-8 d-flex align-items-center gap-6">
                                <input type="checkbox" id="sunday" name="sunday" value="Override Sunday Pay Rates"
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
                                    <input class="form-control" />
                                </div>
                            </div>
                            <div class="row align-items-center py-4">
                                <div class="col-xxl:3 col-xl:3 col-md-3">
                                    <h6 class="" style="font-weight:400">
                                        Normally Off (default: 2.0x)
                                    </h6>
                                </div>
                                <div class="col-xxl:8 col-xl:8 col-md-8 ">
                                    <input class="form-control" />
                                </div>
                            </div>
                        </div>
                        <div class="row py-2 ">
                            <div class="col-xxl-8 col-xl-8 col-md-8 d-flex align-items-center gap-6">
                                <input type="checkbox" id="overtime" name="overtime"
                                    value="Separate input for overtime hours (paid @ 2x)" />
                                <label for="overtime" class="fs-6" style="padding-left:6px"> Separate input for overtime
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

    <script>
        function toggleRow(rowId) {
            const row = document.getElementById(rowId);
            row.style.display = row.style.display === "none" ? "flex" : "none";
        }
    </script>
@endsection