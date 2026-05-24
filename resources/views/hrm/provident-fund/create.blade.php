@extends('layouts.main')

@section('page-title')
    {{ __('Provident Fund') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Payroll') }},
    {{ __('Provident Fund') }}
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
            <form id="createForm" action="{{ route('provident-fund.store') }}" method="POST">

        @csrf
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="form-group col-md-6">
                            <label class="require form-label">{{ __('Beneficiary') }}</label>
                            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                            <input class="form-control" value="{{ $employee->first_name }} {{ $employee->last_name }}" readonly>
                        </div>

                        <div class="form-group col-md-6">
                            <label class="require form-label">{{ __('Contribution Calculation') }}</label>
                            <select class="form-control @error('contribution') is-invalid @enderror" name="contribution" id="contribution">
                                <option value="">{{ __('Select Calculation') }}</option>
                                <option value="fixed_amount" {{ old('contribution') == 'fixed_amount' ? 'selected' : '' }}>{{ __('Fixed Amount') }}</option>
                                <option value="percentage_rfi" {{ old('contribution') == 'percentage_rfi' ? 'selected' : '' }}>{{ __('% of Retirement Funding Income') }}</option>
                            </select>
                            @error('contribution')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Fixed Amount Inputs -->
                        <div id="fixed_contribution_section" style="display: none;">
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Fixed Contribution by Employee') }}</label>
                                <input class="form-control @error('fixed_contribution_employee') is-invalid @enderror"
                                       type="number" step="0.01" min="0" name="fixed_contribution_employee"
                                       value="{{ old('fixed_contribution_employee') }}" placeholder="{{ __('Enter Employee Amount') }}">
                                @error('fixed_contribution_employee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Fixed Contribution by Employer') }}</label>
                                <input class="form-control @error('fixed_contribution_employer') is-invalid @enderror"
                                       type="number" step="0.01" min="0" name="fixed_contribution_employer"
                                       value="{{ old('fixed_contribution_employer') }}" placeholder="{{ __('Enter Employer Amount') }}">
                                @error('fixed_contribution_employer')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Percentage RFI Inputs -->
                        <div id="percentage_rfi_section" style="display: none;">
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('% of RFI - Employee') }}</label>
                                <input class="form-control @error('percentage_rfi_employee') is-invalid @enderror"
                                       type="number" step="0.01" min="0" max="100" name="percentage_rfi_employee"
                                       value="{{ old('percentage_rfi_employee') }}" placeholder="{{ __('Enter Employee Percentage') }}">
                                @error('percentage_rfi_employee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('% of RFI - Employer') }}</label>
                                <input class="form-control @error('percentage_rfi_employer') is-invalid @enderror"
                                       type="number" step="0.01" min="0" max="100" name="percentage_rfi_employer"
                                       value="{{ old('percentage_rfi_employer') }}" placeholder="{{ __('Enter Employer Percentage') }}">
                                @error('percentage_rfi_employer')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Category Factor') }}</label>
                                <input class="form-control @error('category') is-invalid @enderror"
                                       type="number" step="0.01" min="0" name="category" value="{{ old('category') }}"
                                       placeholder="{{ __('Enter Amount') }}">
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <p class="text-muted">{{ __('Optional, only enter if provided by fund.') }}</p>
                        <input type="hidden" name="employee_id" value="{{ request()->get('employee_id') }}">
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
        $('#createForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();
            $.ajax({
                type: 'POST',
                url: '{{ route("provident-fund.ajax-validate-store") }}',
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
                        var input = $('[name="' + field + '"');
                        input.addClass('is-invalid');
                        input.after('<span class="text-danger error-text">' + messages[0] + '</span>');
                    });
                }
            });
        });
    });
</script>
@endpush