@extends('layouts.main')
@section('page-title')
    {{ __('Add Custom Deduction') }}
@endsection
@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Add Custom Deduction') }}
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
                <form id="createForm" action="{{ route('custom-deductions.store') }}" class="mt-3" method="post">
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
                   
                    
                      <!-- Input Type Dropdown -->
                      <div class="row mb-3">
                        <label class="col-sm-2 form-label"  for="input_type">Input Type </label>
                        <div class="col-sm-10">
                       
                        <select id="input_type" name="input_type" class="form-control" required>
                          <option value="">Select Input Type</option>
                          <option value="fixed_amount">Fixed Amount</option>
                          <option value="enter_amount_per_employee">Enter Amount Per Employee</option>
                          <option value="different_on_every_payslip">Different on Every Payslip</option>
                          <option value="once_off">Once-off for Specified Payslips</option>
                          <option value="hourly_rate_factor_hours">Hourly rate * factor * hours</option>
                          <option value="custom_rate_quantity">Custom rate * quantity</option>
                          <option value="percentage_income">% of Income</option>
                          <option value="formula">Formula</option>
                          <option value="monthly">Monthly (for non-monthly employees)</option>
                        </select>
                      </div></div>
                    
                      <!-- Default Checkboxes (Always Visible) -->
                      <!--<div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="exclude_from_accounting" name="exclude_from_accounting" checked>
                        <label for="exclude_from_accounting" class="form-check-label">Exclude from Accounting</label>
                      </div>-->
                      <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="bcea deduction" name="bcea deduction"  checked>
                        <label for="bcea deduction" class="form-check-label">BCEA deduction (Do not deduct from ETI Remuneration) </label>
                      </div>
                    
                     
                      <div id="fixed_amount_section" style="display: none;">
                       
                        <div class="mb-3 form-check">
                          <input type="checkbox" class="form-check-input" id="enable_pro_rata" name="enable_pro_rata" >
                          <label for="enable_pro_rata" class="form-check-label">Enable Pro-Rata</label>
                        </div>
                        <p>Can't be changed if used on a finalised payslip.</p>
                        <div class="mb-3" id="amount_input_section">
                          <label for="amount_fixed" class="form-label">Amount</label>
                          <input type="number" step="0.01" id="amount" name="amount" class="form-control">
                        </div>
                      </div>
                    
                     
                      <div id="enter_amount_section" style="display: none;">
                        <!--<p>Amount will be entered per employee on the payslip.</p>-->
                      </div>
                    
                    
                      <div id="different_payslip_section" style="display: none;">
                        <!--<p>No additional amount input required for this option.</p>-->
                      </div>
                    
                      
                      <div id="hourly_rate_section" style="display: none;">
                        <div class="mb-3">
                          <label for="rate_factor" class="form-label">Rate Factor (e.g., 1.5 for 1.5x hourly rate)</label>
                          <input type="number" step="0.01" id="rate_factor" name="rate_factor" class="form-control">
                        </div>
                      </div>
                    
                      
                      <div id="custom_rate_section" style="display: none;">
                        <div class="mb-3 form-check">
                          <input type="checkbox" class="form-check-input" id="different_rate_employee" name="different_rate_for_every_employee">
                          <label for="different_rate_employee" class="form-check-label">Different rate for every employee</label>
                        </div>
                        <div class="mb-3" id="custom_rate_input_div">
                          <label for="custom_rate" class="form-label">Custom Rate</label>
                          <input type="number" step="0.01" id="custom_rate" name="custom_rate" class="form-control">
                        </div>
                      </div>
                    
                     
                      <div id="percentage_income_section" style="display: none;">
                        <div class="mb-3">
                          <label for="percentage_income" class="form-label">Percentage of Income</label>
                          <input type="number" step="0.01" id="percentage_income" name="percentage_income" class="form-control">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Select Income Items</label><br>
                          <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="selected_income_items[]" value="Basic Salary"> Basic Salary
                          </label><br>
                          <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="selected_income_items[]" value="Basic Hourly Pay"> Basic Hourly Pay
                          </label><br>
                          <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="selected_income_items[]" value="Overtime"> Overtime
                          </label><br>
                          <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="selected_income_items[]" value="Short Time"> Short Time
                          </label><br>
                          <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="selected_income_items[]" value="Sunday Pay"> Sunday Pay
                          </label><br>
                          <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="selected_income_items[]" value="Sunday Overtime"> Sunday Overtime
                          </label><br>
                          <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="selected_income_items[]" value="Public Holiday - Worked"> Public Holiday - Worked
                          </label><br>
                          <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="selected_income_items[]" value="Public Holiday - Not Worked"> Public Holiday - Not Worked
                          </label><br>
                          <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="selected_income_items[]" value="Annual Leave Pay"> Annual Leave Pay
                          </label><br>
                          <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="selected_income_items[]" value="Sick Leave Pay"> Sick Leave Pay
                          </label><br>
                          <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="selected_income_items[]" value="Family Responsibility Pay"> Family Responsibility Pay
                          </label><br>
                          <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="selected_income_items[]" value="Annual Leave Pay Extra"> Annual Leave Pay Extra
                          </label><br>
                          <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="selected_income_items[]" value="Unpaid Leave"> Unpaid Leave
                          </label><br>
                        </div>
                      </div>
                    
                    
                      <div id="formula_section" style="display: none;">
                        <div class="mb-3">
                          <label for="formula" class="form-label">Formula</label>
                          <input type="text" id="formula" name="formula" class="form-control">
                        </div>
                      </div>
                    
                     
                      <div id="monthly_section" style="display: none;">
                        <div class="mb-3">
                          <label for="monthly_amount" class="form-label">Amount</label>
                          <input type="number" step="0.01" id="monthly_amount" name="amount" class="form-control">
                        </div>
                      </div>
                    
                    <div class="row mb-3 text-start">
                        <div class="col-sm-10">

                            <button type="button" class="btn btn-cancel"
                                onclick="window.location='{{ route('custom-beneficiaries.index') }}'">Cancel</button>
                            <button type="submit" class="btn btn-create">Create</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </body>
    <script>
        // Listen for changes on the Input Type dropdown
        document.getElementById('input_type').addEventListener('change', function() {
          var val = this.value;
          
          // Hide all dynamic sections initially
          document.getElementById('fixed_amount_section').style.display = 'none';
          document.getElementById('enter_amount_section').style.display = 'none';
          document.getElementById('different_payslip_section').style.display = 'none';
          document.getElementById('hourly_rate_section').style.display = 'none';
          document.getElementById('custom_rate_section').style.display = 'none';
          document.getElementById('percentage_income_section').style.display = 'none';
          document.getElementById('formula_section').style.display = 'none';
          document.getElementById('monthly_section').style.display = 'none';
      
          // Show sections based on selected option
          if (val === 'fixed_amount') {
            document.getElementById('fixed_amount_section').style.display = 'block';
          } else if (val === 'enter_amount_per_employee') {
            document.getElementById('enter_amount_section').style.display = 'block';
          } else if (val === 'different_on_every_payslip') {
            document.getElementById('different_payslip_section').style.display = 'block';
          } else if (val === 'hourly_rate_factor_hours') {
            document.getElementById('hourly_rate_section').style.display = 'block';
          } else if (val === 'custom_rate_quantity') {
            document.getElementById('custom_rate_section').style.display = 'block';
          } else if (val === 'percentage_income') {
            document.getElementById('percentage_income_section').style.display = 'block';
          } else if (val === 'formula') {
            document.getElementById('formula_section').style.display = 'block';
          } else if (val === 'monthly') {
            document.getElementById('monthly_section').style.display = 'block';
          }
        });
      
        // Handle custom rate checkbox toggle for "Custom rate * quantity"
        document.getElementById('different_rate_employee').addEventListener('change', function() {
          var customRateInputDiv = document.getElementById('custom_rate_input_div');
          if (this.checked) {
            customRateInputDiv.style.display = 'none';
          } else {
            customRateInputDiv.style.display = 'block';
          }
        });

        document.getElementById('pro_data_checkbox').addEventListener('change', function() {
    let amountSection = document.getElementById('amount_input_section');

    if (this.checked) {
        amountSection.style.display = 'none'; // Hide amount input box
    } else {
        amountSection.style.display = 'block'; // Show amount input box
    }
});
      </script>
  <script>
    document.getElementById('input_type').addEventListener('change', function() {
        var fixedAmountDiv = document.getElementById('fixed_amount_options');
        if (this.value === 'fixed_amount') {
            fixedAmountDiv.style.display = 'block';
        } else if (this.value === 'enter_amount_per_employee') {
            // Hide fixed amount options if "Enter Amount Per Employee" is selected.
            fixedAmountDiv.style.display = 'none';
        } else {
            fixedAmountDiv.style.display = 'none';
        }
    });
</script>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#createForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();
            $.ajax({
                type: 'POST',
                url: '{{ route("custom-deductions.ajax-validate-store") }}',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        form.off('submit');
                        form[0].submit();
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    $('.error-text').remove();
                    $('input, select, textarea').removeClass('is-invalid');
                    $.each(errors, function(field, messages) {
                        var input = $('[name="' + field + '"]');
                        input.addClass('is-invalid');
                        input.after('<span class="text-danger error-text">' + messages[0] + '</span>');
                    });
                }
            });
        });
    });
</script>
@endpush
