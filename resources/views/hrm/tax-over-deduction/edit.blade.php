@extends('layouts.main')

@section('page-title')
    {{ __('Voluntary Tax Over-Deduction') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Payroll') }},
    {{ __('Tax Over-Deduction') }}
@endsection

@section('page-action')
    <div>
        <a href="{{ route('payroll.index', ['employee_id' => $taxOverDeductions->employee_id, 'term' => $term]) }}" class="btn btn-rc-outline btn-sm">
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
            <form id="editForm" action="{{ route('tax-over-deduction.update', $taxOverDeductions) }}" method="POST">
    @csrf
    @method('PUT') {{-- Add PUT method for update --}}
    <div class="row">
        <div class="col-md-12 col-xs-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
                            <label class="require form-label">{{ __('Amount per month') }}</label>
                            <input class="form-control @error('per_period') is-invalid @enderror" 
                                   type="number" step="0.01" min="0" name="per_period" value="{{ old('per_period',  $taxOverDeductions->per_period) }}" 
                                   placeholder="{{ __('Enter Amount') }}">
                            @error('per_period')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
<input type="hidden" name="term" value="{{ $term }}">
                    <div class="d-flex justify-content-end gap-2">
                        <a class="btn btn-rc-outline" href="{{ route('payroll.index', ['employee_id' => $taxOverDeductions->employee_id]) }}">
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
                url: '{{ route("tax-over-deduction.ajax-validate-update", $taxOverDeductions->id) }}',
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