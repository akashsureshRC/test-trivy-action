{{--
    RC Table Filter Component
    
    Usage:
    <x-rc-table.filter action="{{ route('employees.index') }}" method="GET">
<x-rc-table.filter-group label="Name">
    <input type="text" name="name" class="rc-filter-input" placeholder="Search name...">
</x-rc-table.filter-group>
<x-rc-table.filter-group label="Status">
    <select name="status" class="rc-filter-select">
        <option value="">All</option>
    </select>
</x-rc-table.filter-group>
</x-rc-table.filter>
--}}

@props([
'action' => null,
'method' => 'GET',
'id' => null,
])

<div class="rc-filter-bar">
    <form
        @if($action) action="{{ $action }}" @endif
        method="{{ $method }}"
        @if($id) id="{{ $id }}" @endif
        class="rc-filter-form">
        @if($method !== 'GET')
        @csrf
        @endif

        <div class="rc-filter-row">
            {{ $slot }}

            @if(isset($actions))
            {{ $actions }}
            @else
            <div class="rc-filter-actions">
                <button type="submit" class="rc-btn-filter rc-btn-filter-primary rc-btn-filter-icon" data-title="{{ __('Apply Filter') }}" data-bs-toggle="tooltip"
                    data-bs-original-title="{{ __('Apply Filter') }}">
                    <i class="ti ti-search"></i>
                </button>
                @if($action)
                <a href="{{ $action }}" class="rc-btn-filter rc-btn-filter-reset rc-btn-filter-icon" data-title="{{ __('Reset') }}" data-bs-toggle="tooltip"
                    data-bs-original-title="{{ __('Reset') }}">
                    <i class="ti ti-refresh"></i>
                </a>
                @endif
            </div>
            @endif
        </div>
    </form>
</div>