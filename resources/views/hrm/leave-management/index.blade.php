@extends('layouts.main')

@section('page-title')
{{ __(' Leave Management') }}
@endsection

@section('page-breadcrumb')
{{ __('Leave Management') }}
@endsection

@section('page-action')
<div>
    @permission('leave create')
        <a href="{{ route('hrm.leave-management.create') }}" class="btn btn-sm btn-rc-icon" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus text-white"></i>
        </a>
    @endpermission
</div>
@endsection

@push('css')
<style>
/* Page-specific styles only */
.returntext {
    background: #414F9D;
    background: linear-gradient(to left, #414F9D 0%, #75378D 50%, #9B357C 69%, #C33C56 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
</style>
@endpush
@section('content')
@if (Auth::check())
    <div class="row">
        <div class="col-sm-12">
            <x-rc-table>
                <x-rc-table.content>
                    <table class="rc-table">
                        <thead>
                            <tr>
                                <th class="col-sno">{{ __('S.No') }}</th>
                                <th>{{ __('Leave Name') }}</th>
                                <th class="col-actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaveManagements as $leave)
                                <tr>
                                    <td class="col-sno">{{ $leaveManagements->firstItem() + $loop->index }}</td>
                                    <td class="font-style">{{ $leave->leave_name }}</td>
                                    <td class="col-actions">
                                        <a href="{{ route('entitlement-policies.index', ['leave' => $leave->id]) }}"
                                            class="rc-table-action rc-table-action-view" data-bs-toggle="tooltip"
                                            data-bs-original-title="{{ __('View Policies') }}">
                                            <i class="ti ti-eye"></i>
                                        </a>

                                        @permission('leave delete')
                                            <form action="{{ route('hrm.leave-management.destroy', $leave->id) }}"
                                                method="POST" class="d-inline" id="delete-form-{{ $leave->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <a href="#" class="rc-table-action rc-table-action-delete show_confirm"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-original-title="{{ __('Delete') }}">
                                                    <i class="ti ti-trash"></i>
                                                </a>
                                            </form>
                                        @endpermission
                                    </td>
                                </tr>
                            @empty
                                <x-rc-table.empty :asRow="true" :colspan="3" icon="ti ti-calendar-off" title="{{ __('No Leave Types') }}" message="{{ __('No leave management records found.') }}" />
                            @endforelse
                        </tbody>
                    </table>
                </x-rc-table.content>
                <x-rc-table.footer :paginator="$leaveManagements" />
            </x-rc-table>
        </div>
    </div>
@else
<div class="alert alert-danger text-center">
    {{ __('You must be logged in to access this page.') }}
</div>
@endif
@endsection

@push('scripts')
<script>
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
<script>
    // Force Leave sidebar menu active for leave-management page
    document.addEventListener('DOMContentLoaded', function() {
        var sidebarLinks = document.querySelectorAll('.dash-sidebar .dash-navbar a.dash-link');
        sidebarLinks.forEach(function(link) {
            var href = link.getAttribute('href') || '';
            if (href.indexOf('leave-management') !== -1) {
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