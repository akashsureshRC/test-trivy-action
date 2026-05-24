@extends('layouts.main')
@section('page-title')
    {{ __('SDL') }}
@endsection
@section('page-breadcrumb')
    {{ __('Payroll') }},
    {{ __('SDL') }}
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp

@section('content')
<form action="{{ route('sdl-registrations.store') }}" class="mt-3" method="post">
    @csrf
    <div class="" style="margin-top:40px">
        <div class="card py-4 px-4 ">

            <div class="row ">
                <div class="col-xxl:4 col-xl:4 col-md-4">
                    <label class="require form-label">{{ __('SDL Registration') }}</label>
                    <select name="sdl_registration" class="form-control @error('sdl_registration') is-invalid @enderror">
                        <option value="">SelectSDL Registration</option>
                        <option value=" registered" {{ old('sdl_registration') == '' ? 'selected' : '' }}>
                           Registered
                        </option>
                        <option value="not registered" {{ old('sdl_registration') == '' ? 'selected' : '' }}>
                            Not registered
                        </option>
                        <option value="registered SDL exempt" {{ old('sdl_registration') == '' ? 'selected' : '' }}>
                            Registered SDL Exempt
                        </option>
                    </select>
                    @error('sdl_registration') 
                        <div class="invalid-feedback">{{ $message }}</div> 
                    @enderror
                </div>
                
               
            </div>

            <div class="row py-4">
                <div class="col-xxl:2 col-xl:2 col-md-2">
                    <label class="require form-label">{{ __('Effective from:') }}</label>
                        <input type="date" name="effective_from"
                            class="form-control @error('effective_from') is-invalid @enderror"
                            value="{{ old('effective_from') }}">
                        @error('effective_from')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                </div>
                <!--<div class="col-xxl:4 col-xl:4 col-md-4">
                    <input class="form-control" type="date"/>
                </div>-->
                
            </div>
        

        </div>

        <div class="d-flex justify-content-start " style="margin-bottom: 20px;">
            <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal"
                    onclick="window.location.href='{{ route('employee-salary.create') }}'">Cancel</button>
            <input class="btn btn-rc-primary" type="submit" value="Calculate">
        </div>
    </div>
</form>
    <script>
        function toggleRow(rowId) {
            const row = document.getElementById(rowId);
            row.style.display = row.style.display === "none" ? "flex" : "none";
        }
    </script>
@endsection