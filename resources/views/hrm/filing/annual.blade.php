@extends('layouts.main')
@section('page-title')
{{ __('BI-Annual Filing') }}
@endsection
@section('page-breadcrumb')
{{ __('BI-Annual Filing') }}
@endsection
@push('css')
<style>
    input[type="checkbox"] {
        width: 18px;
        height: 18px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <x-rc-table title="{{ __('Filing Season') }}">
            <x-slot name="headerActions">
                <select class="form-select" id="seasonSelect">
                    @if(isset($biFilingData) && count($biFilingData) > 0)
                    @foreach($biFilingData as $season)
                    <option value="{{ $season['season'] }}" {{ ($selectedSeason ?? null) == $season['season'] ? 'selected' : '' }}>{{ $season['season'] }}</option>
                    @endforeach
                    @else
                    <option value="">No filing seasons available</option>
                    @endif
                </select>
            </x-slot>

            @if(isset($biFilingData) && count($biFilingData) > 0)
            @foreach($biFilingData as $index => $season)
            @php
                $activeSeason = $selectedSeason ?? ($biFilingData[0]['season'] ?? null);
            @endphp
            <div class="season-data {{ $activeSeason === $season['season'] ? '' : 'd-none' }}" data-season="{{ $season['season'] }}">
                <div class="d-flex justify-content-between align-items-center p-3">
                    <h6 class="m-0">{{ $season['season_label'] }}</h6>
                    <div class="d-flex gap-2">
                        <a href="{{ route('bi-filing.emp501-pdf', $season['season']) }}" class="btn btn-rc-primary btn-sm">
                            <i class="fas fa-file-download me-2"></i> Download EMP501
                        </a>
                        <a href="{{ route('bi-filing.tax-certificate-export', $season['season']) }}" class="btn btn-rc-primary btn-sm">
                            <i class="fas fa-file-export me-2"></i> Export Tax Certificate
                        </a>
                    </div>
                </div>
                <x-rc-table.content>
                    <table class="rc-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Number</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>View</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($season['payruns'] as $payrun)
                            <tr>
                                <td>{{ $payrun['employee_name'] }}</td>
                                <td>{{ $payrun['employee_number'] }}</td>
                                <td>{{ formatDate($payrun['date']) }}</td>
                                <td>{{ $payrun['type'] }}</td>
                                <td><a href="{{ route('employee-bi-filing.pdf', $payrun['payslip_id']) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-pdf"></i></a></td>
                            </tr>
                            @empty
                            <x-rc-table.empty :asRow="true" :colspan="5" icon="ti ti-file-off" title="{{ __('No Filing Records') }}" message="{{ __('No payrun data found for this season.') }}" />
                            @endforelse
                        </tbody>
                    </table>
                </x-rc-table.content>

                <x-rc-table.footer :paginator="$season['payruns']" />
            </div>
            @endforeach
            @else
            <x-rc-table.empty icon="ti ti-file-off" title="{{ __('No Filing Data') }}" message="{{ __('No bi-filing data available. Please ensure payslips are processed through payrun system.') }}" />
            @endif
        </x-rc-table>
    </div>
</div>
<script>
    document.getElementById('seasonSelect').addEventListener('change', function() {
        const selectedSeason = this.value;
        const url = new URL(window.location.href);
        url.searchParams.set('season', selectedSeason);

        const paramsToDelete = [];
        url.searchParams.forEach((value, key) => {
            if (key.startsWith('page_')) {
                paramsToDelete.push(key);
            }
        });
        paramsToDelete.forEach(key => url.searchParams.delete(key));

        window.location.href = url.toString();
    });
</script>
@endsection