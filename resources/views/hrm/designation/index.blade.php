@extends('layouts.main')
@section('page-title')
{{ __('Designation') }}
@endsection
@section('page-breadcrumb')
{{ __('Designation') }}
@endsection
@section('page-action')
<div>
    @permission('designation create')
    <a class="btn btn-sm btn-rc-icon" data-ajax-popup="true" data-size="md" data-title="{{ __('Create New Designation') }}"
        data-url="{{ route('designation.create') }}" data-toggle="tooltip" title="{{ __('Create') }}">
        <i class="ti ti-plus text-white"></i>
    </a>
    @endpermission
</div>
@endsection
@php
$company_settings = getCompanyAllSetting();
@endphp
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
                            <th>{{ !empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch') }}
                            </th>
                            <th>{{ !empty($company_settings['hrm_department_name']) ? $company_settings['hrm_department_name'] : __('Department') }}
                            </th>
                            <th>{{ !empty($company_settings['hrm_designation_name']) ? $company_settings['hrm_designation_name'] : __('Designation') }}
                            </th>
                            <th class="col-actions">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($designations as $designation)
                        <tr>
                            <td>{{ $designation->branch_id ? $designation->branch->name ?? '' : '' }}
                            </td>
                            <td>{{ $designation->department_id ? $designation->department->name ?? '' : '' }}
                            </td>
                            <td>{{ !empty($designation->name) ? $designation->name : '' }}</td>
                            <td class="col-actions">
                                <span class="action-btn-wrapper">
                                    @permission('designation edit')
                                    <a href="#" class="rc-table-action rc-table-action-edit"
                                        data-url="{{ URL::to('designation/' . $designation->id . '/edit') }}"
                                        data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                        data-title="{{ __('Edit Designation') }}"
                                        data-bs-original-title="{{ __('Edit') }}">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    @endpermission
                                    @permission('designation delete')
                                    {{ Form::open(['route' => ['designation.destroy', $designation->id], 'class' => 'm-0 d-inline']) }}
                                    @method('DELETE')
                                    <a href="#" class="rc-table-action rc-table-action-delete bs-pass-para show_confirm"
                                        data-bs-toggle="tooltip"
                                        data-bs-original-title="Delete" aria-label="Delete"
                                        data-confirm="{{ __('Are You Sure?') }}"
                                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                        data-confirm-yes="delete-form-{{ $designation->id }}"><i
                                            class="ti ti-trash"></i></a>
                                    {{ Form::close() }}
                                    @endpermission
                                </span>
                            </td>
                        </tr>
                        @empty
                        <x-rc-table.empty :asRow="true" :colspan="4" icon="ti ti-briefcase" title="{{ __('No Designations Found') }}" message="{{ __('No designations have been created yet.') }}" />
                        @endforelse
                    </tbody>
                </table>
            </x-rc-table.content>

            <x-rc-table.footer :paginator="$designations" />
        </x-rc-table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).on('change', '#branch_id', function() {
        var branch_id = $(this).val();
        getDepartment(branch_id);
    });

    function getDepartment(branch_id) {
        var data = {
            "branch_id": branch_id,
            "_token": "{{ csrf_token() }}",
        }

        $.ajax({
            url: '{{ route('employee.getdepartments') }}',
            method: 'POST',
            data: data,
            success: function(data) {
                $('#department_id').empty();
                $('#department_id').append('<option value="" disabled>{{ __('Select Department ') }}</option>');

                $.each(data, function(key, value) {
                    $('#department_id').append('<option value="' + key + '">' + value +
                        '</option>');
                });
                $('#department_id').val('');
            }
        });
    }
</script>
<script>
    // Force System Setup sidebar menu active for designation pages
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