@extends('layouts.main')

@section('page-title')
    {{ __('Employer Loan') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Payroll') }},
    {{ __('Employer Loan') }}
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
            <form id="createForm" action="{{ route('employer-loans.store') }}" method="POST">

        @csrf
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Interest Rate') }}</label>
                                <input class="form-control @error('interest_rate') is-invalid @enderror"
                                       type="number" step="0.01" min="0" name="interest_rate" value="{{ old('interest_rate') }}"
                                       placeholder="{{ __('Enter Amount') }}">
                                @error('interest_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Regular Repayment') }}</label>
                                <input class="form-control @error('regular_repayment') is-invalid @enderror"
                                       type="number" step="0.01" min="0" name="regular_repayment" value="{{ old('regular_repayment') }}"
                                       placeholder="{{ __('Enter Amount') }}">
                                @error('regular_repayment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input type="hidden" name="calculate_interest_benefit" value="0">
                                    <input type="checkbox" class="form-check-input" name="calculate_interest_benefit" value="1"
                                        {{ old('calculate_interest_benefit') ? 'checked' : '' }} id="calculate_interest_benefit">
                                    <label class="form-check-label" for="calculate_interest_benefit">{{ __('Calculate Interest Benefit') }}</label>
                                </div>
                                <p class="text-muted small mt-1">{{ __('The Balance Increase and Once-off Repayment may be entered under Payslip Inputs') }}</p>
                            </div>
                            <div class="form-group col-md-6" id="interest_benefit_amount_wrapper" style="display: none;">
                                <label class="form-label">{{ __('Interest Benefit Amount') }}</label>
                                <input class="form-control @error('interest_benefit_amount') is-invalid @enderror"
                                       type="number" step="0.01" min="0" name="interest_benefit_amount"
                                        readonly
                                       value="{{ old('interest_benefit_amount', 0) }}"
                                       placeholder="{{ __('Enter Amount') }}">
                                @error('interest_benefit_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
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
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        function calculateInterestBenefitAmount() {
            var checked = $('#calculate_interest_benefit').is(':checked');
            var loanAmount = parseFloat($('input[name="regular_repayment"]').val() || 0);
            var interestRate = parseFloat($('input[name="interest_rate"]').val() || 0);
            var monthlyInterestBenefit = checked ? ((loanAmount * (interestRate / 100)) / 12) : 0;
            $('input[name="interest_benefit_amount"]').val(monthlyInterestBenefit.toFixed(2));
        }

        function toggleInterestBenefitAmount() {
            if ($('#calculate_interest_benefit').is(':checked')) {
                $('#interest_benefit_amount_wrapper').show();
            } else {
                $('#interest_benefit_amount_wrapper').hide();
                $('input[name="interest_benefit_amount"]').val(0);
            }

            calculateInterestBenefitAmount();
        }

        toggleInterestBenefitAmount();
        $('#calculate_interest_benefit').on('change', toggleInterestBenefitAmount);
        $('input[name="regular_repayment"], input[name="interest_rate"]').on('input', calculateInterestBenefitAmount);

        $('#createForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();
            $.ajax({
                type: 'POST',
                url: '{{ route("employer-loans.ajax-validate-store") }}',
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