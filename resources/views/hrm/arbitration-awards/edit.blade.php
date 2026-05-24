@extends('layouts.main')

@section('page-title')
    {{ __('Arbitration Award') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Payroll') }},
    {{ __('Arbitration Award') }}
@endsection

@section('page-action')
    <div>
        <a href="{{ route('payroll.index', ['employee_id' => $arbitrationAward->employee_id, 'term' => $term]) }}" class="btn btn-rc-outline btn-sm">
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
            <form id="editForm" action="{{ route('arbitration-awards.update', $arbitrationAward) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-12 col-xs-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Directive Number') }}</label>
                                    <input class="form-control @error('directive_number') is-invalid @enderror"
                                        type="text" name="directive_number"
                                        value="{{ old('directive_number', $arbitrationAward->directive_number) }}"
                                        placeholder="{{ __('Enter Directive Number') }}">
                                    @error('directive_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Directive Issue Date') }}</label>
                                    <input class="form-control @error('directive_issue_date') is-invalid @enderror"
                                        type="date" name="directive_issue_date"
                                        value="{{ old('directive_issue_date', $arbitrationAward->directive_issue_date) }}">
                                    @error('directive_issue_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <p class="small text-muted">Only applies to payslips from March 2021 onwards</p>
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Directive Income Amount') }}</label>
                                    <input class="form-control @error('directive_income_amount') is-invalid @enderror"
                                        type="number" step="0.01" min="0" name="directive_income_amount"
                                        value="{{ old('directive_income_amount', $arbitrationAward->directive_income_amount) }}"
                                        placeholder="{{ __('Enter Amount') }}">
                                    @error('directive_income_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Tax To Deduct') }}</label>
                                    <input class="form-control @error('tax_to_deduct') is-invalid @enderror"
                                        type="number" step="0.01" min="0" name="tax_to_deduct"
                                        value="{{ old('tax_to_deduct', $arbitrationAward->tax_to_deduct) }}"
                                        placeholder="{{ __('Enter Amount') }}">
                                    @error('tax_to_deduct')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <input type="hidden" name="term" value="{{ $term }}">
                            <div class="d-flex justify-content-end">
                                <a class="btn btn-rc-outline" href="{{ route('payroll.index',['employee_id' => $arbitrationAward->employee_id]) }}">{{ __('Cancel') }}</a>
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
                url: '{{ route("arbitration-awards.ajax-validate-update", $arbitrationAward->id) }}',
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