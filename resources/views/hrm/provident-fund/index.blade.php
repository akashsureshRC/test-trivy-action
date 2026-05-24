@extends('layouts.main')

@section('page-title')
    {{ __(' provident Fund ') }}
@endsection

@section('page-breadcrumb')
    {{ __('provident-fund') }}
@endsection

@section('page-action')
    <div class="col-auto pe-0">
        <a href="{{ route('provident-fund.create') }}" class="btn btn-sm btn-rc-primary btn-icon" data-bs-toggle="tooltip"
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
            <x-rc-table title="Provident Fund" titleIcon="ti ti-wallet">
                <x-rc-table.content>
                    <table class="rc-table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th class="col-sno">SNO</th>
                                <th>Employee</th>
                                <th>Contribution calculation</th>
                                <th>Category Factor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($providentfunds as $ProvidentFund)
                                <tr id="row-{{ $ProvidentFund->id }}">
                                    <td class="col-sno">{{ $loop->index + 1 }}</td>
                                    <td>{{ $ProvidentFund->employee->first_name }} {{ $ProvidentFund->employee->last_name }}</td>
                                    <td>{{ $ProvidentFund->Contribution }}</td>
                                    <td>{{ $ProvidentFund->category }}</td>
                                </tr>
                            @empty
                                <x-rc-table.empty :asRow="true" :colspan="4" icon="ti ti-wallet" title="No Provident Fund Records" message="No provident fund records have been added yet." />
                            @endforelse
                        </tbody>
                    </table>
                </x-rc-table.content>
            </x-rc-table>
        </div>
    </div>
@endsection
