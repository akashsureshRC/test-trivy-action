@extends('layouts.main')

@section('page-title')
    {{ __('Loss of Income Policy') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Payroll') }},
    {{ __('Loss of Income Policy') }}
@endsection

@section('page-action')
    <div>
        <a href="{{ route('payroll.index', ['employee_id' => $incomePolicy->employee_id, 'term' => $term]) }}" class="btn btn-rc-outline btn-sm">
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
            <form id="editForm" action="{{ route('income-policies.update', $incomePolicy) }}" method="POST">
 $incomePolicy->id]) }}" method="POST">
        @csrf
        @method('PUT') <!-- Use PUT for the update method -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="form-group col-md-6">
                            <label for="payout_amount" class="form-label">Payout Amount</label><span class="text-danger px-2">*</span>
                            <input type="number" class="form-control @error('payout_amount') is-invalid @enderror"
                                id="payout_amount" name="payout_amount"
                                value="{{ old('payout_amount', $incomePolicy->payout_amount) }}" required>
                            @error('payout_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <input type="hidden" name="term" value="{{ $term }}">
                        <input type="hidden" name="employee_id" value="{{ $incomePolicy->employee_id }}">
                        <div class="d-flex justify-content-end">
                            <a class="btn btn-rc-outline"
                                href="{{ route('payroll.index', ['employee_id' => $incomePolicy->employee_id]) }}">
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
                url: '{{ route("income-policies.ajax-validate-update", $incomePolicy->id) }}',
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