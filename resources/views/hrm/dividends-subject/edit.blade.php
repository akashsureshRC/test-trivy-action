@extends('layouts.main')

@section('page-title')
    {{ __('Dividends Subject to Tax') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Payroll') }},
    {{ __('Dividends Subject to Tax') }}
@endsection

@section('page-action')
    <div>
        <a href="{{ route('payroll.index', ['employee_id' => $dividendsSubject->employee_id, 'term' => $term]) }}" class="btn btn-rc-outline btn-sm">
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
            <form id="editForm" action="{{ route('dividends-subject.update', $dividendsSubject) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="require form-label">{{ __('Directive Number') }}</label>
                        <input type="text" name="directive_number"
                            class="form-control @error('directive_number') is-invalid @enderror"
                            value="{{ old('directive_number', $dividendsSubject->directive_number) }}">
                        @error('directive_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label class="require form-label">{{ __('Directive Issue Date') }}</label>
                        <input type="date" name="directive_issue_date"
                            class="form-control @error('directive_issue_date') is-invalid @enderror"
                            value="{{ old('directive_issue_date', $dividendsSubject->directive_issue_date) }}">
                        @error('directive_issue_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label class="require form-label">{{ __('Directive Income Source Code') }}</label>
                        <input type="text" name="directive_income_source_code"
                            class="form-control @error('directive_income_source_code') is-invalid @enderror"
                            value="{{ old('directive_income_source_code', $dividendsSubject->directive_income_source_code) }}">
                        @error('directive_income_source_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label class="require form-label">{{ __('Directive Income Amount') }}</label>
                        <input type="number" name="directive_income_amount" step="0.01" min="0"
                            class="form-control @error('directive_income_amount', $dividendsSubject->directive_income_amount) is-invalid @enderror"
                            value="{{ old('directive_income_amount',$dividendsSubject->directive_income_amount) }}">
                        @error('directive_income_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label class="require form-label">{{ __('Amount of Tax to Deduct (from Tax Directive)') }}</label>
                        <input type="number" name="tax_to_deduct" step="0.01" min="0"
                            class="form-control @error('tax_to_deduct', $dividendsSubject->tax_to_deduct) is-invalid @enderror"
                            value="{{ old('tax_to_deduct',$dividendsSubject->tax_to_deduct) }}">
                        @error('tax_to_deduct')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <input type="hidden" name="term" value="{{ $term }}">
                    <div class="d-flex justify-content-end">
                        <a class="btn btn-rc-outline" href="{{ route('payroll.index',['employee_id' => $dividendsSubject->employee_id]) }}">{{ __('Cancel') }}</a>
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
                url: '{{ route("dividends-subject.ajax-validate-update", $dividendsSubject->id) }}',
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