@extends('layouts.main')

@section('page-title')
    {{ __('Savings') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Payroll') }},
    {{ __('Savings') }}
@endsection

@section('page-action')
    <div>
        <a href="{{ route('payroll.index', ['employee_id' => $savings_deduction->employee_id, 'term' => $term]) }}" class="btn btn-rc-outline btn-sm">
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
            <form id="editForm" action="{{ route('savings-deductions.update', $savings_deduction) }}" method="POST">
        @csrf
        @method('PUT') 
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <input type="hidden" name="employee_id" value="{{ $savings_deduction->employee_id }}">
                            <label for="regular_deduction" class="form-label">Regular Deduction</label><span class="text-danger px-2">*</span>
                            <input type="number" step="0.01" min="0" class="form-control @error('regular_deduction') is-invalid @enderror"
                                   id="regular_deduction" name="regular_deduction" value="{{ old('regular_deduction', $savings_deduction->regular_deduction) }}" required>
                            @error('regular_deduction')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <input type="hidden" name="term" value="{{ request('term') }}">
                        <div class="d-flex justify-content-end">
                            <a class="btn btn-rc-outline" href="{{ route('payroll.index', ['employee_id' => $savings_deduction->employee_id]) }}">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-rc-primary">Update</button>
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
                url: '{{ route("savings-deductions.ajax-validate-update", $savings_deduction->id) }}',
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