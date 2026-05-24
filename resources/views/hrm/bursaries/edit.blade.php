@extends('layouts.main')

@section('page-title')
    {{ __('Bursaries & Scholarships') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Payroll') }},
    {{ __('Bursaries & Scholarships') }}
@endsection

@section('page-action')
    <div>
        <a href="{{ route('payroll.index', ['employee_id' => $bursary->employee_id, 'term' => $term]) }}" class="btn btn-rc-outline btn-sm">
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
            <form action="{{ route('bursaries.update', $bursary) }}" method="POST" id="editForm">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="require form-label">{{ __('Taxable Portion') }}</label>
                        <input type="text" name="taxable_portion"
                            class="form-control @error('taxable_portion') is-invalid @enderror"
                            value="{{ old('taxable_portion', $bursary->taxable_portion) }}">
                        @error('taxable_portion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label class="require form-label">{{ __('Exempt Portion') }}</label>
                        <input type="number" name="exempt_portion" step="0.01" min="0"
                            class="form-control @error('exempt_portion') is-invalid @enderror"
                            value="{{ old('exempt_portion', $bursary->exempt_portion) }}">
                        @error('exempt_portion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>


                    <div class="form-group col-md-6">
                        <label class="require form-label">{{ __('Type') }}</label>
                        <select name="type" class="form-control @error('type') is-invalid @enderror">
                            <option value="">Select Type</option>
                            <option value="Grades R to 12 and NQF levels 1 to 4"
                                {{ old('type', $bursary->type) == '' ? 'selected' : '' }}>
                                Basic Education(Grades R to 12 and NQF levels 1 to 4)
                            </option>
                            <option value="NQF 5 to 10" {{ old('type', $bursary->type) == '' ? 'selected' : '' }}>
                                Further Education(NQF 5 to 10)
                            </option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <input type="checkbox" class="form-check-input" name="employee_handles_payment" value="1"> Employee Handles the Payment

                    </div>
                    <div class="form-group col-md-6">
                        <input type="checkbox" class="form-check-input" name="to_disabled_person" value="1"> To a Disabled Person

                    </div>

                    <input type="hidden" name="term" value="{{ old('term', $term ?? $bursary->term) }}">
                    <div class="d-flex justify-content-end">
                        <a class="btn btn-rc-outline"
                            href="{{ route('payroll.index', ['employee_id' => $bursary->employee_id]) }}">
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
                url: '{{ route("bursaries.ajax-validate-update", $bursary->id) }}',
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