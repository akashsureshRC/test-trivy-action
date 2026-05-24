@extends('layouts.main')

@section('page-title')
    {{ __('Add Union Membership Fees') }}
@endsection

@section('page-breadcrumb')
    {{ __(' Union Membership Fees') }}
@endsection

@section('page-action')
    <div class="col-auto pe-0">
        <a href="{{ route('union-membership.create') }}" class="btn btn-sm btn-rc-primary btn-icon"
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
            <x-rc-table title="{{ __('Union Membership Fees') }}" titleIcon="ti ti-users-group">
                <x-rc-table.content>
                    <table class="rc-table" id="pc-dt-simple">
                        <thead class="thead-light">
                            <tr>
                                <th class="col-sno">SNO</th>
                                <th class="col-amount">{{ __('Per Period') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($unionmembershipfees as $UnionMembershipFee)
                                <tr id="row-{{ $UnionMembershipFee->id }}">
                                    <td class="col-sno text-center">{{ $loop->index + 1 }}</td>
                                    <td class="col-amount">{{ $UnionMembershipFee->amount_per_period}}</td>
                                </tr>
                            @empty
                                <x-rc-table.empty :asRow="true" :colspan="2" icon="ti ti-users-group" title="{{ __('No Union Membership Fees') }}" message="{{ __('No union membership fees have been added yet.') }}" />
                            @endforelse
                        </tbody>
                    </table>
                </x-rc-table.content>
            </x-rc-table>
        </div>
    </div>
@endsection
