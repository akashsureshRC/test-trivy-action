@extends('layouts.main')

@section('page-title')
    {{ __('Retirement Annuity Fund') }}
@endsection

@section('page-breadcrumb')
    {{ __('retirement-annuitie') }}, {{ __('index') }}
@endsection

@section('content')
@if(Auth::check())
    <x-rc-table title="{{ __('Retirement Annuity Fund') }}" titleIcon="ti ti-coin">
        <x-slot name="headerActions">
            <a href="{{ route('retirement-annuitie.create') }}" class="btn btn-rc-primary">{{ __('Add New') }}</a>
        </x-slot>

        <x-rc-table.content>
            <table class="rc-table">
                <thead>
                    <tr>
                        <th class="col-sno">S.NO</th>
                        <th class="col-amount">{{ __('Amount per month') }}</th>
                        <th class="col-amount">{{ __('Portion contributed by employer') }}</th>
                        <th>{{ __('Beneficiary') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($retirementannuities as $RetirementAnnuitie)
                        <tr id="row-{{ $RetirementAnnuitie->id }}">
                            <td class="col-sno">{{ $loop->index + 1 }}</td>
                            <td class="col-amount">{{ number_format($RetirementAnnuitie->amount, 2) }}</td>
                            <td class="col-amount">{{ number_format($RetirementAnnuitie->Portion, 2) }}</td>
                            <td>{{ $RetirementAnnuitie->employee->first_name ?? 'N/A' }} {{ $RetirementAnnuitie->employee->last_name ?? '' }}</td>
                        </tr>
                    @empty
                        <x-rc-table.empty :asRow="true" :colspan="4" icon="ti ti-coin-off" title="{{ __('No Retirement Annuities Found') }}" message="{{ __('No retirement annuity records have been added yet.') }}" />
                    @endforelse
                </tbody>
            </table>
        </x-rc-table.content>
    </x-rc-table>
@else
    <div class="alert alert-danger">You must be logged in to access this page.</div>
@endif
@endsection
