@extends('layouts.main')

@section('page-title')
{{ __('User Management') }}
@endsection

@section('page-breadcrumb')
{{ __('User Management') }}
@endsection

@push('css')
<style>
    /* Page-specific styles */
    .badge-company {
        color: #51459d;
        background: #e1ddff;
        border-radius: 4px;
        padding: 4px 8px;
        margin: 2px;
        display: inline-block;
        font-size: 11px;
    }
    .badge-role {
        border-radius: 4px;
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 600;
    }
    .badge-role-global {
        color: #b45309;
        background: #fef3c7;
    }
    .badge-role-master {
        color: #51459d;
        background: #e1ddff;
    }
</style>
@endpush

@section('page-action')
<div>
    <a href="#" class="btn btn-sm btn-rc-icon" data-url="{{ route('master-admin.create') }}"
        data-size="lg" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Create') }}" data-ajax-popup="true"
        data-title="{{ __('Create Administrator') }}">
        <i class="ti ti-plus text-white"></i>
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <x-rc-table>
            <x-rc-table.filter action="{{ route('master-admin.index') }}">
                <x-rc-table.filter-group label="{{ __('Role') }}" narrow>
                    <select name="role" class="rc-filter-select">
                        <option value="">{{ __('All Roles') }}</option>
                        @foreach($roleOptions as $value => $label)
                        <option value="{{ $value }}" {{ request('role') == $value ? 'selected' : '' }}>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </x-rc-table.filter-group>
            </x-rc-table.filter>
            <x-rc-table.content>
                <table class="rc-table" id="master-admin-table">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th>{{ __('Role') }}</th>
                            <th>{{ __('Assigned Customers') }}</th>
                            <th class="col-date">{{ __('Created') }}</th>
                            <th class="col-actions">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($admins as $admin)
                        <tr>
                            <td style="font-weight: 600">{{ $admin->name }}</td>
                            <td>{{ $admin->email }}</td>
                            <td>
                                @if($admin->type === 'super admin')
                                <span class="badge-role badge-role-global">{{ __('Global Administrator') }}</span>
                                @else
                                <span class="badge-role badge-role-master">{{ __('Master Administrator') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($admin->type === 'master_admin')
                                    @if($admin->assignedCompanies->count() > 0)
                                    @foreach($admin->assignedCompanies as $company)
                                    <span class="badge-company">{{ $company->name }}</span>
                                    @endforeach
                                    @else
                                    <span class="text-muted">{{ __('No customers assigned') }}</span>
                                    @endif
                                @else
                                <span class="text-muted">{{ __('All customers (Global)') }}</span>
                                @endif
                            </td>
                            <td class="col-date">{{ formatDate($admin->created_at) }}</td>
                            <td class="col-actions">
                                @if($admin->type === 'master_admin')
                                <a href="#" data-url="{{ route('master-admin.manage-companies', $admin->id) }}"
                                    data-ajax-popup="true" data-size="lg"
                                    data-title="{{ __('Manage Customers') }}"
                                    class="rc-table-action rc-table-action-view" data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('Manage Customers') }}">
                                    <i class="ti ti-building"></i>
                                </a>
                                @endif
                                <a href="#" data-url="{{ route('master-admin.edit', $admin->id) }}"
                                    data-ajax-popup="true" data-size="lg"
                                    data-title="{{ __('Edit Administrator') }}"
                                    class="rc-table-action rc-table-action-edit" data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('Edit') }}">
                                    <i class="ti ti-edit"></i>
                                </a>
                                {!! Form::open(['method' => 'DELETE', 'route' => ['master-admin.destroy', $admin->id], 'id' => 'delete-form-' . $admin->id, 'style' => 'display:inline']) !!}
                                <a href="#" class="rc-table-action rc-table-action-delete show_confirm"
                                    data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('Delete') }}">
                                    <i class="ti ti-trash"></i>
                                </a>
                                {!! Form::close() !!}
                            </td>
                        </tr>
                        @empty
                        <x-rc-table.empty :asRow="true" :colspan="6" icon="ti ti-users" title="{{ __('No Administrators') }}" message="{{ __('Click the + button to create one.') }}" />
                        @endforelse
                    </tbody>
                </table>
            </x-rc-table.content>

            <x-rc-table.footer :paginator="$admins" />
        </x-rc-table>
    </div>
</div>
@endsection