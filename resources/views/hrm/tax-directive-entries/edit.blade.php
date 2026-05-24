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
        <a href="{{ route('payroll.index', ['employee_id' => $taxDirectiveEntry->employee_id, 'term' => $term]) }}" class="btn btn-rc-outline btn-sm">
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
            <form id="editForm" action="{{ route('tax-directive-entries.update', $taxDirectiveEntry) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-12 col-xs-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <!-- Hidden employee_id field -->
                                <input type="hidden" id="employee_id_hidden" name="employee_id" value="{{ old('employee_id', $taxDirectiveEntry->employee_id) }}">
                                
                                <!-- Directive Number -->
                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Directive Number') }}</label>
                                    <input class="form-control @error('directive_number') is-invalid @enderror"
                                        type="text" name="directive_number" value="{{ old('directive_number', $taxDirectiveEntry->directive_number) }}"
                                        placeholder="{{ __('Enter Directive Number') }}">
                                    @error('directive_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Tax Directive Dropdown -->
                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Tax Directive') }}</label>
                                    <select name="tax_directive_id" id="tax_directive_id"
                                        class="form-control @error('tax_directive_id') is-invalid @enderror">
                                        <option value="">{{ __('Select Tax Directive') }}</option>
                                        <option value="irp3c" {{ old('tax_directive_id', $taxDirectiveEntry->tax_directive_id) == 'irp3c' ? 'selected' : '' }}>
                                            Fixed Amount - IRP3(c)
                                        </option>
                                        
                                        <option value="irp3pa" {{ old('tax_directive_id', $taxDirectiveEntry->tax_directive_id) == 'irp3pa' ? 'selected' : '' }}>
                                            Fixed Percentage - IRP3(pa)
                                        </option>
                                    </select>
                                    @error('tax_directive_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Dynamic Input Fields Based on Directive Type -->
                            <div id="directive-fields" class="row" style="display: none;">
                                <!-- Fixed Amount (IRP3(c)) Fields -->
                                <div id="fixed-amount-fields" style="display: none;">
                                    <div class="form-group col-md-6">
                                        <label>{{ __('Directive Issue Date') }}</label>
                                        <input type="date" name="directive_issue_date" id="directive_issue_date"
                                            class="form-control @error('directive_issue_date') is-invalid @enderror"
                                            value="{{ old('directive_issue_date', $taxDirectiveEntry->directive_issue_date) }}">
                                        @error('directive_issue_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>{{ __('Directive Income Source Code') }}</label>
                                        <select name="directive_income_source_code" id="directive_income_source_code"
                                            class="form-control @error('directive_income_source_code') is-invalid @enderror">
                                            <option value="">{{ __('Select Income Source Code') }}</option>
                                            <option value="3707" {{ old('directive_income_source_code', $taxDirectiveEntry->directive_income_source_code) == '3707' ? 'selected' : '' }}>
                                                3707 - Share options
                                            </option>
                                            <option value="3908" {{ old('directive_income_source_code', $taxDirectiveEntry->directive_income_source_code) == '3908' ? 'selected' : '' }}>
                                                3908 - Exempt policy proceeds
                                            </option>
                                        </select>
                                        @error('directive_income_source_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>{{ __('Directive Income Amount') }}</label>
                                        <input type="number" name="directive_income_amount" id="directive_income_amount"
                                            class="form-control @error('directive_income_amount') is-invalid @enderror"
                                            value="{{ old('directive_income_amount', $taxDirectiveEntry->directive_income_amount) }}">
                                        @error('directive_income_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>{{ __('Amount of Tax to Deduct (from Tax Directive)') }}</label>
                                        <input type="number" name="amount_of_tax_to_deduct" id="amount_of_tax_to_deduct"
                                            class="form-control @error('amount_of_tax_to_deduct') is-invalid @enderror"
                                            value="{{ old('amount_of_tax_to_deduct', $taxDirectiveEntry->amount_of_tax_to_deduct) }}">
                                        @error('amount_of_tax_to_deduct')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Fixed Percentage (IRP3(b) & IRP3(pa)) Fields -->
                                <div id="fixed-percentage-fields" style="display: none;">
                                    <div class="form-group col-md-6">
                                        <label>{{ __('Percentage') }}</label>
                                        <input type="number" name="percentage" id="percentage"
                                            class="form-control @error('percentage') is-invalid @enderror"
                                            step="0.01"
                                            value="{{ old('percentage', $taxDirectiveEntry->percentage) }}">
                                        @error('percentage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="term" value="{{ $term }}">
                            <!-- Submit & Cancel Buttons -->
                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <a class="btn btn-rc-outline" href="{{ route('payroll.index', ['employee_id' => $taxDirectiveEntry->employee_id]) }}">
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
                url: '{{ route("tax-directive-entries.ajax-validate-update", $taxDirectiveEntry->id) }}',
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