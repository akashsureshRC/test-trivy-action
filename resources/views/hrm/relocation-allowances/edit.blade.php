@extends('layouts.main')

@section('page-title')
    {{ __('Relocation Allowance') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Payroll') }},
    {{ __('Relocation Allowance') }}
@endsection

@section('page-action')
    <div>
        <a href="{{ route('payroll.index', ['employee_id' => $relocationAllowance->employee_id, 'term' => $term]) }}" class="btn btn-rc-outline btn-sm">
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
            <form id="editForm" action="{{ route('relocation-allowances.update', $relocationAllowance) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="require form-label">{{ __('Relocation Allowance - Taxable') }}</label>
                        <input type="text" name="taxable_allowance"
                            class="form-control @error('taxable_allowance') is-invalid @enderror"
                            value="{{ old('taxable_allowance', $relocationAllowance->taxable_allowance) }}">
                        @error('taxable_allowance')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label class="require form-label">{{ __('Relocation Allowance - Non-Taxable') }}</label>
                        <input type="text" name="non_taxable_allowance"
                            class="form-control @error('non_taxable_allowance') is-invalid @enderror"
                            value="{{ old('non_taxable_allowance', $relocationAllowance->non_taxable_allowance) }}">
                        @error('non_taxable_allowance')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <p>Only applies to payslips from March 2021 onwards</p>
                    <div class="form-group col-md-6">
                        <label class="require form-label">{{ __('Taxable items paid by employer') }}</label>
                        <input type="text" name="taxable_items_paid_by_employer"
                            class="form-control @error('taxable_items_paid_by_employer') is-invalid @enderror"
                            value="{{ old('taxable_items_paid_by_employer', default: $relocationAllowance->taxable_items_paid_by_employer) }}">
                        @error('taxable_items_paid_by_employer')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <input type="hidden" name="term" value="{{ old('term', $term) }}">
                    <div class="d-flex justify-content-end">
                        <a class="btn btn-rc-outline"
                            href="{{ route('payroll.index', ['employee_id' => $relocationAllowance->employee_id]) }}">
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
                url: '{{ route("relocation-allowances.ajax-validate-update", $relocationAllowance->id) }}',
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