@extends('layouts.main')
@section('page-title')
    {{ __('Edit Medical Aid') }}
@endsection
@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Edit Medical Aid') }}
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
            <form action="{{ route('add-medical-aid.update',$addmedicalAid->id) }}" class="mt-3" method="post">
                @csrf
                @method('put')
                <div class="row mb-3">
                    <label class="col-sm-2 form-label" >Name </label>
                    <div class="col-sm-10">
                        <input type="text" name="employee_name"
                        class="form-control @error('employee_name') is-invalid @enderror"
                        value="{{ old('employee_name', $addmedicalAid->employee_name) }}">
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
                            <option value="ABSA Bank" {{ old('bank',$addmedicalAid->bank) == 'ABSA Bank' ? 'selected' : '' }}> ABSA Bank</option>
                            <option value="Capitec Bank" {{ old('bank',$addmedicalAid->bank) == 'Capitec Bank' ? 'selected' : '' }}> Capitec Bank</option>
                            <option value="First National Bank" {{ old('bank',$addmedicalAid->bank) == 'First National Bank' ? 'selected' : '' }}> First National Bank
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
                            value="{{ old('account_number',$addmedicalAid->account_number) }}">
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
                        value="{{ old('branch_code',$addmedicalAid->branch_code) }}">
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
                            <option value="Current (cheque)" {{ old('account_type',$addmedicalAid->account_type) == 'Current (cheque)' ? 'selected' : '' }}> Current (cheque)</option>
                            <option value="Savings" {{ old('account_type',$addmedicalAid->account_type) == 'Savings' ? 'selected' : '' }}> Savings</option>
                            <option value="Transmission" {{ old('account_type',$addmedicalAid->account_type) == 'Transmission' ? 'selected' : '' }}> Transmission
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
                            <option value="each_employee" {{ old('eft_payment_type',$addmedicalAid->eft_payment_type) == 'Savings' ? 'selected' : '' }}>Payment for Each Employee</option>
                            <option value="lump_sum" {{ old('eft_payment_type',$addmedicalAid->eft_payment_type) == 'Savings' ? 'selected' : '' }}>Single Lump Sum Payment</option>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-3" id="your_reference_div" style="display: none;">
                    <label class="col-sm-2 form-label">Your Reference</label>
                    <div class="col-sm-10">
                        <!--<input type="text" class="form-control" name="your_reference" id="your_reference">-->
                        <input type="text" name="your_reference" id="your_reference"
                        class="form-control @error('your_reference') is-invalid @enderror"
                        value="{{ old('your_reference',$addmedicalAid->your_reference) }}">
                    @error('your_reference')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    </div>
                </div>
                
                <div class="row mb-3" id="beneficiary_reference_div" style="display: none;">
                    <label class="col-sm-2 form-label">Beneficiary Reference</label>
                    <div class="col-sm-10">
                        <!--<input type="text" class="form-control" name="beneficiary_reference" id="beneficiary_reference">-->
                        <input type="text" name="beneficiary_reference" id="beneficiary_reference"
                        class="form-control @error('beneficiary_reference') is-invalid @enderror"
                        value="{{ old('beneficiary_reference',$addmedicalAid->beneficiary_reference) }}">
                    @error('beneficiary_reference')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    </div>
                </div>
                <div class="row mb-3 text-start">
                    <div class="col-sm-10">
                        <button type="button" class="btn btn-cancel" onclick="window.location='{{ route('custom-beneficiaries.index') }}'">Cancel</button>
                        <button type="submit" class="btn btn-create">Create</button>
                    </div>
                </div>
                <!--<div class="row mb-3 text-start">
                    <div class="col-sm-10">
                        <button type="button" class="btn btn-cancel">Cancel</button>
                        <button type="submit" class="btn btn-create">Create</button>
                    </div>
                </div>-->
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
