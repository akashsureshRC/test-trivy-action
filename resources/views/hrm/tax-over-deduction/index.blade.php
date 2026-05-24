@extends('layouts.main')

@section('page-title')
    {{ __('Voluntary Tax Over-Deduction') }}
@endsection

@section('page-breadcrumb')
    {{ __(' Tax Over-Deduction') }}
@endsection

@section('page-action')
    <div class="col-auto pe-0">
        <a href="{{ route('tax-over-deduction.create') }}" class="btn btn-sm btn-rc-primary btn-icon"
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
            <x-rc-table title="{{ __('Voluntary Tax Over-Deduction') }}" titleIcon="ti ti-receipt-tax">
                <x-rc-table.content>
                    <table class="rc-table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th class="col-sno">SNO</th>
                                <th>{{ __('Per Period') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($taxOverDeductions as $TaxOverDeduction)
                                <tr id="row-{{ $TaxOverDeduction->id }}">
                                    <td class="col-sno">{{ $loop->index + 1 }}</td>
                                    <td>{{ $TaxOverDeduction->per_period }}</td>
                                </tr>
                            @empty
                                <x-rc-table.empty :asRow="true" :colspan="2" icon="ti ti-receipt-tax" title="{{ __('No Tax Over-Deductions') }}" message="{{ __('No voluntary tax over-deduction records found.') }}" />
                            @endforelse
                        </tbody>
                    </table>
                </x-rc-table.content>
            </x-rc-table>
        </div>
    </div>
@endsection
