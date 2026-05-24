@extends('layouts.main')

@section('page-title')
    {{ __('Add Tax Directive') }}
@endsection

@section('page-breadcrumb')
    {{ __('Tax Directive') }}
@endsection

@section('page-action')
    <div class="col-auto pe-0">
        <a href="{{ route('tax-directive-entries.create') }}" class="btn btn-sm btn-rc-primary btn-icon"
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
            <x-rc-table title="{{ __('Tax Directive Entries') }}" titleIcon="ti ti-file-certificate">
                <x-rc-table.content>
                    <table class="rc-table" id="pc-dt-simple">
                        <thead class="thead-light">
                            <tr>
                                <th class="col-sno">SNO</th>
                                <th>{{ __('Directive Number') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($entries as $entry)
                                <tr id="row-{{ $entry->id }}">
                                    <td class="col-sno">{{ $loop->index + 1 }}</td>
                                    <td>{{ $entry->directive_number }}</td>
                                </tr>
                            @empty
                                <x-rc-table.empty :asRow="true" :colspan="2" icon="ti ti-file-certificate" title="{{ __('No Tax Directives') }}" message="{{ __('No tax directive entries found.') }}" />
                            @endforelse
                        </tbody>
                    </table>
                </x-rc-table.content>
            </x-rc-table>
        </div>
    </div>
@endsection
