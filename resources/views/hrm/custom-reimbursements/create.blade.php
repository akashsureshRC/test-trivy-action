@extends('layouts.main')
@section('page-title')
    {{ __('Add Custom Reimbursement') }}
@endsection
@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Add Custom Reimbursement') }}
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp

@section('content')

    <body>
        <style>
            .btn-cancel {
                background-color: #b03060;
                /* Magenta/Pink */
                color: white;
            }

            .btn-create {
                background-color: #1f60a7;
                /* Blue */
                color: white;
            }

            .btn-cancel:hover {
                background-color: #9a2550;
                /* Slightly darker for hover */
            }

            .btn-create:hover {
                background-color: #174c80;
            }

            .white-section {
                background-color: white;
                padding: 20px;
                border-radius: 8px;
                /* Optional: Adds rounded corners */
                box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
                /* Optional: Adds shadow */
            }
        </style>

        <div class="container mt-5">
            <div class="white-section"> <!-- White background section -->
                <h3 class="mb-4">Add Additional Bank Account</h3>
                <form action="{{ route('custom-reimbursements.store') }}" class="mt-3" method="post">
                    @csrf
                    <div class="row mb-3">
                        <label class="col-sm-2 form-label">Name </label>
                        <div class="col-sm-10">
                            <input type="text" name="name"
                                class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>
                    </div>


                    <br>
                    <div class="row mb-3">
                        <label class="col-sm-2 form-label">Input Type </label>
                        <div class="col-sm-10">
                       
                        <select class="form-control" id="input_type" name="input_type">
                            <option value="different_on_every_payslip">Different on Every Payslip</option>
                            <option value="once_off">Once Off for Specified Payslips</option>
                            <option value="custom_rate_quantity">Custom Rate * Quantity</option>
                        </select>
                    </div></div>
                    
                    <!-- Checkbox for "Different Rate for Every Employee" -->
                    <div class="mb-3 form-check" id="different_rate_checkbox_container" style="display: none;">
                        <input type="checkbox" class="form-check-input" id="different_rate_for_every_employee" name="different_rate_for_every_employee">
                        <label class="form-check-label" for="different_rate_for_every_employee">Different Rate for Every Employee</label>
                    </div>
                    
                    <!-- Input box for "Custom Rate" -->
                    <div class="mb-3" id="custom_rate_container" style="display: none;">
                        <label for="custom_rate" class="form-label">Custom Rate</label>
                        <input type="number" step="0.01" class="form-control" id="custom_rate" name="custom_rate">
                    </div>
                    <div class="row mb-3 text-start">
                        <div class="col-sm-10">

                            <button type="button" class="btn btn-cancel"
                                onclick="window.location='{{ route('custom-beneficiaries.index') }}'">Cancel</button>
                            <button type="submit" class="btn btn-create">Create</button>
                        </div>
                    </div>
                </form>
            </div> <!-- End of white background section -->
        </div>
    </body>
    <script>
        document.getElementById('input_type').addEventListener('change', function() {
            let checkboxContainer = document.getElementById('different_rate_checkbox_container');
            let customRateContainer = document.getElementById('custom_rate_container');
            let checkbox = document.getElementById('different_rate_for_every_employee');
    
            if (this.value === 'custom_rate_quantity') {
                checkboxContainer.style.display = 'block'; // Show checkbox
                customRateContainer.style.display = 'block'; // Show input box by default
            } else {
                checkboxContainer.style.display = 'none'; // Hide checkbox
                customRateContainer.style.display = 'none'; // Hide input box
                checkbox.checked = false; // Reset checkbox
            }
        });
    
        document.getElementById('different_rate_for_every_employee').addEventListener('change', function() {
            let customRateContainer = document.getElementById('custom_rate_container');
            customRateContainer.style.display = this.checked ? 'none' : 'block'; // Hide input box when checkbox is checked
        });
    </script>
    <script>
        document.getElementById('different_rate_for_every_employee').addEventListener('change', function() {
            document.getElementById('custom_rate_container').style.display = this.checked ? 'none' : 'block';
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#include_eftexport').change(function() {
                if ($(this).is(':checked')) {
                    $('#eft_payment_type_div').show();
                } else {
                    $('#eft_payment_type_div').hide();
                    $('#your_reference_div, #beneficiary_reference_div').hide();
                    $('#eft_payment_type').val('');
                }
            });

            $('#eft_payment_type').change(function() {
                if ($(this).val() === 'lump_sum') {
                    $('#your_reference_div, #beneficiary_reference_div').show();
                } else {
                    $('#your_reference_div, #beneficiary_reference_div').hide();
                }
            });
        });
    </script>
@endsection
