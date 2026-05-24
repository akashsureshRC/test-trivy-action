@extends('layouts.main')

@section('page-title')
    {{ __('CompanyCar Under Operating Lease') }}
@endsection

@section('page-breadcrumb')
    {{ __('CompanyCar Under Operating') }}
@endsection

@section('page-action')
    <div class="col-auto pe-0">
        <a href="{{ route('company-car-operating.create') }}" class="btn btn-sm btn-rc-primary btn-icon" data-bs-toggle="tooltip"
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
            <x-rc-table title="{{ __('CompanyCar Under Operating Lease') }}" titleIcon="ti ti-car">
                <x-rc-table.content>
                    <table class="rc-table" id="pc-dt-simple">
                        <thead class="thead-light">
                            <tr>
                                <th class="col-sno">SNO</th>
                                <th class="col-amount">{{ __('Amount') }}</th>
                                <th>{{ __('TaxablePersentage(%)') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($companyCars as $CompanyCar)
                                <tr id="row-{{ $CompanyCar->id }}">
                                    <td class="col-sno">{{ $loop->index + 1 }}</td>
                                    <td class="col-amount">{{ $CompanyCar->amount }}</td>
                                    <td>{{ $CompanyCar->taxable_persentage }}%</td>
                                </tr>
                            @empty
                                <x-rc-table.empty :asRow="true" :colspan="3" icon="ti ti-car" title="{{ __('No Company Cars Found') }}" message="{{ __('No company car operating lease records available.') }}" />
                            @endforelse
                        </tbody>
                    </table>
                </x-rc-table.content>
            </x-rc-table>
        </div>
    </div>
@endsection
