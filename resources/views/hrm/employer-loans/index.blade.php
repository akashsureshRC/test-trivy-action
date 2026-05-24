@extends('layouts.main')

@section('page-title')
    {{ __('Add Employer Loan') }}
@endsection

@section('page-breadcrumb')
{{ __('Employer Loan') }}, {{ __('index') }}
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
            <x-rc-table title="{{ __('Employer Loans') }}" titleIcon="ti ti-cash">
                <x-rc-table.content>
                    <table class="rc-table" id="pc-dt-simple">
                        <thead class="thead-light">
                            <tr>
                                <th class="col-sno">SNO</th>
                                <th>{{ __('Interest rate') }}</th>
                                <th>{{ __('Regular repayment') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($loans as $loan)
                                <tr id="row-{{ $loan->id }}">
                                    <td class="col-sno">{{ $loop->index + 1 }}</td>
                                    <td>{{ $loan->interest_rate }}</td>
                                    <td>{{ $loan->regular_repayment }}</td>
                                </tr>
                            @empty
                                <x-rc-table.empty :asRow="true" :colspan="3" icon="ti ti-cash-off" title="{{ __('No Loans Found') }}" message="{{ __('No employer loans have been added yet.') }}" />
                            @endforelse
                        </tbody>
                    </table>
                </x-rc-table.content>
            </x-rc-table>
        </div>
    </div>
@endsection
