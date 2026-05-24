@extends('layouts.main')

@section('page-title')
    {{ __('Subsistence Allowance (Local)') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Payroll') }},
    {{ __('Subsistence (Local)') }}
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
            <form id="createForm" action="{{ route('subsistence-allowances.store') }}" method="POST">

        @csrf
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Costs for Reimbursement') }}</label>
                                <select name="costs_for_reimbursement" class="form-control @error('costs_for_reimbursement') is-invalid @enderror">
                                    <option value="">{{ __('Select an option') }}</option>
                                    <option value="incidental costs only" {{ old('costs_for_reimbursement') == 'incidental costs only' ? 'selected' : '' }}>{{ __('Incidental Costs Only') }}</option>
                                    <option value="meals & incidental costs" {{ old('costs_for_reimbursement') == 'meals & incidental costs' ? 'selected' : '' }}>{{ __('Meals & Incidental Costs') }}</option>
                                </select>
                                @error('costs_for_reimbursement') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Full Amount Paid to Employee') }}</label>
                                <input type="number" step="0.01" min="0" name="full_amount_paid" class="form-control @error('full_amount_paid') is-invalid @enderror"
                                       value="{{ old('full_amount_paid') }}" placeholder="{{ __('Enter Amount') }}">
                                @error('full_amount_paid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Number of Days') }}</label>
                                <input type="number" step="1" min="0" name="number_of_days" class="form-control @error('number_of_days') is-invalid @enderror"
                                       value="{{ old('number_of_days') }}" placeholder="{{ __('Enter Days') }}">
                                @error('number_of_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
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
                url: '{{ route("subsistence-allowances.ajax-validate-store") }}',
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