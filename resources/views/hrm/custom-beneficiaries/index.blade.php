@extends('layouts.main')
@section('page-title')
    {{ __('Create Employee') }}
@endsection
@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Create Employee') }}
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Garnishee</div>
                <a href="{{ route('add-garnishees.create') }}" class="card-body add-button">
                    <span class="add-icon">+</span> Add
                </a>
            </div>
            <div class="card mb-3">
                <div class="card-header">Maintenance Order</div>
                <a href="{{ route('add-maintenance.create') }}" class="card-body add-button">
                    <span class="add-icon">+</span> Add
                </a>
            </div>
            <div class="card mb-3">
                <div class="card-header">Medical Aid</div>
                <a href="{{ route('add-medical-aid.create') }}" class="card-body add-button">
                    <span class="add-icon">+</span> Add
                </a>
            </div>
            <div class="card mb-3">
                <div class="card-header">Pension Fund
                </div>
                <a href="{{ route('add-pension-funds.create') }}" class="card-body add-button">
                    <span class="add-icon">+</span> Add
                </a>
            </div>
            <div class="card mb-3">
                <div class="card-header">Provident Fund
                </div>
                <a href="{{ route('add-provident-funds.create') }}" class="card-body add-button">
                    <span class="add-icon">+</span> Add
                </a>
            </div>
            <div class="card mb-3">
                <div class="card-header">Retirement Annuity Fund
                </div>
                <a href="{{ route('add-retirement-funds.create') }}" class="card-body add-button">
                    <span class="add-icon">+</span> Add
                </a>
            </div>

            <div class="card mb-3">
                <div class="card-header">Custom Beneficiary

                </div>
                <a href="{{ route('custom-beneficiaries.create') }}" class="card-body add-button">
                    <span class="add-icon">+</span> Add
                </a>
            </div>
            
        </div>
    </div>
</div>

<style>
    .card-header {
        background-color: #3d4f5d;
        color: white;
        font-weight: bold;
    }

    .card-body {
        text-align: center;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 60px;
        cursor: pointer;
        transition: background-color 0.3s, color 0.3s;
        background-color: white;
        text-decoration: none;
        border-radius: 5px;
        color: green; /* Default text color */
        font-weight: bold;
    }

    .add-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        background-color: green;
        color: white;
        border-radius: 50%;
        font-size: 16px;
        font-weight: bold;
        margin-right: 5px;
        transition: background-color 0.3s, color 0.3s;
    }

    /* Hover Effect */
    .card-body:hover {
        background-color: green;
        color: white;
    }

    .card-body:hover .add-icon {
        background-color: white;
        color: green;
    }
</style>


@endsection