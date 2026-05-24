@extends('layouts.main')

@section('page-title')
    {{ __('Edit Pay Frequency') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }}, {{ __('Edit Pay Frequency') }}
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
    <h2>Edit Pay Frequency</h2>

    <!-- Note Box -->
    <div class="note-box mb-4" id="noteBox">
        <button class="close-btn" onclick="document.getElementById('noteBox').style.display='none'">&times;</button>
        <strong>Important Note</strong><br>
        The <strong>last day of the period / month</strong> is the last day for which employees will be paid, not necessarily the payday. 
        For example, if employees are paid until the last day of the month but receive payment on the 25th, select the 31st, not the 25th.<br><br>
        The <strong>first payroll period</strong> is the first period processed through SimplePay. Employees hired before this period will require take-on balances.
    </div>

    <!-- Pay Frequency Form -->
    <form action="{{ route('payfrequency.update', $payFrequency->id) }}" method="POST" class="mt-3">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="frequency" class="form-label">Pay Frequency</label>
            <select class="form-control w-auto" name="pay_frequency" id="frequency" onchange="updateFields()">
                <option value="">Select</option>
                <option value="Daily" {{ old('pay_frequency', $payFrequency->pay_frequency) == 'Daily' ? 'selected' : '' }}>Daily</option>
                <option value="Weekly" {{ old('pay_frequency', $payFrequency->pay_frequency) == 'Weekly' ? 'selected' : '' }}>Weekly</option>
                <option value="Fortnightly" {{ old('pay_frequency', $payFrequency->pay_frequency) == 'Fortnightly' ? 'selected' : '' }}>Fortnightly</option>
            </select>
        </div>

        <!-- Daily Fields -->
        <div id="dailyFields" class="d-none">
            <label>Example of last day of 2-week period:</label>
            <input type="text" class="form-control datepicker" id="daily-datepicker"
                   name="biweekly_date"
                   value="{{ old('biweekly_date', $payFrequency->biweekly_date) }}">
            
            <!-- Checkbox for Daily -->
            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" id="go-further-daily" name="go_further_back"
                    onclick="toggleFurtherBack('daily')"
                    {{ old('go_further_back', $payFrequency->go_further_back) ? 'checked' : '' }}>
                <label class="form-check-label" for="go-further-daily">Go further back by</label>
            
                <select id="years-back-daily" name="years_back" class="form-control w-auto ms-2 disabled-field" disabled>
                    <option value="2" {{ old('years_back', $payFrequency->years_back) == '2' ? 'selected' : '' }}>2 years</option>
                    <option value="max" {{ old('years_back', $payFrequency->years_back) == 'max' ? 'selected' : '' }}>Max</option>
                </select>
            </div>
        </div>

        <!-- Weekly Fields -->
        <div id="weeklyFields" class="d-none">
            <label>Last day of period:</label>
            <select class="form-control w-auto" id="last_day_of_period" name="last_day_of_period">
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
                <option value="Saturday">Saturday</option>
            </select>

            <!-- Checkbox for Weekly -->
            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" id="go-further-weekly" name="go_further_back"
                    onclick="toggleFurtherBack('weekly')">
                    {{ old('go_further_back', $payFrequency->go_further_back) ? 'checked' : '' }}>
                <label class="form-check-label" for="go-further-weekly">Go further back by</label>
            
                <select id="years-back-weekly" name="years_back" class="form-control w-auto ms-2 disabled-field" disabled>
                    <option value="2" {{ old('years_back', $payFrequency->years_back) == '2' ? 'selected' : '' }}>2 years</option>
                    <option value="max" {{ old('years_back', $payFrequency->years_back) == 'max' ? 'selected' : '' }}>Max</option>
                </select>
            </div>
        </div>

        <!-- Fortnightly Fields -->
        <div id="fortnightlyFields" class="d-none">
            <label>Last day of month:</label>
            <select class="form-control w-auto" id="last_day_of_month" name="last_day_of_month">
                @for ($i = 1; $i <= 30; $i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>

            <!-- Checkbox for Fortnightly -->
            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" id="go-further-fortnightly" name="go_further_back"
                    onclick="toggleFurtherBack('fortnightly')">
                    {{ old('go_further_back', $payFrequency->go_further_back) ? 'checked' : '' }}>
                <label class="form-check-label" for="go-further-fortnightly">Go further back by</label>
            
                <select id="years-back-fortnightly" name="years_back" class="form-control w-auto ms-2 disabled-field" disabled>
                    <option value="2" {{ old('years_back', $payFrequency->years_back) == '2' ? 'selected' : '' }}>2 years</option>
                    <option value="max" {{ old('years_back', $payFrequency->years_back) == 'max' ? 'selected' : '' }}>Max</option>
                </select>
            </div>
        </div>

        <!-- Buttons -->
        <button type="button" class="btn btn-rc-outline">Cancel</button>
        <button type="submit" class="btn btn-rc-primary">Update</button>
    </form>
</div>

<!-- jQuery & Datepicker -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<script>
    function updateFields() {
        let frequency = document.getElementById("frequency").value;

        document.getElementById("dailyFields").classList.add("d-none");
        document.getElementById("weeklyFields").classList.add("d-none");
        document.getElementById("fortnightlyFields").classList.add("d-none");

        if (frequency === "Daily") {
            document.getElementById("dailyFields").classList.remove("d-none");
        } else if (frequency === "Weekly") {
            document.getElementById("weeklyFields").classList.remove("d-none");
        } else if (frequency === "Fortnightly") {
            document.getElementById("fortnightlyFields").classList.remove("d-none");
        }
    }

    function toggleFurtherBack(type) {
        let checkbox = document.getElementById(`go-further-${type}`);
        let dropdown = document.getElementById(`years-back-${type}`);

        dropdown.disabled = !checkbox.checked;
        dropdown.classList.toggle("disabled-field", !checkbox.checked);
    }

    document.addEventListener("DOMContentLoaded", function () {
        updateFields();
    });

    $(document).ready(function () {
        $("#daily-datepicker").datepicker({
            dateFormat: "yy-mm-dd",
            changeMonth: true,
            changeYear: true
        });
    });
</script>
@endsection
