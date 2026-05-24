@extends('layouts.main')

@section('page-title')
    {{ __('LossofIncomePolicy') }}
@endsection

@section('page-breadcrumb')
    {{ __('income-policies') }}
@endsection

@section('page-action')
    <div class="col-auto pe-0">
        <a href="{{ route('income-policies.create') }}" class="btn btn-sm btn-rc-primary btn-icon"
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
            <x-rc-table title="{{ __('LossofIncomePolicy') }}" titleIcon="ti ti-shield-dollar">
                <x-rc-table.content>
                    <table class="rc-table" id="pc-dt-simple">
                        <thead class="thead-light">
                            <tr>
                                <th class="col-sno">SNO</th>
                                <th class="col-amount">{{ __('lossof Income Aomunt') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($policies as $policy)
                                <tr id="row-{{ $policy->id }}">
                                    <td class="col-sno">{{ $loop->index + 1 }}</td>
                                    <td class="col-amount">{{ $policy->payout_amount}}</td>
                                </tr>
                            @empty
                                <x-rc-table.empty :asRow="true" :colspan="2" icon="ti ti-shield-off" title="{{ __('No Policies Found') }}" message="{{ __('There are no loss of income policies configured yet.') }}" />
                            @endforelse
                        </tbody>
                    </table>
                </x-rc-table.content>
            </x-rc-table>
        </div>
    </div>
@endsection
