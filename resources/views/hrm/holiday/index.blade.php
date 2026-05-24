@extends('layouts.main')
@section('page-title')
    {{ __('Manage Holiday') }}
@endsection
@section('page-breadcrumb')
{{ __('Holiday') }}
@endsection
@section('page-action')
<div>
    @permission('holiday import')
        <a href="#"  class="btn btn-sm btn-rc-primary" data-ajax-popup="true" data-title="{{__('Holiday Import')}}" data-url="{{ route('holiday.file.import') }}"  data-toggle="tooltip" title="{{ __('Import') }}"><i class="ti ti-file-import"></i>
        </a>
    @endpermission
    <a href="{{ route('holiday.calender') }}" class="btn btn-sm btn-rc-primary" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Calendar View') }}">
        <i class="ti ti-calendar"></i>
    </a>
    @permission('holiday create')
        <a  class="btn btn-sm btn-rc-primary" data-ajax-popup="true" data-size="md" data-title="{{ __('Create New Holiday') }}" data-url="{{route('holiday.create')}}" data-bs-toggle="tooltip"  data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endpermission
</div>
@endsection
@section('content')
<div class="row">
    <div class="col-sm-12">
        <x-rc-table title="{{ __('Manage Holiday') }}" titleIcon="ti ti-calendar-event">
            <x-rc-table.filter action="{{ route('holiday.index') }}" method="GET">
                <x-rc-table.filter-group label="{{ __('Start Date') }}">
                    {{ Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : '', ['class' => 'form-control', 'placeholder' => 'Select Date']) }}
                </x-rc-table.filter-group>
                <x-rc-table.filter-group label="{{ __('End Date') }}">
                    {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : '', ['class' => 'form-control', 'placeholder' => 'Select Date']) }}
                </x-rc-table.filter-group>
            </x-rc-table.filter>

            <x-rc-table.content>
                <table class="rc-table" id="assets">
                    <thead>
                        <tr>
                            <th>{{ __('Occasion') }}</th>
                            <th class="col-date">{{ __('Start Date') }}</th>
                            <th class="col-date">{{ __('End Date') }}</th>
                            @if (Laratrust::hasPermission('holiday edit') || Laratrust::hasPermission('holiday delete'))
                                <th class="col-actions" width="200px">{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($holidays as $holiday)
                            <tr>
                                <td>{{ $holiday->occasion }}</td>
                                <td class="col-date">{{ companyDateFormate($holiday->start_date) }}</td>
                                <td class="col-date">{{ companyDateFormate($holiday->end_date) }}</td>
                                @if (Laratrust::hasPermission('holiday edit') || Laratrust::hasPermission('holiday delete'))
                                    <td class="col-actions">
                                        <span>
                                            @permission('holiday edit')
                                            <a class="rc-table-action rc-table-action-edit"
                                                data-url="{{ URL::to('holiday/' . $holiday->id . '/edit') }}"
                                                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                data-title="{{ __('Edit Holiday') }}"
                                                title="{{ __('Edit') }}">
                                                <i class="ti ti-pencil"></i>
                                            </a>
                                            @endpermission

                                            @permission('holiday delete')
                                            {{ Form::open(array('route' => array('holiday.destroy', $holiday->id), 'class' => 'm-0 d-inline')) }}
                                            @method('DELETE')
                                            <a class="rc-table-action rc-table-action-delete bs-pass-para show_confirm"
                                                data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                aria-label="Delete" data-confirm="{{ __('Are You Sure?') }}" data-text="{{ __('This action can not be undone. Do you want to continue?') }}" data-confirm-yes="delete-form-{{ $holiday->id }}">
                                                <i class="ti ti-trash"></i>
                                            </a>
                                            {{ Form::close() }}
                                            @endpermission
                                        </span>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <x-rc-table.empty :asRow="true" :colspan="4" icon="ti ti-calendar-off" title="{{ __('No Holidays Found') }}" message="{{ __('There are no holidays to display.') }}" />
                        @endforelse
                    </tbody>
                </table>
            </x-rc-table.content>
        </x-rc-table>
    </div>
</div>
@endsection

