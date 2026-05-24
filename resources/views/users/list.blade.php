@extends('layouts.main')
@php
if(Auth::user()->type=='super admin' || Auth::user()->type=='master_admin')
{
$plural_name = __('Customers');
$singular_name = __('Customer');
}
else{

$plural_name =__('Users');
$singular_name =__('User');
}

// Define display names for system roles
$roleDisplayNames = [
'super admin' => 'Global Administrator',
'master_admin' => 'Master Administrator',
'company' => 'Company Administrator',
'payroll_officer' => 'Payroll Officer',
];
@endphp

@section('page-title')
{{$plural_name}}
@endsection
@section('page-breadcrumb')
{{ $plural_name}}
@endsection
@section('page-action')
<div class="d-flex gap-2">
    @permission('user logs history')
    <a href="{{ route('users.userlog.history') }}" class="btn btn-sm btn-rc-icon"
        data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('User Logs History') }}">
        <i class="ti ti-user-check"></i>
    </a>
    @endpermission

    @permission('user import')
    <a href="#" class="btn btn-sm btn-rc-icon" data-ajax-popup="true" data-title="{{ __('Import') }}"
        data-url="{{ route('users.file.import') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Import') }}">
        <i class="ti ti-file-import"></i>
    </a>
    @endpermission
    @permission('user manage')
    <a href="{{ route('users.index') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Grid View') }}"
        class="btn btn-sm btn-rc-icon">
        <i class="ti ti-layout-grid"></i>
    </a>
    @endpermission
    @permission('user create')
    <a href="#" class="btn btn-sm btn-rc-icon"
        data-ajax-popup="true" data-size="md" data-title="{{ __('Create New '.($singular_name)) }}" data-url="{{ route('users.create') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Create') }}">
        <i class="ti ti-plus"></i>
    </a>
    @endpermission
</div>
@endsection
@section('content')
<!-- [ Main Content ] start -->
<div class="row">
    <div class="col-sm-12">
        <x-rc-table>
            <x-rc-table.filter action="{{ route('users.list.view') }}" method="GET" id="user_submit">
                <x-rc-table.filter-group label="Name" wide>
                    <input type="text" name="name" class="rc-filter-input" placeholder="{{ __('Enter Name') }}" value="{{ request('name') }}">
                </x-rc-table.filter-group>
                <x-rc-table.filter-group label="{{ __('Status') }}" narrow>
                    <select name="status" class="rc-filter-select">
                        <option value="">{{ __('All') }}</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>{{ __('Suspended') }}</option>
                    </select>
                </x-rc-table.filter-group>
                @if(Auth::user()->type == 'super admin' || Auth::user()->type == 'master_admin')
                <x-rc-table.filter-group label="{{ __('Plan') }}" narrow>
                    <select name="plan_type" class="rc-filter-select">
                        <option value="">{{ __('All') }}</option>
                        <option value="trial" {{ request('plan_type') == 'trial' ? 'selected' : '' }}>{{ __('Trial') }}</option>
                        <option value="paid" {{ request('plan_type') == 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                    </select>
                </x-rc-table.filter-group>
                @endif
            </x-rc-table.filter>

            {{-- Table Content --}}
            <x-rc-table.content>
                <table class="rc-table" id="users">
                    <thead>
                        <tr>
                            <th class="col-sno">#</th>
                            <th class="col-avatar">{{ __('Picture') }}</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th class="col-status">{{ __('Login Status') }}</th>
                            <th class="col-status">{{ __('Account Status') }}</th>
                            @if(Auth::user()->type == 'super admin' || Auth::user()->type == 'master_admin')
                            <th class="col-status">{{ __('Plan') }}</th>
                            @endif
                            @if (Laratrust::hasPermission('user edit') || Laratrust::hasPermission('user delete'))
                            <th class="col-actions">{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td class="col-sno">{{ $users->firstItem() + $loop->index }}</td>
                            <td class="col-avatar">
                                @php
                                $avatarUrl = getAvatarUrl($user->avatar);
                                @endphp
                                @if (!empty($avatarUrl))
                                <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" class="rc-table-avatar">
                                @else
                                <div class="rc-table-avatar-placeholder">
                                    <span>{{ substr($user->name, 0, 1) }}</span>
                                </div>
                                @endif
                            </td>
                            <td>
                                <span class="text-primary-cell">{{$user->name}}</span>
                            </td>
                            <td>{{$user->email}}</td>
                            @php
                            $loginStatus = $user->is_enable_login == 1 ? __('Enabled') : __('Disabled');
                            $accountStatus = $user->is_disable == 1 ? __('Active') : __('Suspended');
                            @endphp
                            <td class="col-status">
                                <span class="rc-status {{ $user->is_enable_login == 1 ? 'rc-status-success' : 'rc-status-danger' }}">
                                    {{ $loginStatus }}
                                </span>
                            </td>
                            <td class="col-status">
                                <span class="rc-status {{ $user->is_disable == 1 ? 'rc-status-success' : 'rc-status-danger' }}">
                                    {{ $accountStatus }}
                                </span>
                            </td>
                            @if(Auth::user()->type == 'super admin' || Auth::user()->type == 'master_admin')
                            <td class="col-status">
                                <span class="rc-status {{ $user->isOnTrial() ? 'rc-status-info' : 'rc-status-success' }}">
                                    {{ $user->isOnTrial() ? __('Trial') : __('Paid') }}
                                </span>
                            </td>
                            @endif
                            <td class="col-actions">
                                @if($user->is_disable == 1 || Auth::user()->type == 'super admin' || Auth::user()->type == 'master_admin')
                                @if(Auth::user()->type == "super admin" || Auth::user()->type == "master_admin")
                                <a data-url="{{ route('company.info',$user->id) }}" data-ajax-popup="true" data-size="lg"
                                    data-title="{{__('Admin Hub')}}" class="rc-table-action rc-table-action-view"
                                    data-bs-toggle="tooltip" data-bs-original-title="{{ __('Admin Hub')}}">
                                    <i class="ti ti-atom"></i></a>
                                <a href="{{ route('login.with.company',$user->id) }}"
                                    class="rc-table-action rc-table-action-neutral" data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('Login As Company')}}">
                                    <i class="ti ti-replace"></i></a>
                                @endif
                                @permission('user reset password')
                                <a href="#" data-url="{{ route('users.reset', \Crypt::encrypt($user->id)) }}"
                                    data-ajax-popup="true" data-size="md" data-title="{{ __('Reset Password') }}"
                                    class="rc-table-action rc-table-action-warning" data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('Reset Password')}}">
                                    <i class="ti ti-adjustments"></i></a>
                                @endpermission
                                @permission('user login manage')
                                @if ($user->is_enable_login == 1)
                                <a href="{{ route('users.login', [\Crypt::encrypt($user->id), 'view' => 'list']) }}"
                                    class="rc-table-action rc-table-action-danger" data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('Login Disable')}}">
                                    <i class="ti ti-road-sign"></i></a>
                                @elseif ($user->is_enable_login == 0 && $user->password == null)
                                <a href="#" data-url="{{ route('users.reset', \Crypt::encrypt($user->id)) }}"
                                    data-ajax-popup="true" data-size="md" data-title="{{ __('New Password') }}"
                                    class="rc-table-action rc-table-action-neutral login_enable" data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('New Password')}}"><i class="ti ti-road-sign"></i></a>
                                @else
                                <a href="{{ route('users.login', [\Crypt::encrypt($user->id), 'view' => 'list']) }}"
                                    class="rc-table-action rc-table-action-success login_enable" data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('Login Enable')}}"><i class="ti ti-road-sign"></i></a>
                                @endif
                                @endpermission
                                @permission('user edit')
                                <a href="#" data-url="{{ route('users.edit', $user->id) }}"
                                    data-ajax-popup="true" data-size="md" data-title="{{ __('Update User') }}"
                                    class="rc-table-action rc-table-action-edit" data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('Edit')}}">
                                    <i class="ti ti-edit"></i></a>
                                @endpermission
                                @permission('user delete')
                                {{ Form::open(['route' => ['users.destroy', $user->id], 'class' => 'm-0 d-inline']) }}
                                @method('DELETE')
                                <a href="#" class="rc-table-action rc-table-action-delete bs-pass-para show_confirm"  aria-label="Delete" data-confirm-yes="delete-form-{{ $user->id }}" data-bs-toggle="tooltip"data-bs-original-title="{{ __('Delete')}}">
                                    <i class="ti ti-trash"></i>
                                </a>
                                {{ Form::close() }}
                                @endpermission
                                @else
                                <div class="text-center">
                                    <i class="ti ti-lock"></i>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        @php
                            $baseCols = (Auth::user()->type == 'super admin' || Auth::user()->type == 'master_admin') ? 7 : 6;
                            $emptyCols = (Laratrust::hasPermission('user edit') || Laratrust::hasPermission('user delete')) ? $baseCols + 1 : $baseCols;
                        @endphp
                        <x-rc-table.empty :asRow="true" :colspan="$emptyCols" icon="ti ti-users" title="{{ __('No Records') }}" message="{{ __('No users found.') }}" />
                        @endforelse
                    </tbody>
                </table>
            </x-rc-table.content>

            <x-rc-table.footer :paginator="$users" />
        </x-rc-table>
    </div>
</div>
</div>
<!-- [ Main Content ] end -->
@endsection
@push('scripts')
{{-- Password  --}}
<script>
    function initTooltips() {
        if (window.bootstrap && bootstrap.Tooltip) {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
                if (!el._tooltipInstance) {
                    el._tooltipInstance = new bootstrap.Tooltip(el, {
                        placement: 'top',
                        trigger: 'hover focus'
                    });
                }
            });
            return;
        }

        if (window.jQuery && typeof jQuery.fn.tooltip === 'function') {
            jQuery('[data-bs-toggle="tooltip"]').tooltip({
                placement: 'top'
            });
        }
    }

    document.addEventListener('DOMContentLoaded', initTooltips);
    window.addEventListener('load', initTooltips);

    $(document).on('change', '#password_switch', function() {
        if ($(this).is(':checked')) {
            $('.ps_div').removeClass('d-none');
            $('#password').attr("required", true);

        } else {
            $('.ps_div').addClass('d-none');
            $('#password').val(null);
            $('#password').removeAttr("required");
        }
    });
    $(document).on('click', '.login_enable', function() {
        setTimeout(function() {
            $('.modal-body').append($('<input>', {
                type: 'hidden',
                val: 'true',
                name: 'login_enable'
            }));
        }, 1000);
    });
</script>

@endpush