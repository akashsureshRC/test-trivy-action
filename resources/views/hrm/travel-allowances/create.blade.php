@extends('layouts.main')

@section('page-title')
{{ __('Travel Allowance') }}
@endsection

@section('page-breadcrumb')
{{ __('Employee') }},
{{ __('Payroll') }},
{{ __('Travel Allowance') }}
@endsection

@section('page-action')
<div>
    <a href="{{ route('payroll.index', ['employee_id' => $employee->id, 'term' => $term]) }}" class="btn btn-rc-outline btn-sm">
        <i class="ti ti-arrow-left"></i> {{ __('Back to Payroll') }}
    </a>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <form action="{{ route('travel-allowances.store') }}" method="POST" id="createForm">

            @csrf
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <input type="hidden" name="fixed_allowance" value="0">
                                <input type="checkbox" class="form-check-input" id="fixed_allowance" name="fixed_allowance" value="1">
                                <label class="form-check-label" for="fixed_allowance">{{ __('Fixed allowance paid regularly') }}</label>
                                <input type="number" step="0.01" id="fixed_amount" name="fixed_amount" class="form-control mt-2 d-none"
                                    placeholder="{{ __('Enter fixed amount') }}">
                                @error('fixed_amount')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <input type="hidden" name="reimbursed_expenses" value="0">
                                <input type="checkbox" class="form-check-input" id="reimbursed_expenses" name="reimbursed_expenses" value="1">
                                <label class="form-check-label" for="reimbursed_expenses">{{ __('Reimbursed for expenses (petrol, garage, maintenance, etc.)') }}</label>
                                <p class="text-muted mt-2 d-none" id="expense_prompt">{{ __('You will be prompted on every payslip for the Expenses') }}</p>
                            </div>

                            <div class="form-group">
                                <input type="hidden" name="company_petrol_card" value="0">
                                <input type="checkbox" class="form-check-input" id="company_petrol_card" name="company_petrol_card" value="1">
                                <label class="form-check-label" for="company_petrol_card">{{ __('Company Petrol Card (not paid out)') }}</label>
                                <p class="text-muted mt-2 d-none" id="petrol_card_prompt">{{ __('You will be prompted on every payslip for the Petrol card spend') }}</p>
                            </div>

                            <div class="form-group">
                                <input type="hidden" name="reimbursed_per_km" value="0">
                                <input type="checkbox" class="form-check-input" id="reimbursed_per_km" name="reimbursed_per_km" value="1">
                                <label class="form-check-label" for="reimbursed_per_km">{{ __('Reimbursed per Km travelled') }}</label>
                                <input type="number" step="0.01" id="rate_per_km" name="rate_per_km" class="form-control mt-2 d-none"
                                    placeholder="{{ __('Enter Rate per Km') }}">
                                <p class="text-muted mt-2 d-none" id="km_prompt">{{ __('You will be prompted on every payslip for the Kms travelled') }}</p>
                            </div>

                            <div class="form-group">
                                <input type="hidden" name="subject_to_20_tax" value="0">
                                <input type="checkbox" class="form-check-input" id="subject_to_20_tax" name="subject_to_20_tax" value="1">
                                <label class="form-check-label" for="subject_to_20_tax">{{ __('Only 20% Subject to Tax') }}</label>
                            </div>

                            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                            <input type="hidden" name="term" value="{{ $term }}">

                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <a class="btn btn-rc-outline" href="{{ route('payroll.index', ['employee_id' => $employee->id, 'term' => $term]) }}">
                                    {{ __('Cancel') }}
                                </a>
                                <button type="submit" class="btn btn-rc-primary">{{ __('Submit') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fixedAllowance = document.getElementById('fixed_allowance');
        const fixedAmount = document.getElementById('fixed_amount');
        const reimbursedExpenses = document.getElementById('reimbursed_expenses');
        const expensePrompt = document.getElementById('expense_prompt');
        const companyPetrolCard = document.getElementById('company_petrol_card');
        const petrolCardPrompt = document.getElementById('petrol_card_prompt');
        const reimbursedPerKm = document.getElementById('reimbursed_per_km');
        const ratePerKm = document.getElementById('rate_per_km');
        const kmPrompt = document.getElementById('km_prompt');

        fixedAllowance.addEventListener('change', function() {
            fixedAmount.classList.toggle('d-none', !this.checked);
        });

        reimbursedExpenses.addEventListener('change', function() {
            expensePrompt.classList.toggle('d-none', !this.checked);
        });

        companyPetrolCard.addEventListener('change', function() {
            petrolCardPrompt.classList.toggle('d-none', !this.checked);
        });

        reimbursedPerKm.addEventListener('change', function() {
            ratePerKm.classList.toggle('d-none', !this.checked);
            kmPrompt.classList.toggle('d-none', !this.checked);
        });
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
                url: '{{ route("travel-allowances.ajax-validate-store") }}',
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