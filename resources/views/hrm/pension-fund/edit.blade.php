@extends('layouts.main')

@section('page-title')
    {{ __('Pension Fund') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Payroll') }},
    {{ __('Pension Fund') }}
@endsection

@section('page-action')
    <div>
        <a href="{{ route('payroll.index', ['employee_id' => $pensionFund->employee_id, 'term' => $term]) }}" class="btn btn-rc-outline btn-sm">
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
            <form action="{{ route('pension-fund.update', $employee) }}" method="POST" id="editForm">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-12 col-xs-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Beneficiary') }}</label>
                                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                <input class="form-control @error('employee_id') is-invalid @enderror" id="employee_id" value="{{ $employee->first_name }} {{ $employee->last_name }}" readonly>
                                @error('employee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Pension Calculation') }}</label>
                                <select class="form-control @error('pension') is-invalid @enderror" name="pension" id="pension_calculation">
                                    <option value="">Select Calculation</option>
                                    <option value="fixed_amount" {{ old('pension', $pensionFund->pension) == 'fixed_amount' ? 'selected' : '' }}>Fixed Amount</option>
                                    <option value="percentage_rfi" {{ old('pension', $pensionFund->pension) == 'percentage_rfi' ? 'selected' : '' }}>% of Retirement Funding Income</option>
                                </select>
                                @error('pension')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fixed Amount Inputs -->
                            <div id="fixed_contribution_section" style="display: {{ $pensionFund->pension == 'fixed_amount' ? 'block' : 'none' }};">
                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Fixed Contribution by Employee') }}</label>
                                    <input class="form-control @error('fixed_contribution_employee') is-invalid @enderror" type="number" step="0.01" min="0" name="fixed_contribution_employee" value="{{ old('fixed_contribution_employee', $pensionFund->fixed_contribution_employee) }}" placeholder="{{ __('Enter Employee Amount') }}">
                                    @error('fixed_contribution_employee')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Fixed Contribution by Employer') }}</label>
                                    <input class="form-control @error('fixed_contribution_employer') is-invalid @enderror" type="number" step="0.01" min="0" name="fixed_contribution_employer" value="{{ old('fixed_contribution_employer', $pensionFund->fixed_contribution_employer) }}" placeholder="{{ __('Enter Employer Amount') }}">
                                    @error('fixed_contribution_employer')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Percentage RFI Inputs -->
                            <div id="percentage_rfi_section" style="display: {{ $pensionFund->pension == 'percentage_rfi' ? 'block' : 'none' }};">
                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('% of RFI - Employee') }}</label>
                                    <input class="form-control @error('percentage_rfi_employee') is-invalid @enderror" type="number" step="0.01" min="0" max="100" name="percentage_rfi_employee" value="{{ old('percentage_rfi_employee', $pensionFund->percentage_rfi_employee) }}" placeholder="{{ __('Enter Employee Percentage') }}">
                                    @error('percentage_rfi_employee')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('% of RFI - Employer') }}</label>
                                    <input class="form-control @error('percentage_rfi_employer') is-invalid @enderror" type="number" step="0.01" min="0" max="100" name="percentage_rfi_employer" value="{{ old('percentage_rfi_employer', $pensionFund->percentage_rfi_employer) }}" placeholder="{{ __('Enter Employer Percentage') }}">
                                    @error('percentage_rfi_employer')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Category Factor') }}</label>
                                    <input class="form-control @error('category') is-invalid @enderror" type="number" name="category" value="{{ old('category', $pensionFund->category) }}" placeholder="{{ __('Enter Amount') }}">
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <p>Optional, only enter if provided by fund.</p>
<input type="hidden" name="term" value="{{ $term }}">
                            <div class="d-flex justify-content-end gap-2">
                                <a class="btn btn-rc-outline" href="{{ route('payroll.index', ['employee_id' => $pensionFund->employee_id]) }}">
                                    {{ __('Cancel') }}
                                </a>
                                <button class="btn btn-rc-primary" type="submit">{{ __('Update') }}</button>
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
        $('#editForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();
            $.ajax({
                type: 'POST',
                url: '{{ route("pension-fund.ajax-validate-update", $pensionFund->id) }}',
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