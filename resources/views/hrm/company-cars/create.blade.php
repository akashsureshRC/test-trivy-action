@extends('layouts.main')

@section('page-title')
    {{ __('Company Car') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Payroll') }},
    {{ __('Company Car') }}
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
            <form id="createForm" action="{{ route('company-cars.store') }}" method="POST">

        @csrf
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Deemed value of vehicle') }}</label>
                                <input class="form-control @error('deemed_value') is-invalid @enderror"
                                       type="number" step="0.01" min="0" name="deemed_value" value="{{ old('deemed_value') }}"
                                       placeholder="{{ __('Enter Amount') }}">
                                @error('deemed_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input" name="includes_maintenance_plan" value="1"
                                        {{ old('includes_maintenance_plan') ? 'checked' : '' }} id="includes_maintenance_plan">
                                    <label class="form-check-label" for="includes_maintenance_plan">{{ __('Purchase Price Includes Maintenance Plan') }}</label>
                                </div>
                                <p class="text-muted small">{{ __('Reduces the percentage to 3.25% of the deemed value (instead of the default 3.5%).') }}</p>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Taxable Percentage') }}</label>
                                <select name="taxable_percentage_id" id="taxable_percentage_id"
                                    class="form-control @error('taxable_percentage_id') is-invalid @enderror">
                                    <option value="">{{ __('Select Taxable Type') }}</option>
                                    @foreach ($taxableTypes as $type)
                                        <option value="{{ $type->id }}" {{ old('taxable_percentage_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->percentage }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('taxable_percentage_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                url: '{{ route("company-cars.ajax-validate-store") }}',
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