@extends('layouts.main')
@section('page-title')
    {{ __('Edit Additional Bank Account') }}
@endsection
@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Edit Additional Bank Account') }}
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
            <form action="{{ route('additional-bank-accounts.update',$bankaccount->id) }}" class="mt-3" method="post">
                @csrf
                @method('put')
                <div class="row mb-3">
                    <label class="col-sm-2 form-label" >Name </label>
                    <div class="col-sm-10">
                        <input type="text" name="employee_name"
                        class="form-control @error('employee_name') is-invalid @enderror"
                        value="{{ old('employee_name', $bankaccount->employee_name) }}">
                    @error('employee_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                        Give this bank account a label to identify it later
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 form-label">EFT Format</label>
                    <div class="col-sm-10">
                        <select name="eft_format" class="form-control @error('eft_format') is-invalid @enderror">
                            <option value="">Select EFT</option>
                            <option value=" ABSA Cash Focus" {{ old('eft_format',$bankaccount->eft_format) == 'ABSA Cash Focus' ? 'selected' : '' }}> ABSA Cash Focus</option>
                            <option value="ABSA Business Integrator(.txt)" {{ old('eft_format',$bankaccount->eft_format) == 'ABSA Business Integrator(.txt)' ? 'selected' : '' }}>  ABSA Business Integrator(.txt)</option>
                            <option value="ABSA Business Integrator(.csv)" {{ old('eft_format',$bankaccount->eft_format) == 'ABSA Business Integrator(.csv)' ? 'selected' : '' }}> ABSA Business Integrator(.csv)
                            </option>
                            
                        </select>
                        @error('eft_format')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <h5 class="text-primary">Bank Account Details</h5>
                <br>
                <div class="row mb-3">
                    <label class="col-sm-2 form-label">Bank</label>
                    <div class="col-sm-10">
                        <select name="bank" class="form-control @error('bank') is-invalid @enderror">
                            <option value="">Select Bank</option>
                            <option value="ABSA Bank" {{ old('bank',$bankaccount->bank) == 'ABSA Bank' ? 'selected' : '' }}> ABSA Bank</option>
                            <option value="Capitec Bank" {{ old('bank',$bankaccount->bank) == 'Capitec Bank' ? 'selected' : '' }}> Capitec Bank</option>
                            <option value="First National Bank" {{ old('bank',$bankaccount->bank) == 'First National Bank' ? 'selected' : '' }}> First National Bank
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
                            value="{{ old('account_number',$bankaccount->account_number) }}">
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
                        value="{{ old('branch_code',$bankaccount->branch_code) }}">
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
                            <option value="Current (cheque)" {{ old('account_type',$bankaccount->account_type) == 'Current (cheque)' ? 'selected' : '' }}> Current (cheque)</option>
                            <option value="Savings" {{ old('account_type',$bankaccount->account_type) == 'Savings' ? 'selected' : '' }}> Savings</option>
                            <option value="Transmission" {{ old('account_type',$bankaccount->account_type) == 'Transmission' ? 'selected' : '' }}> Transmission
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
            
                <div class="row mb-3 text-start">
                    <div class="col-sm-10">
                        <button type="button" class="btn btn-cancel">Cancel</button>
                        <button type="submit" class="btn btn-create">Create</button>
                    </div>
                </div>
            </form>
        </div> <!-- End of white background section -->
    </div>
</body>

@endsection
