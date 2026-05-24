@extends('layouts.main')

@section('page-title')
    {{ __('Beneficiary') }}
@endsection

@section('page-breadcrumb')
    {{ __('beneficiary') }}, {{ __('index') }}
@endsection

@push('css')
    <link href="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css') }}" rel="stylesheet">
@endpush

@section('content')
    @if (Auth::check())
        {{-- Ensure user is logged in --}}
        <form action="{{ route('beneficiary.store') }}" class="mt-3" method="post">
            @csrf
            <div class="row">
                <div class="col-md-12 col-xs-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-6">

                                    <label class="require form-label">{{ __('Employee') }}</label>
                                    <select class="form-control" id="employee_id" name="employee_id" required>
                                        <option value="">Select Employee</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->first_name }}
                                                {{ $employee->last_name }}</option>
                                        @endforeach
                                    </select>

                                </div>
                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Beneficiary Name') }}</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="{{ old('name') }}" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="require form-label">{{ __('Relationship') }}</label> 
                                        <input type="text" class="form-control" id="relationship" name="relationship" value="{{ old('relationship') }}" required>
                                    </div>
                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Amount per Month') }}</label>
                                    <input class="form-control @error('amount_per_month') is-invalid @enderror" type="number"
                                        name="amount_per_month" value="{{ old('amount_per_month') }}" required
                                        placeholder="{{ __('Enter Amount') }}">
                                    @error('amount_per_month')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a class="btn btn-rc-outline"
                                    href="{{ route('beneficiary.index') }}">{{ __('Cancel') }}</a>
                                <button class="btn btn-rc-primary" type="submit">{{ __('Submit') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @else
        <div class="alert alert-danger">You must be logged in to access this page.</div>
    @endif
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
@endpush
