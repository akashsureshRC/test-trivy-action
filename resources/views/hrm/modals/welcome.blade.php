
@extends('layouts.main')

@section('page-title')
    {{ __('Welcome Page') }}
@endsection

@section('content')

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    /* Button Styles */
    .open-popup-btn {
        padding: 10px 15px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    .open-popup-btn:hover {
        background-color: #0056b3;
    }
</style>

<div class="container mt-5 ">
    <!-- Button to Open Income Popup -->
    <button class="open-popup-btn" data-bs-toggle="modal" data-bs-target="#incomeModal">Open Income Popup</button>
</div>

<!-- Income Popup Modal -->
<div class="modal fade" id="incomeModal" tabindex="-1" aria-labelledby="incomeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Income & Deductions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row text-center">
                    <!-- Income Section -->
                    <div class="col-md-4">
                        <h4 class="text-primary">Income</h4>
                        <ul class="list-group">
                            <li class="list-group-item list-group-item-action" onclick="showCommissionField()">Commission</li>
                            <a href="{{ route('payslip-commissions.index') }}" class="list-group-item list-group-item-action">Commission</a>
                            <li class="list-group-item list-group-item-action" onclick="showMessage('Loss of Income Policy Payout')">Loss of Income Policy Payout</li>
                        </ul>

                        <h4 class="text-primary mt-3">Allowance</h4>
                        <ul class="list-group">
                            <li class="list-group-item list-group-item-action" onclick="showMessage('Travel Allowance')">Travel Allowance</li>
                        </ul>

                        <h4 class="text-primary mt-3">Benefit</h4>
                        <ul class="list-group">
                            <li class="list-group-item list-group-item-action" onclick="showMessage('Accommodation Benefit')">Accommodation Benefit</li>
                            <li class="list-group-item list-group-item-action" onclick="showMessage('Bursaries And Scholarships (Regular)')">Bursaries And Scholarships (Regular)</li>
                            <li class="list-group-item list-group-item-action" onclick="showMessage('company-car-taxable-types.index')">Company Car</li>
                            <a href="{{ route('company-car-taxable-types.index') }}" class="list-group-item list-group-item-action">Company Car</a>


                            <li class="list-group-item list-group-item-action" onclick="showMessage('Company Car Under Operating Lease')">Company Car Under Operating Lease</li>
                        </ul>
                    </div>

                    <!-- Deduction Section -->
                    <div class="col-md-4">
                        <h4 class="text-danger">Deduction</h4>
                        <ul class="list-group">
                            <li class="list-group-item list-group-item-action" onclick="showMessage('Garnishee')">Garnishee</li>
                            <li class="list-group-item list-group-item-action" onclick="showMessage('Income Protection')">Income Protection</li>
                            <li class="list-group-item list-group-item-action" onclick="showMessage('Maintenance Order')">Maintenance Order</li>
                            <li class="list-group-item list-group-item-action" onclick="showMessage('Medical Aid')">Medical Aid</li>
                            <li class="list-group-item list-group-item-action" onclick="showMessage('Pension Fund')">Pension Fund</li>
                            <li class="list-group-item list-group-item-action" onclick="showMessage('Provident Fund')">Provident Fund</li>
                            <li class="list-group-item list-group-item-action" onclick="showMessage('Retirement Annuity Fund')">Retirement Annuity Fund</li>
                            <li class="list-group-item list-group-item-action" onclick="showMessage('Union Membership Fee')">Union Membership Fee</li>
                            <li class="list-group-item list-group-item-action" onclick="showMessage('Voluntary Tax Over-Deduction')">Voluntary Tax Over-Deduction</li>
                        </ul>
                    </div>

                    <!-- Other Section -->
                    <div class="col-md-4">
                        <h4 class="text-secondary">Other</h4>
                        <ul class="list-group">
                            <li class="list-group-item list-group-item-action" onclick="showMessage('Employer Loan')">Employer Loan</li>
                            <li class="list-group-item list-group-item-action" onclick="showMessage('Foreign Service Income')">Foreign Service Income</li>
                            <li class="list-group-item list-group-item-action" onclick="showMessage('Savings')">Savings</li>
                            <li class="list-group-item list-group-item-action" onclick="showMessage('Tax Directive')">Tax Directive</li>
                        </ul>
                    </div>
                </div>

                <!-- Commission Input Field -->
                <div class="mt-4 text-center d-none" id="commissionField">
                    <h5>Enter Commission Percentage:</h5>
                    <input type="text" class="form-control w-50 mx-auto" placeholder="Enter percentage">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap & JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showMessage(option) {
        alert("You selected: " + option);
    }

    function showCommissionField() {
        document.getElementById('commissionField').classList.remove('d-none');
    }
</script>

@endsection
