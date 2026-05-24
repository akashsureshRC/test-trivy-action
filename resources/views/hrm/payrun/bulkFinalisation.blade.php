@extends('layouts.main')
@section('page-title')
    {{ __('Payment Runs') }}
@endsection
@section('page-breadcrumb')
    {{ __('Payroll') }},
    {{ __('Payment Runs') }}
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp

@section('content')
    <style>
        .banner {
            background-color: #FFF4CC;
            padding: 10px;
            text-align: center;
            font-weight: bold;
        }
        .section-header {
            font-size: 20px;
            font-weight: bold;
            margin-top: 15px;
        }
        .alert-info {
            background-color: #D9F2FF;
            padding: 15px;
            font-size: 16px;
        }
        .toggle-switch {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-switch .form-check-input {
            width: 40px;
            height: 20px;
        }
        .btn-group {
            margin-top: 15px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 30%;
            box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1);
        }
        .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
    <script>
        function toggleRecentActivity() {
            const recentActivityElements = document.querySelectorAll('.recent-activity');
            const header = document.getElementById('recentActivityHeader');
            recentActivityElements.forEach(el => el.style.display = el.style.display === 'none' ? '' : 'none');
            header.style.display = header.style.display === 'none' ? '' : 'none';
        }

        function togglePayslipValues() {
            const payslipElements = document.querySelectorAll('.payslip-values');
            const headers = document.querySelectorAll('.payslip-header');
            payslipElements.forEach(el => el.style.display = el.style.display === 'none' ? '' : 'none');
            headers.forEach(header => header.style.display = header.style.display === 'none' ? '' : 'none');
        }

        function openFilterPopup() {
            document.getElementById("filterModal").style.display = "block";
        }
        function closeFilterPopup() {
            document.getElementById("filterModal").style.display = "none";
        }
    </script>
    <div class="d-flex justify-content-end"></div>
    <div class="banner">
        Welcome to RCBooks! Effortless payroll and accounting management at your fingertips.
    </div>
    <form method="POST" action="{{ route('payrun.bulkFinalisation.store') }}">
        @csrf
        <div class="container mt-4">
            <h3>Bulk Finalisation - Monthly - {{ $term }}</h3>

            {{-- <div class="d-flex align-items-center mb-3">
                <button  style="background: linear-gradient(250deg, rgba(141, 50, 134, 1) 37%, rgba(175, 55, 107, 1) 78%) !important;
                    }"class="btn btn-rc-outline btn-sm">
                    Pay Point
                </button>
                <button class="btn btn-link" onclick="openFilterPopup()">
                <input type="text" class="form-control form-control-sm w-auto" placeholder="Search">
            </div> --}}

            {{-- <div class="alert alert-info">
                <strong>Payslips that can't be finalised yet</strong><br>
                <strong>Bothma, Grant</strong>
                <p>Payslip has negative nett pay - you may want to skip some regular items.</p>
            </div> --}}

            {{-- <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="recentActivity" onclick="toggleRecentActivity()">
                <label class="form-check-label" for="recentActivity">Show recent activity</label>
            </div> --}}
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="payslipValues" onclick="togglePayslipValues()">
                <label class="form-check-label" for="payslipValues">Show payslip values</label>
            </div>

            <h4 class="section-header">Payslips Ready for Finalisation <i class="bi bi-file-earmark-excel"></i></h4>

            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th></th>
                        <th>Name</th>
                        <th>Number</th>
                        <th>Nett Pay</th>
                        <th id="recentActivityHeader" style="display: none;">Recent Activity</th>
                        <th class="payslip-header" style="display: none;">Basic Salary</th>
                        <th class="payslip-header" style="display: none;">Total Income</th>
                        <th class="payslip-header" style="display: none;">Total Allowance</th>
                        <th class="payslip-header" style="display: none;">Total Benefits</th>
                        <th class="payslip-header" style="display: none;">UIF - Employee</th>
                        <th class="payslip-header" style="display: none;">Vat (PAYE)</th>
                        <th class="payslip-header" style="display: none;">Total Deductions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ( $payslips as $payslip)
                        <tr>
                            <td>
                                <input type="checkbox" name="payslip_ids[]" value="{{ $payslip->id }}">
                            </td>
                            <td>{{ $payslip->employee_profile->first_name }} {{ $payslip->employee_profile->last_name }}</td>
                            <td>{{ $payslip->employee_profile->employee_id }}</td>
                            <td>{{ $payslip->netPayValue }}</td>
                            <td id="recentActivityHeader" style="display: none;">Recent Activity</td>
                            <th class="payslip-header" style="display: none;">{{ $payslip->basicSalaryValue }}</th>
                            <th class="payslip-header" style="display: none;">{{ $payslip->totalIncomeValue }}</th>
                            <th class="payslip-header" style="display: none;">{{ $payslip->totalAllowanceValue }}</th>
                            <th class="payslip-header" style="display: none;">{{ $payslip->totalBenefitValue }}</th>
                            <th class="payslip-header" style="display: none;">{{ $payslip->uifValue }}</th>
                            <th class="payslip-header" style="display: none;">{{ $payslip->payTaxValue }}</th>
                            <th class="payslip-header" style="display: none;">{{ $payslip->totalDeductionValue }}</th>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="btn-group">
                <a href="{{ route('payrun.index') }}" style="background-color:#b03060" class="btn btn-rc-outline me-3">Cancel</a></div>
                <div class="btn-group">
                <button style="background-color:#1f60a7" class="btn btn-rc-primary" type="submit">Finalise</button>
            </div>
        </div>
    </form>
    <div id="filterModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeFilterPopup()">&times;</span>
            <h5>Filter Pay Points</h5>
            <a href="#">All, None</a><br>
            <input type="checkbox" checked> Unassigned<br>
            <button class="btn btn-rc-outline mt-3" onclick="closeFilterPopup()">Close</button>
        </div>
    </div>
@endsection


