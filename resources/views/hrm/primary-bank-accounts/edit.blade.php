@extends('layouts.main')
@section('page-title')
    {{ __('Create Employee') }}
@endsection
@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Create Employee') }}
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp

@section('content')


<style>
        .card-header {
            background-color: #475866;
            color: white;
            font-weight: bold;
        }
        .btn-cancel {
            background-color: #a33862;
            color: white;
        }
        .btn-cancel:hover {
            background-color: #892d50;
        }
        .btn-save {
            background-color: #2f64b5;
            color: white;
        }
        .btn-save:hover {
            background-color: #255194;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <!-- Primary Bank Account Section -->
    <form action="{{ route('primary-bank-accounts.update',$bankAccount->id) }}" class="mt-3" method="post">
        @csrf
        @method('put')
    <div class="card">
        <div class="card-header">Primary Bank Account</div>
        <div class="card-body">
            <div class="row mb-3">
                <label class="col-md-3 form-label">EFT Format</label>
                <div class="col-md-9">
                    <select name="eft_format" class="form-control @error('eft_format') is-invalid @enderror">
                        <option value="">Select EFT</option>
                        <option value=" ABSA Cash Focus" {{ old('eft_format',$bankAccount->eft_format) == 'ABSA Cash Focus' ? 'selected' : '' }}> ABSA Cash Focus</option>
                        <option value="ABSA Business Integrator(.txt)" {{ old('eft_format',$bankAccount->eft_format) == 'ABSA Business Integrator(.txt)' ? 'selected' : '' }}>  ABSA Business Integrator(.txt)</option>
                        <option value="ABSA Business Integrator(.csv)" {{ old('eft_format',$bankAccount->eft_format) == 'ABSA Business Integrator(.csv)' ? 'selected' : '' }}> ABSA Business Integrator(.csv)
                        </option>
                        
                    </select>
                    @error('eft_format')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <h6 class="fw-bold text-primary">Bank Account Details</h6>
            
            <div class="row mb-3">
                <label class="col-md-3 form-label">Bank</label>
                <div class="col-md-9">
                    <select name="bank" class="form-control @error('bank') is-invalid @enderror">
                        <option value="">Select Bank</option>
                        <option value="ABSA Bank" {{ old('bank',$bankAccount->bank) == 'ABSA Bank' ? 'selected' : '' }}> ABSA Bank</option>
                        <option value="Capitec Bank" {{ old('bank',$bankAccount->bank) == 'Capitec Bank' ? 'selected' : '' }}> Capitec Bank</option>
                        <option value="First National Bank" {{ old('bank',$bankAccount->bank) == 'First National Bank' ? 'selected' : '' }}> First National Bank
                        </option>
                        
                    </select>
                    @error('bank')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-3">
                <label class="col-md-3 form-label">Account number</label>
                <div class="col-md-9">
                   <!-- <label class="form-label require">{{ __('Hourly Rate') }}</label>-->
                    <input type="number" name="account_number"
                        class="form-control @error('account_number') is-invalid @enderror"
                        value="{{ old('account_number',$bankAccount->account_number) }}">
                    @error('account_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-md-3 form-label">Branch code</label>
                <div class="col-md-9">
                    <!-- <label class="form-label require">{{ __('Hourly Rate') }}</label>-->
                    <input type="number" name="branch_code"
                        class="form-control @error('branch_code') is-invalid @enderror"
                        value="{{ old('branch_code',$bankAccount->branch_code) }}">
                    @error('branch_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-md-3 form-label">Account type</label>
                <div class="col-md-9">
                    <select name="account_type" class="form-control @error('account_type') is-invalid @enderror">
                        <option value="">Select Account type</option>
                        <option value="Current (cheque)" {{ old('account_type',$bankAccount->account_type) == 'Current (cheque)' ? 'selected' : '' }}> Current (cheque)</option>
                        <option value="Savings" {{ old('account_type',$bankAccount->account_type) == 'Savings' ? 'selected' : '' }}> Savings</option>
                        <option value="Transmission" {{ old('account_type',$bankAccount->account_type) == 'Transmission' ? 'selected' : '' }}> Transmission
                        </option>
                        
                    </select>
                    @error('account_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-cancel">Cancel</button>
                <button class="btn btn-save">Create</button>
            </div>
        </div>
    </div>

    <!-- Additional Bank Accounts Section -->
    <div class="card mt-4">
        <div class="card-header">Additional Bank Accounts</div>
        <div class="card-body">
            <p>Add additional bank accounts to pay employees from multiple bank accounts</p>
            <button class="btn btn-outline-success">
                <span class="me-1">➕ Add   <a href="/eft/addprimaryeft" class="card-body add-button"></span>
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
@endsection