@extends('layouts.main')
@section('page-title')
    {{ __('Regular Inputs') }}
@endsection
@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Regular Inputs') }}
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp
<style>
    .note-box {
        background-color: rgb(237, 237, 237);
        padding: 15px;
        border-radius: 5px;
        border: 1px solid #000;
        position: relative;
    }
    .note-box strong {
        font-weight: bold;
    }
    .close-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        border: none;
        background: none;
        font-size: 16px;
        cursor: pointer;
    }
    .disabled-field {
        pointer-events: none;
        opacity: 0.5;
    }
</style>
@section('content')
<div class="container mt-5">
    <h2>New Pay Frequency</h2>
    <div class="note-box mb-4" id="noteBox">
        <button class="close-btn" onclick="document.getElementById('noteBox').style.display='none'">&times;</button>
        <strong>Important Note</strong><br>
        The <strong>last day of the period / month</strong> is the last day for which the employees will be paid and not necessarily the same as the day on which they receive payment – e.g. if your employees are paid for working until the last day of the month, but are paid early, on the 25th, you would select the 31st, not the 25th.<br><br>
        The <strong>first payroll period</strong> is the first period that will be processed through SimplePay. You will automatically be asked for take-on balances for employees that started employment before this period.
    </div>
<div class="container mt-4">
    <h3>Select Pay Frequency</h3>
    <form action="{{ route('payfrequency.store') }}" class="mt-3" method="post">
        @csrf
        <div class="mb-3">
            <label for="frequency" class="form-label">Pay Frequency</label>
            <select class="form-control w-auto" name="pay_frequency" id="frequency" onchange="updateFields()">
                <option value="">Select</option>
                <option value="Daily">Daily</option>
                <option value="Weekly">Weekly</option>
                <option value="Fortnightly">Fortnightly</option>
            </select>
        </div>

        <div id="weeklyFields" class="d-none">
            <label>Last day of period:</label>
            <select class="form-control w-auto" id="last_day_of_period" name="last_day_of_period">
                <option>Monday</option>
                <option>Tuesday</option>
                <option>Wednesday</option>
                <option>Thursday</option>
                <option>Friday</option>
                <option>Saturday</option>
            </select>
            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" id="go-further" name="go_further_back" onclick="toggleFurtherBack()">
                <label class="form-check-label" for="go-further">Go further back by</label>
                <select id="years-back" name="years_back" class="form-control w-auto ms-2 disabled-field" disabled>
                    <option value="2">2 years</option>
                    <option value="max">Max</option>
                </select>
            </div>
        </div>

        <div id="dailyFields" class="d-none">
            <div>
                <label>Example of last day of 2-week period (select any valid date)</label>
                <input type="text" class="form-control datepicker" id="daily-datepicker" placeholder="Select date" name="biweekly_date">
            </div>
            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" id="go-further-daily" name="go_further_back" onclick="toggleFurtherBack('daily')">
                <label class="form-check-label" for="go-further-daily">Go further back by</label>
                <select id="years-back-daily" name="years_back" class="form-control w-auto ms-2 disabled-field" disabled>
                    <option value="2">2 years</option>
                    <option value="max">Max</option>
                </select>
            </div>
        </div>
        <div id="fortnightlyFields" class="d-none">
            <label>Last day of month:</label>
            <select class="form-control w-auto" id="last_day_of_month" name="last_day_of_month">
                <script>
                    for (let i = 1; i <= 30; i++) {
                        document.write(`<option>${i}</option>`);
                    }
                </script>
            </select>
            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" id="go-further-fortnightly" name="go_further_back" onclick="toggleFurtherBack('fortnightly')">
                <label class="form-check-label" for="go-further-fortnightly">Go further back by</label>
                <select id="years-back-fortnightly" name="years_back" class="form-control w-auto ms-2 disabled-field" disabled>
                    <option value="2">2 years</option>
                    <option value="max">Max</option>
                </select>
            </div>
        </div>

        <button type="button" class="btn btn-rc-outline";>Cancel</button>
        <button type="submit" class="btn btn-rc-primary">Save</button>
    </form>
</div>
</div>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
    function updateFields() {
        let frequency = document.getElementById("frequency").value;

        // Hide all fields initially
        document.getElementById("dailyFields").classList.add("d-none");
        document.getElementById("weeklyFields").classList.add("d-none");
        document.getElementById("fortnightlyFields").classList.add("d-none");

        // Show selected fields
        if (frequency === "Daily") {
            document.getElementById("dailyFields").classList.remove("d-none");
        } else if (frequency === "Weekly") {
            document.getElementById("weeklyFields").classList.remove("d-none");
        } else if (frequency === "Fortnightly") {
            document.getElementById("fortnightlyFields").classList.remove("d-none");
        }
    }

    function toggleFurtherBack(id) {
        let checkbox = document.getElementById("go-further-" + id);
        let dropdown = document.getElementById("years-back-" + id);

        if (checkbox.checked) {
            dropdown.disabled = false;  // Enable dropdown
            dropdown.classList.remove("enable-field");
        } else {
            dropdown.disabled = true;   // Disable dropdown
            dropdown.classList.add("disabled-field");
        }
    }
</script>
<script>
    function toggleFurtherBack(payType) {
        let checkbox = document.getElementById("go-further-" + payType);
        let dropdown = document.getElementById("years-back-" + payType);

        if (checkbox.checked) {
            dropdown.disabled = false;
            dropdown.classList.remove("disabled-field");
        } else {
            dropdown.disabled = true;
            dropdown.classList.add("disabled-field");
        }
    }
</script>
<script>
    $(document).ready(function () {
        // Initialize datepicker
        $("#daily-datepicker").datepicker({
            dateFormat: "yy-mm-dd",
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true
        });

        // Toggle dropdown enable/disable on checkbox click
        $("#go-further").change(function () {
            if ($(this).is(":checked")) {
                $("#years-back").prop("disabled", false).removeClass("disabled-field");
            } else {
                $("#years-back").prop("disabled", true).addClass("disabled-field");
            }
        });
    });
</script>

@endsection
