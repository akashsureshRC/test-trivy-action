@extends('layouts.main')
@section('page-title')
    {{ __('Add Custom Beneficiary') }}
@endsection
@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Add Custom Beneficiary') }}
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp

@section('content')

<body>
    <style>
        
        .btn-cancel {
            background-color: #b03060; /* Magenta/Pink */
            color: white;
        }
        .btn-create {
            background-color: #1f60a7; /* Blue */
            color: white;
        }
        .btn-cancel:hover {
            background-color: #9a2550; /* Slightly darker for hover */
        }
        .btn-create:hover {
            background-color: #174c80;
        }
        .white-section {
            background-color: white;
            padding: 20px;
            border-radius: 8px; /* Optional: Adds rounded corners */
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); /* Optional: Adds shadow */
        }
    </style>

    <div class="container mt-5">
        <div class="white-section"> <!-- White background section -->
            <h3 class="mb-4">Add Additional Bank Account</h3>
            <form action="{{ route('custom-beneficiaries.store') }}" class="mt-3" method="post">
                @csrf
                <div class="row mb-3">
                    <label class="col-sm-2 form-label" >Name </label>
                    <div class="col-sm-10">
                        <input type="text" name="employee_name"
                        class="form-control @error('employee_name') is-invalid @enderror"
                        value="{{ old('employee_name') }}">
                    @error('employee_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                        Give this bank account a label to identify it later
                    </div>
                </div>
               
                <h5 class="text-primary">Bank Account Details</h5>
                <br>
                <div class="row mb-3">
                    <label class="col-sm-2 form-label">Bank</label>
                    <div class="col-sm-10">
                        <select name="bank" class="form-control @error('bank') is-invalid @enderror">
                            <option value="">Select Bank</option>
                            <option value=" ABSA Cash Focus"> ABSA Bank</option>
                            <option value="ABSA Business Integrator(.txt)"> Capitec Bank</option>
                            <option value="ABSA Business Integrator(.csv)"> First National Bank
                            </option>
                            
                        </select>
                        @error('bank')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 form-label">Account number</label>
                    <div class="col-sm-10">
                       <!-- <label class="form-label require">{{ __('Hourly Rate') }}</label>-->
                        <input type="number" name="account_number"
                            class="form-control @error('account_number') is-invalid @enderror"
                            value="{{ old('account_number') }}">
                        @error('account_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 form-label">Branch code</label>
                    <div class="col-sm-10">
                        <input type="number" name="branch_code"
                        class="form-control @error('branch_code') is-invalid @enderror"
                        value="{{ old('branch_code') }}">
                    @error('branch_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 form-label">Account type</label>
                    <div class="col-sm-10">
                        <select name="account_type" class="form-control @error('account_type') is-invalid @enderror">
                            <option value="">Select Account type</option>
                            <option value="Current (cheque)"> Current (cheque)</option>
                            <option value="Savings"> Savings</option>
                            <option value="Transmission"> Transmission
                            </option>
                            
                        </select>
                        @error('account_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                       <!-- <select class="form-select"  style="width:50%">
                            <option selected disabled>Select Account Type</option>
                            <option>Current (Cheque)</option>
                            <option>Transmission</option>
                            <option>Bond</option>
                            <option>Subscription Share</option>
                            <option>Savings</option>
                        </select>-->
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 form-label">Include in Beneficiaries EFT Export</label>
                    <div class="col-sm-10">
                        <input type="checkbox" class="form-check-input" id="include_eftexport" name="include_eftexport">
                    </div>
                </div>
                
                <div class="row mb-3" id="eft_payment_type_div" style="display: none;">
                    <label class="col-sm-2 form-label">EFT Payment Type</label>
                    <div class="col-sm-10">
                        <select id="eft_payment_type" name="eft_payment_type" class="form-control">
                            <option value="">Select Payment Type</option>
                            <option value="each_employee">Payment for Each Employee</option>
                            <option value="lump_sum">Single Lump Sum Payment</option>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-3" id="your_reference_div" style="display: none;">
                    <label class="col-sm-2 form-label">Your Reference</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="your_reference" id="your_reference">
                    </div>
                </div>
                
                <div class="row mb-3" id="beneficiary_reference_div" style="display: none;">
                    <label class="col-sm-2 form-label">Beneficiary Reference</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="beneficiary_reference" id="beneficiary_reference">
                    </div>
                </div>
                <div class="row mb-3 text-start">
                    <div class="col-sm-10">
                        <button type="button" class="btn btn-cancel" onclick="window.location='{{ route('custom-beneficiaries.index') }}'">Cancel</button>

                        <!--<button type="button" href="{{ route('custom-beneficiaries.index') }}" class="btn btn-cancel">Cancel</button>-->
                        <button type="submit" class="btn btn-create">Create</button>
                    </div>
                </div>
            </form>
        </div> <!-- End of white background section -->
    </div>
</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('#include_eftexport').change(function () {
            if ($(this).is(':checked')) {
                $('#eft_payment_type_div').show();
            } else {
                $('#eft_payment_type_div').hide();
                $('#your_reference_div, #beneficiary_reference_div').hide();
                $('#eft_payment_type').val('');
            }
        });

        $('#eft_payment_type').change(function () {
            if ($(this).val() === 'lump_sum') {
                $('#your_reference_div, #beneficiary_reference_div').show();
            } else {
                $('#your_reference_div, #beneficiary_reference_div').hide();
            }
        });
    });
</script>
@endsection
