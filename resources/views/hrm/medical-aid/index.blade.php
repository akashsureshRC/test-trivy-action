@extends('layouts.main')

@section('page-title')
    {{ __('Medical Aid') }}
@endsection

@section('page-breadcrumb')
    {{ __('medical-aid') }}, {{ __('index') }}
@endsection

@section('content')
@if(Auth::check())
    <x-rc-table title="{{ __('Medical Aid List') }}" titleIcon="ti ti-heart-plus">
        <x-rc-table.content>
            <table class="rc-table">
                <thead>
                    <tr>
                        <th class="col-sno">#</th>
                        <th class="col-amount">{{ __('Total Amount') }}</th>
                        <th class="col-amount">{{ __('Employer Contribution') }}</th>
                        <th>{{ __('Employee Payment') }}</th>
                        <th>{{ __('Beneficiary') }}</th>
                        <th>{{ __('Members') }}</th>
                        <th class="col-actions">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($medicalAids as $index => $medicalAid)
                        <tr>
                            <td class="col-sno">{{ $index + 1 }}</td>
                            <td class="col-amount">{{ number_format($medicalAid->total_amount, 2) }}</td>
                            <td class="col-amount">{{ number_format($medicalAid->employer_contribution, 2) }}</td>
                            <td>{{ $medicalAid->employee_payment ? 'Yes' : 'No' }}</td>
                            <td>{{ $medicalAid->employee->first_name ?? 'N/A' }} {{ $medicalAid->employee->last_name ?? '' }}</td>
                            <td>{{ $medicalAid->members }}</td>
                            <td class="col-actions">
                                
                            </td>
                        </tr>
                    @empty
                        <x-rc-table.empty :asRow="true" :colspan="7" icon="ti ti-heart-plus" title="{{ __('No Medical Aid Records') }}" message="{{ __('No medical aid records have been added yet.') }}" />
                    @endforelse
                </tbody>
            </table>
        </x-rc-table.content>
    </x-rc-table>
@else
    <div class="alert alert-danger">You must be logged in to access this page.</div>
@endif
@endsection
