@extends('layouts.main')
@php
$company_settings = getCompanyAllSetting();
@endphp
@section('page-title')
{{ __(!empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : 'Branch') }}
@endsection
@section('page-breadcrumb')
{{ __('Branch') }}
@endsection
@section('page-action')
<div>
    @permission('branch create')
    <a class="btn btn-sm btn-rc-icon"
        data-ajax-popup="true" data-size="lg" data-title="{{ __('Create New Branch') }}" data-url="{{route('branch.create')}}" data-toggle="tooltip" title="{{ __('Create') }}">
        <i class="ti ti-plus text-white"></i>
    </a>
    @endpermission
</div>
@endsection
@section('content')
<div class="row">
    <div class="col-sm-3">
        @include('hrm.layouts.hrm_setup')
    </div>
    <div class="col-sm-9">
        <x-rc-table>
            <x-rc-table.content>
                <table class="rc-table">
                    <thead>
                        <tr>
                            <th>{{!empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch')}}</th>
                            <th>{{__('Address')}}</th>
                            <th class="col-actions">{{__('Action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($branches as $branch)
                        <tr>
                            <td>{{ !empty($branch->name) ? $branch->name : '' }}</td>
                            <td>{{ $branch->address ?? '-' }}</td>
                            <td class="col-actions">
                                <span>
                                    @permission('branch edit')
                                    <a href="#" class="rc-table-action rc-table-action-edit"
                                        data-url="{{ URL::to('branch/' . $branch->id . '/edit') }}"
                                        data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip" title=""
                                        data-title="{{ __('Edit Branch') }}"
                                        data-bs-original-title="{{ __('Edit') }}">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <a href="#" class="rc-table-action rc-table-action-view"
                                        data-url="{{ route('branch.working-hours', $branch->id) }}"
                                        data-ajax-popup="true" data-size="xl" data-bs-toggle="tooltip" title=""
                                        data-title="{{ __('Working Hours - ') . $branch->name }}"
                                        data-bs-original-title="{{ __('Working Hours') }}">
                                        <i class="ti ti-clock"></i>
                                    </a>
                                    @endpermission
                                    @permission('branch delete')
                                    {{Form::open(array('route'=>array('branch.destroy', $branch->id),'class' => 'm-0 d-inline'))}}
                                    @method('DELETE')
                                    <a href="#" class="rc-table-action rc-table-action-delete bs-pass-para show_confirm"
                                        data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                        aria-label="Delete" data-confirm="{{__('Are You Sure?')}}" data-text="{{__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="delete-form-{{$branch->id}}"><i
                                            class="ti ti-trash"></i></a>
                                    {{Form::close()}}
                                    @endpermission
                                </span>
                            </td>
                        </tr>
                        @empty
                        <x-rc-table.empty :asRow="true" :colspan="3" icon="ti ti-building" title="{{ __('No Branches Found') }}" message="{{ __('No branches have been created yet.') }}" />
                        @endforelse
                    </tbody>
                </table>
            </x-rc-table.content>

            <x-rc-table.footer :paginator="$branches" />
        </x-rc-table>
    </div>
</div>
@endsection