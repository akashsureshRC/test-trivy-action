{{--
    RC Table Filter Group Component
    
    Usage:
    <x-rc-table.filter-group label="Name" wide>
        <input type="text" name="name" class="rc-filter-input">
    </x-rc-table.filter-group>
--}}

@props([
    'label' => null,
    'wide' => false,
    'narrow' => false,
])

@php
    $sizeClass = '';
    if ($wide) $sizeClass = 'rc-filter-wide';
    if ($narrow) $sizeClass = 'rc-filter-narrow';
@endphp

<div {{ $attributes->merge(['class' => 'rc-filter-group ' . $sizeClass]) }}>
    @if($label)
        <label class="rc-filter-label">{{ $label }}</label>
    @endif
    {{ $slot }}
</div>
