@extends('layouts.main')

@section('page-title')
    {{ __('Garnishee List') }}
@endsection

@section('page-breadcrumb')
    {{ __('garnishee') }}
@endsection

@section('page-action')
    <div class="col-auto pe-0">
        <a href="{{ route('garnishee.create') }}" class="btn btn-sm btn-rc-primary btn-icon"
            data-bs-toggle="tooltip" title="{{ __('Create') }}">
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
            <x-rc-table title="{{ __('Garnishee List') }}" titleIcon="ti ti-gavel">
                <x-rc-table.content>
                    <table class="rc-table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th class="col-sno">SNO</th>
                                <th>{{ __('Beneficiary') }}</th>
                                <th class="col-amount">{{ __('Installment') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($maintenanceorders as $MaintenanceOrder)
                                <tr id="row-{{ $MaintenanceOrder->id }}">
                                    <td class="col-sno">{{ $loop->index + 1 }}</td>
                                    <td>{{ $MaintenanceOrder->employee->first_name }} {{ $MaintenanceOrder->employee->last_name }}</td>
                                    <td class="col-amount">{{ number_format($MaintenanceOrder->installment, 2) }}</td>
                                </tr>
                            @empty
                                <x-rc-table.empty :asRow="true" :colspan="3" icon="ti ti-gavel" title="{{ __('No Garnishees Found') }}" message="{{ __('There are no garnishee orders to display.') }}" />
                            @endforelse
                        </tbody>
                    </table>
                </x-rc-table.content>
            </x-rc-table>
        </div>
    </div>
@endsection
