@extends('layouts.main')
@php
$company_settings = getCompanyAllSetting();
@endphp
@section('page-title')
{{!empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department') }}
@endsection
@section('page-breadcrumb')
{{ __('Department') }}
@endsection
@section('page-action')
<div>
    @permission('department create')
    <a class="btn btn-sm btn-rc-icon"
        data-ajax-popup="true" data-size="md" data-title="{{ __('Create New Department') }}" data-url="{{route('department.create')}}" data-toggle="tooltip" title="{{ __('Create') }}">
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
                            <th>{{!empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department') }}</th>
                            <th class="col-actions">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($departments as $department)
                        <tr>
                            <td>{{ !empty($department->branch) ? ($department->branch->name ) ?? '' : '' }}</td>
                            <td>{{ $department->name }}</td>
                            <td class="col-actions">
                                <span class="action-btn-wrapper">
                                    @permission('department edit')
                                    <a href="#" class="rc-table-action rc-table-action-edit"
                                        data-url="{{ URL::to('department/' . $department->id . '/edit') }}"
                                        data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                        data-title="{{ __('Edit Department') }}"
                                        data-bs-original-title="{{ __('Edit') }}">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    @endpermission
                                    @permission('department delete')
                                    {{Form::open(array('route'=>array('department.destroy', $department->id),'class' => 'm-0 d-inline'))}}
                                    @method('DELETE')
                                    <a href="#" class="rc-table-action rc-table-action-delete bs-pass-para show_confirm"
                                        data-bs-toggle="tooltip" data-bs-original-title="Delete"
                                        aria-label="Delete" data-confirm="{{__('Are You Sure?')}}" data-text="{{__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="delete-form-{{$department->id}}"><i
                                            class="ti ti-trash"></i></a>
                                    {{Form::close()}}
                                    @endpermission
                                </span>
                            </td>
                        </tr>
                        @empty
                        <x-rc-table.empty :asRow="true" :colspan="3" icon="ti ti-building" title="{{ __('No Departments Found') }}" message="{{ __('No departments have been created yet.') }}" />
                        @endforelse
                    </tbody>
                </table>
            </x-rc-table.content>

            <x-rc-table.footer :paginator="$departments" />
        </x-rc-table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Force System Setup sidebar menu active for department pages
    document.addEventListener('DOMContentLoaded', function() {
        var sidebarLinks = document.querySelectorAll('.dash-sidebar .dash-navbar a.dash-link');
        sidebarLinks.forEach(function(link) {
            var href = link.getAttribute('href') || '';
            if (href.indexOf('branch') !== -1) {
                link.parentNode.classList.add('active');
                var parentLi = link.parentNode.parentNode.parentNode;
                if (parentLi) {
                    parentLi.classList.add('active');
                    parentLi.classList.add('dash-trigger');
                    var submenu = link.parentNode.parentNode;
                    if (submenu) submenu.style.display = 'block';
                }
            }
        });
    });
</script>
@endpush