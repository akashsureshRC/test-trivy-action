@extends('layouts.main')
@section('page-title')
    {{ __('Create Customer') }}
@endsection
@section('page-breadcrumb')
    {{ __('Customers') }}
@endsection
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Create Customer') }}</h5>
            </div>
            <div class="card-body">
                @include('users.create')
            </div>
        </div>
    </div>
</div>
@endsection
