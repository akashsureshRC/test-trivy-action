@extends('layouts.main')

@section('page-title')
    {{ __('Add Savings') }}
@endsection

@section('page-breadcrumb')
    {{ __('Add Savings') }}
@endsection

@section('page-action')
    <div class="col-auto pe-0">
        <a href="{{ route('savings-deductions.create') }}" class="btn btn-sm btn-rc-primary btn-icon"
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
            <x-rc-table title="{{ __('Savings Deductions') }}" titleIcon="ti ti-wallet">
                <x-rc-table.content>
                    <table class="rc-table" id="pc-dt-simple">
                        <thead class="thead-light">
                            <tr>
                                <th class="col-sno">SNO</th>
                                <th>{{ __('Regular Deduction') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($deductions as $deduction)
                                <tr id="row-{{ $deduction->id }}">
                                    <td class="col-sno text-center">{{ $loop->index + 1 }}</td>
                                    <td>{{ $deduction->regular_deduction }}</td>
                                </tr>
                            @empty
                                <x-rc-table.empty :asRow="true" :colspan="2" icon="ti ti-wallet-off" title="{{ __('No Savings Deductions') }}" message="{{ __('No savings deductions have been added yet.') }}" />
                            @endforelse
                        </tbody>
                    </table>
                </x-rc-table.content>
            </x-rc-table>
        </div>
    </div>
@endsection
