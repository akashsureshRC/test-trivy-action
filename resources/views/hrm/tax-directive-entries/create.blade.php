@extends('layouts.main')

@section('page-title')
    {{ __('Tax Directive') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Payroll') }},
    {{ __('Tax Directive') }}
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
            <form id="createForm" action="{{ route('tax-directive-entries.store') }}" method="POST">

        @csrf
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Directive Number') }}</label>
                                <input class="form-control @error('directive_number') is-invalid @enderror"
                                       type="text" name="directive_number" value="{{ old('directive_number') }}"
                                       placeholder="{{ __('Enter Directive Number') }}">
                                @error('directive_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Tax Directive') }}</label>
                                <select name="tax_directive_id" id="tax_directive_id"
                                    class="form-control @error('tax_directive_id') is-invalid @enderror">
                                    <option value="">{{ __('Select Tax Directive') }}</option>
                                    <option value="irp3c" {{ old('tax_directive_id') == 'irp3c' ? 'selected' : '' }}>{{ __('Fixed Amount - IRP3(c)') }}</option>
                                    <option value="irp3b" {{ old('tax_directive_id') == 'irp3b' ? 'selected' : '' }}>{{ __('Fixed Percentage - IRP3(b)') }}</option>
                                </select>
                                @error('tax_directive_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Dynamic Input Fields Based on Directive Type -->
                        <div id="directive-fields" style="display: none;">
                            <!-- Fixed Amount (IRP3(c)) -->
                            <div id="fixed-amount-fields" style="display: none;">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label class="form-label">{{ __('Directive Issue Date') }}</label>
                                        <input type="date" name="directive_issue_date" class="form-control @error('directive_issue_date') is-invalid @enderror">
                                        @error('directive_issue_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="form-label">{{ __('Directive Income Source Code') }}</label>
                                        <select name="directive_income_source_code" class="form-control @error('directive_income_source_code') is-invalid @enderror">
                                            <option value="">{{ __('Select Income Source Code') }}</option>
                                            <option value="3707" {{ old('directive_income_source_code') == '3707' ? 'selected' : '' }}>{{ __('3707 - Share options') }}</option>
                                            <option value="3908" {{ old('directive_income_source_code') == '3908' ? 'selected' : '' }}>{{ __('3908 - Exempt policy proceeds') }}</option>
                                        </select>
                                        @error('directive_income_source_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="form-label">{{ __('Directive Income Amount') }}</label>
                                        <input type="number" step="0.01" min="0" name="directive_income_amount" class="form-control @error('directive_income_amount') is-invalid @enderror">
                                        @error('directive_income_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="form-label">{{ __('Amount of Tax to Deduct (from Tax Directive)') }}</label>
                                        <input type="number" step="0.01" min="0" name="amount_of_tax_to_deduct" class="form-control @error('amount_of_tax_to_deduct') is-invalid @enderror">
                                        @error('amount_of_tax_to_deduct')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Fixed Percentage (IRP3(b)) -->
                            <div id="fixed-percentage-fields" style="display: none;">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label class="form-label">{{ __('Percentage') }}</label>
                                        <input type="number" step="0.01" min="0" name="percentage" class="form-control @error('percentage') is-invalid @enderror">
                                        @error('percentage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
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
        $('#createForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();
            $.ajax({
                type: 'POST',
                url: '{{ route("tax-directive-entries.ajax-validate-store") }}',
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