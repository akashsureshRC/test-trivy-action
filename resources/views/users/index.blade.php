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
{{ $plural_name}}
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
    <a href="#" class="btn btn-sm btn-rc-icon" data-ajax-popup="true" data-title="{{ __('Import User') }}"
        data-url="{{ route('users.file.import') }}" data-bs-toggle="tooltip" title="{{ __('Import User') }}">
        <i class="ti ti-file-import"></i>
    </a>
    @endpermission
    @permission('user manage')
    <a href="{{ route('users.list.view') }}" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('List View') }}"
        class="btn btn-sm btn-rc-icon">
        <i class="ti ti-list"></i>
    </a>
    @endpermission
    @permission('user create')
    <a href="#" class="btn btn-sm btn-rc-icon" data-ajax-popup="true" data-size="md"
        data-title="{{ __('Create New '.($singular_name)) }}"
        data-url="{{ route('users.create') }}" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Create') }}">
        <i class="ti ti-plus"></i>
    </a>
    @endpermission
</div>
@endsection
@section('content')
<!-- [ Main Content ] start -->
<div class="row">
    <div class="col-sm-12">
        <x-rc-table class="mb-4">
            <x-rc-table.filter action="{{ route('users.index') }}">
                <x-rc-table.filter-group label="{{ __('Name') }}" wide>
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
        </x-rc-table>

        <div class="row g-3">
            @foreach ($users as $user)
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card employee-grid-card">
                    <div class="employee-grid-status">
                        <span class="rc-status {{ $user->is_disable == 1 ? 'rc-status-success' : 'rc-status-danger' }}">
                            {{ $user->is_disable == 1 ? __('Active') : __('Suspended') }}
                        </span>
                    </div>
                    <div class="employee-grid-actions">
                        @if(Auth::user()->type == "super admin" || Auth::user()->type == "master_admin" || Laratrust::isAbleTo('user manage'))
                        <div class="dropdown">
                            @if($user->is_disable == 1 || Auth::user()->type == "super admin" || Auth::user()->type == "master_admin")
                            <button type="button" class="btn grid-actions-btn" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="feather icon-more-vertical"></i>
                            </button>
                            @else
                            <div class="btn">
                                <i class="ti ti-lock"></i>
                            </div>
                            @endif
                            <div class="dropdown-menu dropdown-menu-end">
                                @permission('user edit')
                                <a data-url="{{ route('users.edit', $user->id) }}" class="dropdown-item"
                                    data-ajax-popup="true" data-size="md" data-title="{{ __('Update '.($singular_name)) }}"
                                    data-toggle="tooltip" data-original-title="{{ __('Edit') }}">
                                    <i class="ti ti-edit text-rc-primary"></i>
                                    <span>{{ __('Edit') }}</span>
                                </a>
                                @endpermission
                                @permission('user delete')
                                {{ Form::open(['route' => ['users.destroy', $user->id], 'class' => 'm-0']) }}
                                @method('DELETE')
                                <a href="#!" class="dropdown-item bs-pass-para show_confirm" aria-label="Delete"
                                    data-confirm="{{ __('Are You Sure?') }}"
                                    data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                    data-confirm-yes="delete-form-{{ $user->id }}">
                                    <i class="ti ti-trash text-danger"></i>
                                    <span>{{ __('Delete') }}</span>
                                </a>
                                {{ Form::close() }}
                                @endpermission
                                @if(Auth::user()->type == "super admin" || Auth::user()->type == "master_admin")
                                <a href="{{ route('login.with.company',$user->id) }}" class="dropdown-item"
                                    data-bs-original-title="{{ __('Login As Company') }}">
                                    <i class="ti ti-replace text-info"></i>
                                    <span> {{ __('Login As Company') }}</span>
                                </a>
                                @endif
                                @permission('user reset password')
                                <a href="#!" data-url="{{ route('users.reset', \Crypt::encrypt($user->id)) }}"
                                    data-ajax-popup="true" data-size="md" class="dropdown-item"
                                    data-title="{{ __('Reset Password') }}"
                                    data-bs-original-title="{{ __('Reset Password') }}">
                                    <i class="ti ti-adjustments text-success"></i>
                                    <span> {{ __('Reset Password') }}</span>
                                </a>
                                @endpermission
                                @permission('user login manage')
                                @if ($user->is_enable_login == 1)
                                <a href="{{ route('users.login', \Crypt::encrypt($user->id)) }}"
                                    class="dropdown-item">
                                    <i class="ti ti-road-sign "></i>
                                    <span class="text-danger"> {{ __('Login Disable') }}</span>
                                </a>
                                @elseif ($user->is_enable_login == 0 && $user->password == null)
                                <a href="#" data-url="{{ route('users.reset', \Crypt::encrypt($user->id)) }}"
                                    data-ajax-popup="true" data-size="md" class="dropdown-item login_enable"
                                    data-title="{{ __('New Password') }}" class="dropdown-item">
                                    <i class="ti ti-road-sign"></i>
                                    <span class="text-success"> {{ __('Login Enable') }}</span>
                                </a>
                                @else
                                <a href="{{ route('users.login', \Crypt::encrypt($user->id)) }}"
                                    class="dropdown-item">
                                    <i class="ti ti-road-sign"></i>
                                    <span class="text-success"> {{ __('Login Enable') }}</span>
                                </a>
                                @endif
                                @endpermission
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="employee-avatar-wrapper">
                            @php
                            $avatarUrl = getAvatarUrl($user->avatar);
                            $initials = strtoupper(substr($user->name, 0, 1));
                            @endphp
                            @if (!empty($avatarUrl))
                            <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" class="employee-avatar">
                            @else
                            <div class="employee-avatar-placeholder">{{ $initials }}</div>
                            @endif
                        </div>
                        <div class="employee-grid-name">{{ $user->name }}</div>
                        <div class="employee-grid-id">{{ $user->email }}</div>
                        @if( Auth::user()->type == "super admin" || Auth::user()->type == "master_admin")
                        <div class="mt-4">
                            <div class="row justify-content-between align-items-center">
                                <div class="col-4 text-center">
                                    <span class="rc-status {{ $user->isOnTrial() ? 'rc-status-info' : 'rc-status-success' }}">
                                        {{ $user->isOnTrial() ? __('Trial') : __('Paid') }}
                                    </span>
                                </div>
                                <div class="col-8 text-center Id ">
                                    <a href="#" data-url="{{route('company.info', $user->id)}}" data-size="lg" data-ajax-popup="true" class="btn btn-outline-primary" data-title="{{__('Admin Hub')}}">{{__('Admin Hub')}}</a>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
            @auth('web')
            @permission('user create')
            <div class="col-xl-3 col-lg-4 col-md-6" style="min-height: 291.4px;">
                <a href="#" class="employee-add-card" data-ajax-popup="true" data-size="md"
                    data-title="{{ __('Create New '.($singular_name)) }}" data-url="{{ route('users.create') }}">
                    <div class="add-icon">
                        <i class="ti ti-plus"></i>
                    </div>
                    <h6>{{ __('New '.($singular_name)) }}</h6>
                    <p>{{ __('Click here to Create New '.($singular_name)) }}</p>
                </a>
            </div>
            @endpermission
            @endauth
        </div>
    </div>
</div>
<!-- [ Main Content ] end -->
@endsection
@push('scripts')
{{-- Password  --}}

<script>
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
        }, 2000);
    });
</script>
@endpush