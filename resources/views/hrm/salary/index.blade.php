
@extends('layouts.main')



<style>
    table {
        width: 100%;
        border-collapse: collapse;
    }
    table, th, td {
        border: 1px solid #ddd;
        padding: 8px;
    }
    th {
        background-color: #f4f4f4;
        text-align: left;
    }
    input[type="text"], select {
        width: 100%;
        padding: 6px;
        box-sizing: border-box;
    }
    .add-row, .delete-row {
        cursor: pointer;
        color: #007bff;
        border: none;
        background: none;
    }
</style>

@section('page-title')
    {{ __('Create Employee') }}
@endsection

@section('content')
<div class="card-header d-flex justify-content-between">
    <h4>{{ __('SalaryDetails') }}</h4>
    <a href="{{ route('payslip-commissions.create') }}" class="btn btn-rc-primary">{{ __('create') }}</a>
</div>

    <div class="row">
        <div class="col-sm-12">
            <div class="mb-4 col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                <div class="col-md-6">
                    <ul class="nav nav-pills nav-fill cust-nav information-tab" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="personal-details" data-bs-toggle="pill"
                                data-bs-target="#personal-details-tab" type="button">{{ __('Personal Details') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="company" data-bs-toggle="pill" data-bs-target="#company-tab"
                                type="button">{{ __('Company Details') }}</button>
                        </li>
                    </ul>
                   
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {{ Form::open(['route' => ['salary.store'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}

            <div class="">
                <div class="">
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="personal-details-tab" role="tabpanel"
                            aria-labelledby="pills-user-tab-1">

                            <!-- Salary Details Code -->
                            <div class="col-sm-12 card py-4 px-4">
                                <h5 style="text-transform: uppercase;">{{ __('Salary Details') }}</h5>

                                <div class="form-group col-md-6">
                                    {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                                    <span class="text-danger">*</span>
                                    <div class="form-icon-user" id="nameDropdown">
                                        {{ Form::select('employee_id', 
                                            $employees->pluck('first_name', 'id')->toArray(), // Correcting the field names
                                            null,
                                            ['class' => 'form-control', 'id' => 'nameDropdown', 'required' => 'required', 'placeholder' => 'Select Employee']
                                        ) }}
                                    </div>
                                    <p class="text-danger d-none" id="name_validation">
                                        {{ __('This field is required.') }}
                                    </p>

                                    <!-- Container for the dynamically generated input field -->
                                <div class="form-group mt-3 d-none" id="dynamicInputContainer">
                                       {{ Form::label('dynamic_input', __('Annual CTC'), ['class' => 'form-label', 'id' => 'dynamicLabel']) }}
                                        <div class="form-icon-user">
                                            {{ Form::text('dynamic_input', null, ['class' => 'form-control', 'id' => 'annual_ctc','name' => 'annual_ctc', 'placeholder' => 'Enter your CTC']) }}
                                        </div>
                                    </div>
                                   
                                    <div class="col-md-4">
                                        <label class="font-weight-bold">Annual CTC</label>
                                        <input type="number" name="annual_ctc" id="annual_ctc" class="form-control" placeholder="Enter Annual CTC" required>
                                    </div>
                        
                                    
                                </div>

                                <!-- Table to Display Salary Components -->
                                <table id="salaryTable">
                                    <tr>
                                        <th colspan="5">Earnings</th>
                                    </tr>
                                    <tr>
                                        <th>Component</th>
                                        <th>Calculation Type</th>
                                        <th>Monthly Amount</th>
                                        <th>Annual Amount</th>
                                    </tr>
                                    <tr id="basicSalaryRow">
                                        <td>Basic</td>
                                        <td>70% of CTC</td>
                                        <td><input type="text" class="monthly-amount" id="basic-monthly" value="0" readonly></td>
                                        <td><input type="text" class="annual-amount" id="basic-annual" value="0" readonly></td>
                                    </tr>

                                    <!--<tr id="commissionRow">
                                        <td>Commission</td>
                                        <td>Calculated from Base Salary</td>
                                        <td><input type="text" class="monthly-amount" id="commission-monthly" value="0" readonly></td>
                                        <td><input type="text" class="annual-amount" id="commission-annual" value="0" readonly></td>
                                    </tr>-->
                                   
                                   
                                            <tr id="commissionRow">
                                                <td>Commission</td>
                                                <td>Calculated from Base Salary</td>
                                                <td><input type="text" class="monthly-amount" id="commission-monthly" value="{{ $commission ? $commission->commission_amount / 12 : 0 }}" readonly></td>
                                                <td><input type="text" class="annual-amount" id="commission-annual" value="{{ $commission ? $commission->commission_amount : 0 }}" readonly></td>
                                            </tr>
                                 <tr id="uifRow">
                                        <td>UIF</td>
                                        <td>Fixed</td>
                                        <td><input type="text" class="monthly-amount" id="uif-monthly" value="15000" readonly></td>
                                        <td><input type="text" class="annual-amount" id="uif-annual" value="15000" readonly></td>
                                    </tr>

                                    <tr id="paytaxRow">
                                        <td>Pay Tax</td>
                                        <td>Fixed</td>
                                        <td><input type="text" class="monthly-amount" id="paytax-monthly" value="15000" readonly></td>
                                        <td><input type="text" class="annual-amount" id="paytax-annual" value="15000" readonly></td>
                                    </tr>

                                    <tr>
                                        <th colspan="3">Total</th>
                                        <td><input type="text" class="total-salary" id="totalSalary" value="0" readonly></td>
                                    </tr>
                                </table>

                                <button class="add-row" onclick="addRow()">Add New Row</button>
                            </div>

                            <!-- Salary Details End -->
                        </div>
                    </div>
                </div>
            </div>

            {{ Form::close() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/jquery.min.js') }}"></script>

   
   
    <script>
       $(document).ready(function() {
    // Listen for changes in the Annual CTC input
    $('#annual_ctc').on('input', function() {
        const annualCTC = parseFloat($(this).val()) || 0;

        // Calculate Basic Salary (70% of Annual CTC)
        const basicAnnual = annualCTC * 0.70; // Annual Basic Salary
        const basicMonthly = basicAnnual / 12; // Monthly Basic Salary

        // Update the Basic Salary fields
        $('#basic-annual').val(basicAnnual.toFixed(2)); // Annual Basic Salary
        $('#basic-monthly').val(basicMonthly.toFixed(2)); // Monthly Basic Salary

        // Recalculate the total salary
        calculateTotalSalary();
    });

    // Listen for changes in the Employee dropdown
    $('#nameDropdown').change(function() {
        const employeeId = $(this).val();
        const annualCTC = $('#annual_ctc').val();

        if (employeeId && annualCTC) {
            $.ajax({
                url: '{{ route('salary.calculateSalary', '') }}/' + employeeId,
                method: 'GET',
                data: { annual_ctc: annualCTC },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Update Basic Salary
                        $('#basic-annual').val(response.basicAnnual);
                        $('#basic-monthly').val(response.basicMonthly);

                        // Update Commission
                        $('#commission-annual').val(response.commissionAnnual);
                        $('#commission-monthly').val(response.commissionMonthly);

                        // Update UIF and Pay Tax
                        $('#uif-annual').val(response.uifAnnual);
                        $('#uif-monthly').val(response.uifMonthly);
                        $('#paytax-annual').val(response.payTaxAnnual);
                        $('#paytax-monthly').val(response.payTaxMonthly);

                        // Update Total Salary
                        calculateTotalSalary();
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr) {
                    alert('Error calculating salary');
                }
            });
        }
    });

    // Function to calculate the total salary
    function calculateTotalSalary() {
        const basicAnnual = parseFloat($('#basic-annual').val()) || 0;
        const commissionAnnual = parseFloat($('#commission-annual').val()) || 0;
        const uifAnnual = parseFloat($('#uif-annual').val()) || 0;
        const paytaxAnnual = parseFloat($('#paytax-annual').val()) || 0;

        // Calculate the total annual salary
        const totalAnnual = basicAnnual + commissionAnnual + uifAnnual + paytaxAnnual;

        // Update the total salary field
        $('#totalSalary').val(totalAnnual.toFixed(2));
    }
});
    </script>
@endpush
