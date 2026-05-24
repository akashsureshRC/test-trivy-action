{{--
    RC Table Empty State Component
    
    Usage:
    <x-rc-table.empty 
        icon="ti ti-users" 
        title="No Employees Found"
        message="Get started by adding your first employee."
    >
        <a href="{{ route('employees.create') }}" class="btn btn-rc-primary">
            <i class="ti ti-plus me-1"></i> Add Employee
        </a>
    </x-rc-table.empty>
--}}

@props([
    'icon' => 'ti ti-database-off',
    'title' => 'No Data Found',
    'message' => 'There are no records to display.',
    'colspan' => 1,
    'asRow' => false,
])

@if($asRow)
<tr>
    <td colspan="{{ $colspan }}">
@endif

<div class="rc-table-empty">
    <div class="rc-table-empty-icon">
        <i class="{{ $icon }}"></i>
    </div>
    <h5 class="rc-table-empty-title">{{ $title }}</h5>
    <p class="rc-table-empty-text">{{ $message }}</p>
    @if($slot->isNotEmpty())
    <div class="rc-table-empty-action">
        {{ $slot }}
    </div>
    @endif
</div>

@if($asRow)
    </td>
</tr>
@endif
