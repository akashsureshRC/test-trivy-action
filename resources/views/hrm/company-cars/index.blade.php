@extends('layouts.main')

@section('page-title')
    {{ __('Add Company Car') }}
@endsection

@section('page-breadcrumb')
    {{ __('Company Car') }}
@endsection

@section('page-action')
    <div class="col-auto pe-0">
        <a href="{{ route('company-cars.create') }}" class="btn btn-sm btn-rc-primary btn-icon" data-bs-toggle="tooltip"
            title="{{ __('Create') }}">
            <i class="ti ti-plus text-white"></i>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12">
            @if (session()->has('success') || session()->has('error'))
                <div class="alert alert-info">
                    @if (session()->has('success'))
                        {!! session('success') !!}
                    @endif
                    @if (session()->has('error'))
                        {!! session('error') !!}
                    @endif
                </div>
            @endif
        </div>
        <div class="col-lg-12 col-md-12">
            <x-rc-table title="{{ __('Company Cars') }}" titleIcon="ti ti-car">
                <x-rc-table.content>
                    <table class="rc-table" id="pc-dt-simple">
                        <thead class="thead-light">
                            <tr>
                                <th class="col-sno">SNO</th>
                                <th class="col-amount">{{ __('Deemed value of vehicle') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($companyCars as $car)
                                <tr id="row-{{ $car->id }}">
                                    <td class="col-sno">{{ $loop->index + 1 }}</td>
                                    <td class="col-amount">{{ $car->deemed_value }}</td>
                                </tr>
                            @empty
                                <x-rc-table.empty :asRow="true" :colspan="2" icon="ti ti-car" title="{{ __('No Company Cars') }}" message="{{ __('No company cars have been added yet.') }}" />
                            @endforelse
                        </tbody>
                    </table>
                </x-rc-table.content>
            </x-rc-table>
        </div>
    </div>
@endsection
