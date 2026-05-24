@php
if(Auth::user()->type=='super admin')
{
$titles = __('Customer Log History') ;
}
else{

$titles = __('User Log History') ;
}
@endphp

@extends('layouts.main')
@section('page-title')
{{ $titles }}
@endsection
@section('page-breadcrumb')
{{ $titles }}
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('User Log History')}}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <x-rc-table>
            <x-rc-table.filter action="{{ route('users.userlog.history') }}" method="GET" id="user_userlog">
                <x-rc-table.filter-group label="{{ __('Month') }}" wide>
                    <div class="d-flex gap-2">
                        @php
                            $selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
                            list($selectedYear, $selectedMonthNum) = explode('-', $selectedMonth);
                        @endphp
                        <select name="month_select" id="month_select" class="rc-filter-select" style="flex: 2;">
                            @foreach(['01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'] as $monthNum => $monthName)
                                <option value="{{ $monthNum }}" {{ $selectedMonthNum == $monthNum ? 'selected' : '' }}>
                                    {{ $monthName }}
                                </option>
                            @endforeach
                        </select>
                        <select name="year_select" id="year_select" class="rc-filter-select" style="flex: 1;">
                            @for($year = date('Y'); $year >= date('Y') - 10; $year--)
                                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                        <input type="hidden" name="month" id="month_hidden" value="{{ $selectedMonth }}">
                    </div>
                </x-rc-table.filter-group>

                <x-rc-table.filter-group label="{{ Auth::user()->type == 'super admin' ? __('Customer') : __('User') }}" wide>
                    <select name="users" class="rc-filter-select">
                        @foreach($filteruser as $id => $name)
                        <option value="{{ $id }}" {{ isset($_GET['users']) && $_GET['users'] == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                        @endforeach
                    </select>
                </x-rc-table.filter-group>
            </x-rc-table.filter>


            <x-rc-table.content>
                <table class="rc-table" id="users_log">
                    <thead>
                        <tr>
                            @if(Auth::user()->type == 'super admin' || Auth::user()->type == 'company')
                            <th>{{ __('User Name') }}</th>
                            <th>{{ __('Role') }}</th>
                            @endif
                            <th class="col-date">{{ __('Last Login') }}</th>
                            <th>{{ __('Ip') }}</th>
                            <th>{{ __('Country') }}</th>
                            <th>{{ __('Device') }}</th>
                            <th>{{ __('OS') }}</th>
                            <th class="col-actions">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($userdetails as $user)
                        @php
                        $userdetail = json_decode($user->details);
                        @endphp
                        <tr>
                            @if(Auth::user()->type == 'super admin' || Auth::user()->type == 'company')
                            <td>{{ $user->user_name }}</td>
                            <td>
                                <span class="rc-status rc-status-info">{{$user->user_type}}</span>
                            </td>
                            @endif
                            <td class="col-date">{{ !empty($user->date) ? companyDateTimeFormate($user->date) : '-' }}</td>
                            <td>{{ $user->ip }}</td>
                            <td>{{ !empty($userdetail->country)?$userdetail->country:'-' }}</td>
                            <td>{{ $userdetail->device_type }}</td>
                            <td>{{ $userdetail->os_name }}</td>
                            <td class="col-actions">
                                <a href="#" class="rc-table-action rc-table-action-view"
                                    data-size="lg" data-url="{{ route('users.userlog.view', [$user->id]) }}"
                                    data-ajax-popup="true" data-bs-toggle="tooltip"
                                    data-title="{{ __('View User Logs') }}" title="{{ __('View') }}">
                                    <i class="ti ti-eye"></i>
                                </a>
                                @permission('user delete')
                                {{ Form::open(['route' => ['users.userlog.destroy', $user->id], 'class' => 'm-0 d-inline']) }}
                                @method('DELETE')
                                <a href="#" class="rc-table-action rc-table-action-delete bs-pass-para show_confirm"
                                    data-bs-toggle="tooltip" title="{{ __('Delete') }}" aria-label="Delete"
                                    data-confirm-yes="delete-form-{{ $user->id }}">
                                    <i class="ti ti-trash"></i>
                                </a>
                                {{ Form::close() }}
                                @endpermission
                            </td>
                        </tr>
                        @empty
                        <x-rc-table.empty :asRow="true" :colspan="(Auth::user()->type == 'super admin' || Auth::user()->type == 'company') ? 8 : 6" icon="ti ti-history" title="{{ __('No Log History') }}" message="{{ __('No user log history found.') }}" />
                        @endforelse
                    </tbody>
                </table>
            </x-rc-table.content>
            <x-rc-table.footer :paginator="$userdetails" />
        </x-rc-table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const monthSelect = document.getElementById('month_select');
        const yearSelect = document.getElementById('year_select');
        const monthHidden = document.getElementById('month_hidden');
        
        function updateMonthValue() {
            if (monthSelect && yearSelect && monthHidden) {
                monthHidden.value = yearSelect.value + '-' + monthSelect.value;
            }
        }
        
        if (monthSelect && yearSelect) {
            monthSelect.addEventListener('change', updateMonthValue);
            yearSelect.addEventListener('change', updateMonthValue);
        }
    });
</script>
@endpush