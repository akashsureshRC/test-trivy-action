<div class="card sticky-top" style="top:30px">
    <div class="list-group list-group-flush" id="useradd-sidenav">
        @permission('branch manage')
            <a href="{{route('branch.index')}}" class="list-group-item list-group-item-action border-0 {{ (request()->is('branch*') ? 'active' : '')}}">{{__('Branch')}} <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
        @endpermission

        @permission('department manage')
            <a href="{{ route('department.index') }}" class="list-group-item list-group-item-action border-0 {{ (request()->is('department*') ? 'active' : '')}}">{{__('Department')}}<div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
        @endpermission

        @permission('designation manage')
            <a href="{{ route('designation.index') }}" class="list-group-item list-group-item-action border-0 {{ (request()->is('designation*') ? 'active' : '')}}">{{__('Designation')}}<div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
        @endpermission

    </div>
</div>
