@extends('layouts.main')

@section('page-title')
    {{ __('Bursaries & Scholarships (Regular)') }}
@endsection

@section('page-breadcrumb')
    {{ __('Bursaries & Scholarships') }}
@endsection

@section('page-action')
    <div class="col-auto pe-0">
        <a href="{{ route('bursaries-scholarships.create') }}" class="btn btn-sm btn-rc-primary btn-icon" data-bs-toggle="tooltip"
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
            <x-rc-table title="{{ __('Bursaries & Scholarships') }}" titleIcon="ti ti-school">
                <x-rc-table.content>
                    <table class="rc-table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th class="col-sno">SNO</th>
                                <th>{{ __('Taxable_Portion') }}</th>
                                <th>{{ __('Exempt_Portion(%)') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bursaries as $bursary)
                                <tr id="row-{{ $bursary->id }}">
                                    <td class="col-sno">{{ $loop->index + 1 }}</td>
                                    <td>{{ $bursary->taxable_portion }}</td>
                                    <td>{{ $bursary->exempt_portion }}</td>
                                </tr>
                            @empty
                                <x-rc-table.empty :asRow="true" :colspan="3" icon="ti ti-school" title="{{ __('No Bursaries Found') }}" message="{{ __('No bursaries or scholarships have been added yet.') }}" />
                            @endforelse
                        </tbody>
                    </table>
                </x-rc-table.content>
            </x-rc-table>
        </div>
    </div>
@endsection
