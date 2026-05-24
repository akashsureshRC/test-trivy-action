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
        <a href="{{ route('payroll.index', ['employee_id' => $subsistenceAllowance->employee_id, 'term' => $term]) }}" class="btn btn-rc-outline btn-sm">
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
            <form id="editForm" action="{{ route('subsistence-allowances.update', $subsistenceAllowance) }}" method="POST">
 $subsistenceAllowance->id]) }}" method="POST"
        class="mt-3">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="require form-label">{{ __('Costs for Reimbursement') }}</label>
                        <select name="costs_for_reimbursement" class="form-control">
                            <option value="incidental costs only"
                                {{ $subsistenceAllowance->costs_for_reimbursement == 'incidental costs only' ? 'selected' : '' }}>
                                Incidental Costs Only</option>
                            <option value="meals & incidental costs"
                                {{ $subsistenceAllowance->costs_for_reimbursement == 'meals & incidental costs' ? 'selected' : '' }}>
                                Meals & Incidental Costs</option>
                        </select>
                        @error('paid_to_employee')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label class="require form-label">{{ __('Full Amount Paid to Employee') }}</label>
                        <input type="text" name="full_amount_paid"
                            class="form-control @error('full_amount_paid') is-invalid @enderror"
                            value="{{ old('full_amount_paid', $subsistenceAllowance->full_amount_paid) }}">
                        @error('full_amount_paid')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <p>Only applies to payslips from March 2021 onwards</p>
                    <div class="form-group col-md-6">
                        <label class="require form-label">{{ __('Number of days') }}</label>
                        <input type="text" name="number_of_days"
                            class="form-control @error('number_of_days') is-invalid @enderror"
                            value="{{ old('number_of_days', $subsistenceAllowance->number_of_days) }}">
                        @error('number_of_days')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                  <input type="hidden" name="term"
       value="{{ old('term', $term ?? $subsistenceAllowance->term) }}">
                    <div class="d-flex justify-content-end">
                        <a class="btn btn-rc-outline"
                            href="{{ route('payroll.index', ['employee_id' => $subsistenceAllowance->employee_id]) }}">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="btn btn-rc-primary">{{ __('Update') }}</button>
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
                url: '{{ route("subsistence-allowances.ajax-validate-update", $subsistenceAllowance->id) }}',
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