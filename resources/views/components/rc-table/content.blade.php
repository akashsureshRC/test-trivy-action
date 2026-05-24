{{--
    RC Table Content Wrapper
    
    Usage:
    <x-rc-table.content>
        <table class="rc-table">
            ...
        </table>
    </x-rc-table.content>
--}}

@props([
    'responsive' => true,
])

<div {{ $attributes->merge(['class' => $responsive ? 'rc-table-responsive' : '']) }}>
    {{ $slot }}
</div>
