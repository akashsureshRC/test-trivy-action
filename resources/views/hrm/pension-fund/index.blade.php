@extends('layouts.main')

@section('page-title')
    {{ __('Pension Fund') }}
@endsection

@section('page-breadcrumb')
    {{ __('pension-fund') }}
@endsection

@section('page-action')
    <div class="col-auto pe-0">
        <a href="{{ route('pension-fund.create') }}" class="btn btn-sm btn-rc-primary btn-icon" data-bs-toggle="tooltip"
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
            <x-rc-table title="Pension Fund" titleIcon="ti ti-building-bank">
                <x-rc-table.content>
                    <table class="rc-table" id="pc-dt-simple">
                        <thead class="thead-light">
                            <tr>
                                <th class="col-sno">SNO</th>
                                <th>Employee</th>
                                <th>Pension Calculation</th>
                                <th>Category Factor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pensionfunds as $PensionFund)
                                <tr id="row-{{ $PensionFund->id }}">
                                    <td class="col-sno text-center">{{ $loop->index + 1 }}</td>
                                    <td>{{ $PensionFund->employee->first_name }} {{ $PensionFund->employee->last_name }}</td>
                                    <td>{{ $PensionFund->pension }}</td>
                                    <td>{{ $PensionFund->category }}</td>
                                </tr>
                            @empty
                                <x-rc-table.empty :asRow="true" :colspan="4" icon="ti ti-building-bank" title="No Pension Funds" message="No pension fund records found." />
                            @endforelse
                        </tbody>
                    </table>
                </x-rc-table.content>
            </x-rc-table>
        </div>
    </div>
@endsection
