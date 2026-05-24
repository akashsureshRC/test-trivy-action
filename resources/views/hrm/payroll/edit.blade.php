@extends('layouts.main')

@section('page-title', 'Basic Salary')

@section('content')

@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
<h2>{{ isset($basicSalary) ? 'Update Basic Salary' : 'Add Basic Salary' }}</h2>
<p>Employee ID: {{ request('employee_id') }}</p>

    <form action="{{ route('basic-salariess.update', $basicSalary->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card">
            <div class="card-body">
                <!--<div class="form-group">
                        <label>Employee Name</label>
                        <input type="text" class="form-control" name="employee_name" required>
                    </div>-->

                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="hourly_paid" name="hourly_paid" value="1">
                        <label class="form-check-label" for="hourly_paid">Hourly Paid</label>
                    </div>

                <!-- Hourly Rate Input original-->
        <div class="mb-3" id="hourlyRateBox" style="display: none;">
            <label for="hourly_rate" class="form-label">Hourly Rate</label>
            <input type="number" step="0.01" class="form-control" id="hourly_rate" name="hourly_rate"  >
        </div>
       <!-- <form action="{{ route('basic-salaries.store') }}" method="POST">
            @csrf
            <label for="fixed_salary">Fixed Salary:</label>
            <input type="text" name="fixed_salary" id="fixed_salary" placeholder="Enter Fixed Salary">
            <button type="submit">Save</button>
        </form>-->

                 <!-- Don't Auto-Pay Public Holidays -->
        <div class="form-check mb-3" id="dontAutoPayBox" style="display: none;">
            <input type="checkbox" class="form-check-input" id="dont_auto_pay_public_holidays" name="dont_auto_pay_public_holidays" value="1">
            <label class="form-check-label" for="dont_auto_pay_public_holidays">Don't Auto-Pay Public Holidays</label>
        </div>

        <p id="payslipPrompt" style="display: none; color: blue;">You will be prompted on every payslip for Normal Hours and Overtime Hours.</p>
       
    <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">

        <div class="mb-3" id="fixedSalaryBox" style="display: none;">
            <label for="fixed_salary" class="form-label">Fixed Salary</label>
            <input type="number" step="0.01" class="form-control" id="fixed_salary" name="fixed_salary"  value="{{ old('fixed_salary', $basicSalary->fixed_salary) }}">
        </div>
       <!-- Fixed Salary Input (New Additional Input) 
<div class="mb-3" id="fixedSalaryBoxSecondary" style="display: none;">
    <label for="fixed_salary_secondary" class="form-label">Fixed Salary (Secondary)</label>
    <input type="number" step="0.01" class="form-control" id="fixed_salary_secondary" name="fixed_salary_secondary">
</div> -->
        
        <!-- Fixed Salary Box (Initially Hidden) dummy
        <div class="mb-3" id="fixedSalaryBox" style="display: none;"> 
            <label for="fixed_salary" class="form-label">Fixed Salary</label>
            <input type="number" step="0.01" class="form-control" id="fixed_salary" name="fixed_salary">
        </div>-->
        <!-- Paid for Additional Hours Checkbox -->
        <div class="form-check mb-3" id="paidForAdditionalBox" style="display: none;">
            <input type="checkbox" class="form-check-input" id="paid_for_additional_hours" name="paid_for_additional_hours" value="1">
            <label class="form-check-label" for="paid_for_additional_hours">Paid for Additional Hours</label>
        </div>

        <!-- Override Hourly Rate Checkbox -->
        <div class="form-check mb-3" id="overrideHourlyRateBox" style="display: none;">
            <input type="checkbox" class="form-check-input" id="override_hourly_rate" name="override_hourly_rate" value="1">
            <label class="form-check-label" for="override_hourly_rate">Override Calculated Hourly Rate</label>
        </div>

        <!-- Rate Override Input -->
        <div class="mb-3" id="rateOverrideBox" style="display: none;">
            <label for="rate_override" class="form-label">Rate Override</label>
            <input type="number" step="0.01" class="form-control" id="rate_override" name="rate_override"  >
        </div>
                <button type="submit" class="btn btn-rc-primary" id="save_salary">Submit</button>
            </div>
        </div>
    </form>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script type="text/javascript">
 $(document).ready(function () {
    // Trigger when the Save Salary button is clicked
    $('#save_salary').on('click', function () {
        var employee_id = $('#employee_id').val();  // Get employee_id from input field
        var fixed_salary = parseFloat($('#fixed_salary').val());  // Get fixed_salary from input field and convert to number

        // Validate that the fields are not empty and fixed_salary is a number
        if (!employee_id || isNaN(fixed_salary)) {
            alert('Please enter valid Employee ID and Fixed Salary.');
            return;
        }

        // Send the data using AJAX
        $.ajax({
            url: "{{ route('basic-salariess.store') }}",  // Your route for saving salary
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",  // CSRF token for security
                employee_id: employee_id,  // Pass the employee ID
                fixed_salary: fixed_salary,  // Pass the fixed salary
            },
            success: function(response) {
                if (response.success) {
                    // Update UI with the returned payroll values
                    $('#basic_salary').text(response.payroll.basic_salary);
                    $('#uif').text(response.payroll.uif);
                    $('#pay_tax').text(response.payroll.pay_tax);
                    $('#net_pay').text(response.payroll.net_pay);
                } else {
                    alert('There was an error saving the salary.');
                }
            },
            error: function(xhr, status, error) {
                alert('Error: ' + error);
            }
        });
    });
});
</script>
    <script>
        document.getElementById("basic_salary").addEventListener("input", function () {
            let basic_salary = parseFloat(this.value) || 0;
            let uif = basic_salary * 0.01;
            let tax_pay = basic_salary * 0.18;
            let net_pay = basic_salary - (uif + tax_pay);
        
            document.getElementById("uif").innerText = uif.toFixed(2);
            document.getElementById("tax_pay").innerText = tax_pay.toFixed(2);
            document.getElementById("net_pay").innerText = net_pay.toFixed(2);
        });
        
        </script>
    <script>
        $(document).ready(function(){
            $("#salaryForm").submit(function(e){
                e.preventDefault();
                
                $.ajax({
                    url: "{{ route('basic-salaries.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        alert(response.success);
                        
                        // Get selected employee name
                        let selectedEmployeeName = $("#employee_id option:selected").text();
                        
                        // Store employee name in session storage
                        sessionStorage.setItem("employeeName", selectedEmployeeName);
    
                        fetchPayrollData();
                    }
                });
            });
        });
    
        function fetchPayrollData() {
            $.ajax({
                url: "{{ route('payroll.fetch') }}",
                type: "GET",
                success: function(data) {
                    let payrollTable = $("#payrollTable tbody");
                    payrollTable.empty();
    
                    if (data.payrolls.length > 0) {
                        let firstEmployee = sessionStorage.getItem("employeeName") || data.payrolls[0].employee.name;
                        $("#employeeName").text(firstEmployee);
                    }
    
                    $.each(data.payrolls, function(index, payroll) {
                        let basic_salary = payroll.basic_salary ? payroll.basic_salary.fixed_salary : 0;
                        let uif = 200;
                        let tax_pay = 100;
                        let net_pay = basic_salary - (uif + tax_pay);
    
                        payrollTable.append(`
                            <tr>
                                <td>${payroll.employee.name}</td>
                                <td>${basic_salary.toFixed(2)}</td>
                                <td>${uif.toFixed(2)}</td>
                                <td>${tax_pay.toFixed(2)}</td>
                                <td>${net_pay.toFixed(2)}</td>
                            </tr>
                        `);
                    });
                }
            });
        }
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script>
        document.addEventListener("DOMContentLoaded", function () {
            const hourlyPaid = document.getElementById("hourly_paid");
            const hourlyRateBox = document.getElementById("hourlyRateBox");
            const dontAutoPayBox = document.getElementById("dontAutoPayBox");
            const payslipPrompt = document.getElementById("payslipPrompt");
        
            const fixedSalaryBox = document.getElementById("fixedSalaryBox");
            const paidForAdditionalBox = document.getElementById("paidForAdditionalBox");
            const overrideHourlyRateBox = document.getElementById("overrideHourlyRateBox");
            const rateOverrideBox = document.getElementById("rateOverrideBox");
        
            const paidForAdditional = document.getElementById("paid_for_additional_hours");
            const overrideHourlyRate = document.getElementById("override_hourly_rate");
        
            function toggleFields() {
                if (hourlyPaid.checked) {
                    hourlyRateBox.style.display = "block";
                    dontAutoPayBox.style.display = "block";
                    payslipPrompt.style.display = "block";
        
                    fixedSalaryBox.style.display = "none";
                    paidForAdditionalBox.style.display = "none";
                    overrideHourlyRateBox.style.display = "none";
                    rateOverrideBox.style.display = "none";
                } else {
                    hourlyRateBox.style.display = "none";
                    dontAutoPayBox.style.display = "none";
                    payslipPrompt.style.display = "none";
        
                    fixedSalaryBox.style.display = "block";
                    paidForAdditionalBox.style.display = "block";
                }
            }
        
            function toggleOverride() {
                if (paidForAdditional.checked) {
                    overrideHourlyRateBox.style.display = "block";
                } else {
                    overrideHourlyRateBox.style.display = "none";
                    rateOverrideBox.style.display = "none";
                    overrideHourlyRate.checked = false;
                }
            }
        
            function toggleRateOverride() {
                if (overrideHourlyRate.checked) {
                    rateOverrideBox.style.display = "block";
                } else {
                    rateOverrideBox.style.display = "none";
                }
            }
        
            hourlyPaid.addEventListener("change", toggleFields);
            paidForAdditional.addEventListener("change", toggleOverride);
            overrideHourlyRate.addEventListener("change", toggleRateOverride);
        
            toggleFields();
        });
        </script>
        <!-- payroll calculation -->
        <script>
            $("#basicSalaryForm").submit(function(e) {
                e.preventDefault();
                var amount = $("#amount").val();
    
                $.ajax({
                    url: "{{ route('basic-salaries.store') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        amount: amount
                    },
                    success: function(response) {
                        if (response.success) {
                            alert("Basic Salary Updated Successfully!");
                            window.location.href = "{{ route('payroll.index') }}";
                        }
                    }
                });
            });
        </script>
@endsection
