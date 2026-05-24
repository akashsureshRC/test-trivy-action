@extends('layouts.main')

@section('page-title')
    {{ __('Income Protection') }}
@endsection

@section('page-breadcrumb')
    {{ __('income-protection') }}
@endsection

@section('page-action')
    <div class="col-auto pe-0">
        <a href="{{ route('income-protection.create') }}" class="btn btn-sm btn-rc-primary btn-icon"
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
            <x-rc-table title="Income Protection" titleIcon="ti ti-shield-check">
                <x-rc-table.content>
                    <table class="rc-table" id="pc-dt-simple">
                        <thead class="thead-light">
                            <tr>
                                <th class="col-sno">SNO</th>
                                <th class="col-amount">Amount</th>
                                <th class="col-amount">Amount Deducted</th>
                                <th class="col-amount">Amount Paid</th>
                                <th>Employer Owns Policy</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($incomeProtections as $incomeProtection)
                                <tr id="row-{{ $incomeProtection->id }}">
                                    <td class="col-sno">{{ $loop->index + 1 }}</td>
                                    <td class="col-amount">{{ $incomeProtection->amount }}</td>
                                    <td class="col-amount">{{ $incomeProtection->amount_deducted }}</td>
                                    <td class="col-amount">{{ $incomeProtection->amount_paid }}</td>
                                    <td>{{ $incomeProtection->employer_own ? 'Yes' : 'No' }}</td>
                                </tr>
                            @empty
                                <x-rc-table.empty :asRow="true" :colspan="5" icon="ti ti-shield-off" title="No Income Protection Records" message="No income protection records have been added yet." />
                            @endforelse
                        </tbody>
                    </table>
                </x-rc-table.content>
            </x-rc-table>
        </div>
    </div>
@endsection
