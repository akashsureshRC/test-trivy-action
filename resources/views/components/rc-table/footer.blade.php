{{--
    RC Table Footer/Pagination Component
    
    Usage with Laravel Pagination:
    <x-rc-table.footer :paginator="$employees" />
    
    Or custom:
    <x-rc-table.footer info="Showing 1-10 of 100">
        <x-slot name="pagination">
            {{ $employees->links() }}
        </x-slot>
    </x-rc-table.footer>
--}}

@props([
    'paginator' => null,
    'info' => null,
    'perPageOptions' => [10, 25, 50, 100],
])

@php
    $currentPerPage = request('per_page', $paginator ? $paginator->perPage() : 10);
    // Build the per-page URL by merging current query params
    $queryParams = request()->query();
    unset($queryParams['page']); // Reset to page 1 when changing per_page
@endphp

@if($paginator || $info || isset($pagination))
<div class="rc-table-footer">
    <div class="rc-table-left">
        @if($paginator && method_exists($paginator, 'firstItem'))
            <div class="rc-per-page">
                <label class="rc-per-page-label">{{ __('Per page') }}</label>
                <select class="rc-per-page-select" onchange="window.location.href=this.value">
                    @foreach($perPageOptions as $size)
                        @php
                            $params = array_merge($queryParams, ['per_page' => $size]);
                            $url = request()->url() . '?' . http_build_query($params);
                        @endphp
                        <option value="{{ $url }}" {{ $currentPerPage == $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="rc-table-info">
            @if($paginator && method_exists($paginator, 'firstItem'))
                {{ __('Showing') }} 
                <strong>{{ $paginator->firstItem() ?? 0 }}</strong> - 
                <strong>{{ $paginator->lastItem() ?? 0 }}</strong> 
                {{ __('of') }} 
                <strong>{{ $paginator->total() }}</strong> 
                {{ __('results') }}
            @elseif($info)
                {{ $info }}
            @endif
        </div>
    </div>
    
    <div class="rc-pagination">
        @if($paginator && method_exists($paginator, 'hasPages') && $paginator->hasPages())
            {{-- Previous --}}
            @if($paginator->onFirstPage())
                <span class="rc-pagination-btn disabled">
                    <i class="ti ti-chevron-left"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="rc-pagination-btn">
                    <i class="ti ti-chevron-left"></i>
                </a>
            @endif
            
            {{-- Page Numbers --}}
            @foreach($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                <a href="{{ $url }}" class="rc-pagination-btn {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                    {{ $page }}
                </a>
            @endforeach
            
            {{-- Next --}}
            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="rc-pagination-btn">
                    <i class="ti ti-chevron-right"></i>
                </a>
            @else
                <span class="rc-pagination-btn disabled">
                    <i class="ti ti-chevron-right"></i>
                </span>
            @endif
        @elseif(isset($pagination))
            {{ $pagination }}
        @endif
    </div>
</div>
@endif
