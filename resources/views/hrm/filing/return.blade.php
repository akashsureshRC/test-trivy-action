@extends('layouts.main')
@section('page-title')
{{ __('OID Return') }}
@endsection
@section('page-breadcrumb')
{{ __('OID Return') }}
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            {{-- Filter Bar --}}
            <div class="rc-filter-bar" style="border-top-right-radius: 8px; border-top-left-radius: 8px;">
                <form method="GET" action="{{ route('filing.return') }}" id="seasonForm">
                    <div class="rc-filter-row">
                        <div class="rc-filter-group">
                            <label class="rc-filter-label">Filing Season</label>
                            <select class="rc-filter-select" name="season" onchange="document.getElementById('seasonForm').submit()">
                                @if(isset($oidData['seasons']))
                                    @foreach($oidData['seasons'] as $seasonOption)
                                        <option value="{{ $seasonOption }}" {{ $season == $seasonOption ? 'selected' : '' }}>
                                            {{ $seasonOption }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="2025-02-28" {{ $season == '2025-02-28' ? 'selected' : '' }}>2025-02-28</option>
                                    <option value="2026-02-28" {{ $season == '2026-02-28' ? 'selected' : '' }}>2026-02-28</option>
                                    <option value="2024-02-28" {{ $season == '2024-02-28' ? 'selected' : '' }}>2024-02-28</option>
                                @endif
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('filing.return.export') }}" id="downloadForm">
                    @csrf
                    <input type="hidden" name="season" value="{{ $season }}">

                    {{-- Benefits Section --}}
                    <div class="filing-category">
                        <div class="filing-category-header">
                            <h5 class="filing-category-title">Benefits</h5>
                            <span class="filing-toggle-all" onclick="toggleCategoryCheckboxes(this, 'benefits')">Deselect All</span>
                        </div>
                        <div class="filing-checklist" data-category="benefits">
                            @if(isset($oidData['benefits']))
                                @foreach($oidData['benefits'] as $key => $benefit)
                                    <label class="filing-check-item" for="accounts_{{ $key }}">
                                        <input type="checkbox" name="accounts[{{ $key }}]" id="accounts_{{ $key }}" value="1" checked>
                                        <label for="accounts_{{ $key }}">{{ $benefit['name'] }}</label>
                                        <span class="filing-check-count {{ $benefit['count'] > 0 ? 'has-records' : 'no-records' }}">{{ $benefit['count'] }}</span>
                                    </label>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    {{-- Income Section --}}
                    <div class="filing-category">
                        <div class="filing-category-header">
                            <h5 class="filing-category-title">Income</h5>
                            <span class="filing-toggle-all" onclick="toggleCategoryCheckboxes(this, 'income')">Deselect All</span>
                        </div>
                        <div class="filing-checklist" data-category="income">
                            @if(isset($oidData['income']))
                                @foreach($oidData['income'] as $key => $income)
                                    <label class="filing-check-item" for="accounts_{{ $key }}">
                                        <input type="checkbox" name="accounts[{{ $key }}]" id="accounts_{{ $key }}" value="1" checked>
                                        <label for="accounts_{{ $key }}">{{ $income['name'] }}</label>
                                        <span class="filing-check-count {{ $income['count'] > 0 ? 'has-records' : 'no-records' }}">{{ $income['count'] }}</span>
                                    </label>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    {{-- Allowance Section --}}
                    <div class="filing-category">
                        <div class="filing-category-header">
                            <h5 class="filing-category-title">Allowances</h5>
                            <span class="filing-toggle-all" onclick="toggleCategoryCheckboxes(this, 'allowance')">Deselect All</span>
                        </div>
                        <div class="filing-checklist" data-category="allowance">
                            @if(isset($oidData['allowances']))
                                @foreach($oidData['allowances'] as $key => $allowance)
                                    <label class="filing-check-item" for="accounts_{{ $key }}">
                                        <input type="checkbox" name="accounts[{{ $key }}]" id="accounts_{{ $key }}" value="1" checked>
                                        <label for="accounts_{{ $key }}">{{ $allowance['name'] }}</label>
                                        <span class="filing-check-count {{ $allowance['count'] > 0 ? 'has-records' : 'no-records' }}">{{ $allowance['count'] }}</span>
                                    </label>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="d-flex align-items-center justify-content-between pt-4" style="border-top: 1px solid var(--rc-border);">
                        <div>
                            <span class="text-muted" style="font-size: var(--rc-font-sm);">
                                <i class="ti ti-info-circle me-1"></i>
                                Selected items will be included in the OID Return export.
                            </span>
                        </div>
                        <button type="submit" class="btn btn-rc-primary">
                            <i class="ti ti-download me-1"></i>
                            Download OID Return
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleCategoryCheckboxes(trigger, category) {
        const container = trigger.closest('.filing-category');
        const checkboxes = container.querySelectorAll('input[type="checkbox"]');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);

        checkboxes.forEach(cb => cb.checked = !allChecked);
        trigger.textContent = allChecked ? 'Select All' : 'Deselect All';
    }
</script>
@endsection