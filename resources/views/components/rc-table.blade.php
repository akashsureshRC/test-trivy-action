{{--
    RC Table Component
    
    A versatile, reusable table component for consistent styling across the app.
    
    Usage:
    <x-rc-table 
        :title="'Employees'" 
        :columns="$columns" 
        :data="$employees"
        :filters="$filters"
        :actions="true"
        :pagination="$employees"
        id="employees-table"
    />
    
    Or use individual parts:
    <x-rc-table.card>
        <x-rc-table.filter>...</x-rc-table.filter>
        <x-rc-table.content>
            <table class="rc-table">...</table>
        </x-rc-table.content>
        <x-rc-table.footer>...</x-rc-table.footer>
    </x-rc-table.card>
--}}

@props([
    'title' => null,
    'titleIcon' => null,
    'headerActions' => null,
    'id' => null,
    'class' => '',
])

<div {{ $attributes->merge(['class' => 'rc-table-card ' . $class]) }} @if($id) id="{{ $id }}" @endif>
    @if($title || $headerActions)
    <div class="rc-table-header">
        <h5 class="rc-table-title">
            @if($titleIcon)
                <i class="{{ $titleIcon }} me-2"></i>
            @endif
            {{ $title }}
        </h5>
        @if($headerActions)
        <div class="rc-table-actions">
            {{ $headerActions }}
        </div>
        @endif
    </div>
    @endif
    
    {{ $slot }}
</div>
