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

@section('content')
<style>
    body {
        background-color: #f8f9fa;
        display: flex;
        align-items: flex-start;
        height: 100vh;
        padding: 20px;
    }
    .container {
        width: 400px;
    }
    .list-group-item {
        border: none;
        font-size: 16px;
    }
    .list-group-item a {
        color: #087CA7;
        text-decoration: none;
    }
    .list-group-item a:hover {
        text-decoration: underline;
        
    }
    .add-btn {
        border: 1px solid  #087CA7;
        color:rgb(255, 255, 255);
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .add-btn a:hover{

        color:white;
    }
    .separator {
        border-top: 1px solid grey;
        margin-top: 10px;
        margin-bottom: 10px;
    }
    .highlight-bold {
font-weight: bold;

padding: 5px;
}
</style>
    <div class="d-flex justify-content-end"></div>
    <div class="" style="margin-top:10px; padding-bottom: 55px;">
        <div class="container">
            <h3 class="mb-4" style="font-weight: bold;  padding: 5px;">Pay Frequencies</h3>
        
        <div class="separator"></div>
                <div class="card">
                    <ul class='vl-simple-main-list list-group'>
                        
                        @foreach($payFrequencies as $payFrequency)
                            <li class='list-group-item'>
                                <a href="{{ route('payfrequency.edit', $payFrequency->id) }}">
                                    {{ $payFrequency->pay_frequency }}, 
                                    ending on 
                                    @if($payFrequency->pay_frequency == 'Weekly')
                                        {{ $payFrequency->last_day_of_period }}</li>
                                        
                                    @elseif($payFrequency->pay_frequency == 'Daily')
                                        {{ $payFrequency->last_day_of_period }} (e.g.: {{ $payFrequency->biweekly_date }})
                                            </li>
                                    @elseif($payFrequency->pay_frequency == 'Fortnightly')
                                        the {{ $payFrequency->last_day_of_month }}@if($payFrequency->last_day_of_month == 1)st
                                        @elseif($payFrequency->last_day_of_month == 2)nd
                                        @elseif($payFrequency->last_day_of_month == 3)rd
                                        @else th
                                        @endif
                                        @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <button class="btn btn-outline-success mt-3 add-btn">
                    <span>&#x2795;</span> <a href="{{ route('payfrequency.create') }}" style="text-decoration:none; color:#087CA7">Add
                </button>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Retrieve values from Laravel Blade variables
            let basicSalary = parseFloat("{{ $basicSalary ?? 0 }}");
            let travelAllowance = parseFloat("{{ $travelAllowance ?? 0 }}");
            let incomePolicy = parseFloat("{{ $incomePolicy ?? 0 }}");

            // Ensure all values are valid numbers
            basicSalary = isNaN(basicSalary) ? 0 : basicSalary;
            travelAllowance = isNaN(travelAllowance) ? 0 : travelAllowance;
            incomePolicy = isNaN(incomePolicy) ? 0 : incomePolicy;

            // Calculate Total Income
            let totalIncome = basicSalary + travelAllowance + incomePolicy;

            // Display the calculated Income in the HTML
            document.getElementById("income_policy").innerText = totalIncome.toFixed(2);
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Get values from PHP variables
            let basicSalary = parseFloat("{{ $basicSalary ?? 0 }}");
            let travelAllowance = parseFloat("{{ $travelAllowance ?? 0 }}");
            let incomePolicy = parseFloat("{{ $incomePolicy ?? 0 }}");
            let uif = parseFloat("{{ $uif ?? 0 }}");
            let payTax = parseFloat("{{ $payTax ?? 0 }}");

            // Calculate total earnings
            let totalEarnings = basicSalary + travelAllowance + incomePolicy;

            // Calculate total deductions
            let totalDeductions = uif + payTax;

            // Calculate net pay (Total Earnings - Total Deductions)
            let netPay = totalEarnings - totalDeductions;

            // Update the HTML elements dynamically
            document.getElementById("basic_salary").textContent = "R " + basicSalary.toFixed(2);
            document.getElementById("income_policy").textContent = "R " + incomePolicy.toFixed(2);
            document.getElementById("travel_allowance").textContent = "R " + travelAllowance.toFixed(2);
            document.getElementById("uif").textContent = "R " + uif.toFixed(2);
            document.getElementById("pay_tax").textContent = "R " + payTax.toFixed(2);

            // Display total earnings, deductions, and net pay
            document.getElementById("total_earnings").textContent = "R " + totalEarnings.toFixed(2);
            document.getElementById("total_deductions").textContent = "R " + totalDeductions.toFixed(2);
            document.getElementById("net_pay").textContent = "R " + netPay.toFixed(2);
        });
    </script>

    <script>
        function toggleRow(rowId) {
            const row = document.getElementById(rowId);
            row.style.display = row.style.display === "none" ? "flex" : "none";
        }
    </script>
@endsection
