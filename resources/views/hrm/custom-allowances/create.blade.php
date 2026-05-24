@extends('layouts.main')
@section('page-title')
    {{ __('Add Custom Allowance') }}
@endsection
@section('page-breadcrumb')
    {{ __('Employee') }} > {{ __('Add Custom Allowance') }}
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp

@section('content')
    <style>
        .btn-cancel {
            background-color: #b03060;
            color: white;
        }

        .btn-create {
            background-color: #1f60a7;
            color: white;
        }

        .btn-cancel:hover {
            background-color: #9a2550;
        }

        .btn-create:hover {
            background-color: #174c80;
        }

        .white-section {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>

    <div class="container mt-5">
        <div class="white-section">
            <h3 class="mb-4">Add Custom Allowance</h3>
            <form action="{{ route('custom-allowances.store') }}" class="mt-3" method="POST">
                @csrf
                <div class="row mb-3">
                    <label class="col-sm-2 form-label">Name</label>
                    <div class="col-sm-10">
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-2 form-label">Input Type</label>
                    <div class="col-sm-10">
                        <select id="input_type" name="input_type" class="form-control @error('input_type') is-invalid @enderror">
                            <option value="">Select Input Type</option>
                            <option value="fixed_amount">Fixed Amount</option>
                            <option value="hourly_rate_factor_hours">Hourly rate * factor * hours</option>
                            <option value="custom_rate_quantity">Custom rate * quantity</option>
                            <option value="monthly">Monthly (for non-monthly employees)</option>
                        </select>
                        @error('input_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="affects wage for eti purpose" name="affects wage for eti purpose">
                    <label for="affects wage for eti purpose" class="form-check-label">Affects Wage for ETI Purposes</label>
                </div>

                <!-- Fixed Amount Section -->
                <div id="fixed_amount_section" style="display: none;">
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="enable_pro_rata" name="enable_pro_rata">
                        <label for="enable_pro_rata" class="form-check-label">Enable Pro-Rata</label>
                    </div>
                    <p id="pro_rata_text" style="display: none; color: rgb(17, 14, 14);">Can't be changed if used on a finalized payslip.</p>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" step="0.01" id="amount" name="amount" class="form-control">
                    </div>
                </div>

                <!-- Hourly Rate Section -->
                <div id="hourly_rate_section" style="display: none;">
                    <div class="mb-3">
                        <label for="rate_factor" class="form-label">Rate Factor</label>
                        <input type="number" step="0.01" id="rate_factor" name="rate_factor" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="employee_work_factor" class="form-label">Hours Worked Factor</label>
                        <input type="number" step="0.01" id="employee_work_factor" name="employee_work_factor" class="form-control">
                    </div>
                </div>

                <!-- Custom Rate Section -->
                <div id="custom_rate_section" style="display: none;">
                    <div class="mb-3">
                        <label for="hours_work_factor" class="form-label">Hours Worked Factor</label>
                        <input type="number" step="0.01" id="hours_work_factor" name="hours_work_factor" class="form-control">
                    </div>
                    <div id="custom_rate_input_section">
                        <div class="mb-3">
                            <label for="custom_rate" class="form-label">Custom Rate</label>
                            <input type="number" step="0.01" id="custom_rate" name="custom_rate" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="different_rate_for_every_employee" name="different_rate_for_every_employee">
                        <label for="different_rate_for_every_employee" class="form-check-label">Different rate for every employee</label>
                    </div>
                </div>

                <!-- Monthly Section -->
                <div id="monthly_section" style="display: none;">
                    <div class="mb-3">
                        <label for="monthly_amount" class="form-label">Amount</label>
                        <input type="number" step="0.01" id="monthly_amount" name="monthly_amount" class="form-control">
                    </div>
                </div>

                <div class="row mb-3 text-start">
                    <div class="col-sm-10">
                        <button type="button" class="btn btn-cancel">Cancel</button>
                        <button type="submit" class="btn btn-create">Create</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('input_type').addEventListener('change', function () {
            var selectedValue = this.value;

            document.getElementById('fixed_amount_section').style.display = 'none';
            document.getElementById('hourly_rate_section').style.display = 'none';
            document.getElementById('custom_rate_section').style.display = 'none';
            document.getElementById('monthly_section').style.display = 'none';

            if (selectedValue === 'fixed_amount') {
                document.getElementById('fixed_amount_section').style.display = 'block';
            } else if (selectedValue === 'hourly_rate_factor_hours') {
                document.getElementById('hourly_rate_section').style.display = 'block';
            } else if (selectedValue === 'custom_rate_quantity') {
                document.getElementById('custom_rate_section').style.display = 'block';
            } else if (selectedValue === 'monthly') {
                document.getElementById('monthly_section').style.display = 'block';
            }
        });

        document.getElementById('different_rate_for_every_employee').addEventListener('change', function () {
            var customRateInput = document.getElementById('custom_rate_input_section');
            customRateInput.style.display = this.checked ? 'none' : 'block';
        });
    </script>
@endsection
