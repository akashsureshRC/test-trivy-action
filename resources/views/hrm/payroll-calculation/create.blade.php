@extends('layouts.main')
@section('page-title')
    {{ __('BCEA Leave') }}
@endsection
@section('page-breadcrumb')
    {{ __('Payroll') }},
    {{ __('BCEA Leave') }}
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp

@section('content')
    <div class="" style="margin-top:40px">
        <div class="card py-4 px-4 ">
            <div class="row py-2 mt-6">
                <div class="col-xxl-4 col-xl-4 col-md-4 d-flex align-items-center gap-6">
                    <input type="checkbox" id="rate" name="rate" value="Enable fluctuating rate" />
                    <label for="rate" class="fs-6" style="padding-left:6px"> Enable fluctuating rate </label>
                </div>
            </div>
            <div class="row py-4 align-items-center">
                <div class="col-xxl:2 col-xl:2 col-md-2">
                    <h5 class="">
                        Effective From
                    </h5>
                </div>
                <div class="col-xxl:4 col-xl:4 col-md-4">
                    <input type="date" class="form-control" />
                </div>
            </div>


        </div>

        <div class="d-flex justify-content-start " style="margin-bottom: 20px;">
            <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal"
               >Cancel</button>
            <input class="btn btn-rc-primary" type="submit" value="Save">
        </div>
    </div>

    <script>
        function toggleRow(rowId) {
            const row = document.getElementById(rowId);
            row.style.display = row.style.display === "none" ? "flex" : "none";
        }
    </script>
@endsection