@extends('layouts.main')

@section('page-title')
    {{ __('Pricing Tiers') }}
@endsection

@section('page-breadcrumb')
    {{ __('Billing') }}, {{ __('Pricing Tiers') }}
@endsection

@section('page-action')
<div></div>
@endsection

@section('content')
<!-- Pricing Tiers Table -->
<div class="row">
    <div class="col-12">
        @php
            $editorTiers = collect(old('tiers', $tiers->map(function($tier) {
                return [
                    'id' => $tier->id,
                    'name' => $tier->name,
                    'min_payslips' => $tier->min_payslips,
                    'max_payslips' => $tier->max_payslips,
                    'price_per_payslip' => $tier->price_per_payslip,
                ];
            })->toArray()));

            if ($editorTiers->isEmpty()) {
                $editorTiers = collect([[ 
                    'id' => null,
                    'name' => '',
                    'min_payslips' => 1,
                    'max_payslips' => null,
                    'price_per_payslip' => '',
                ]]);
            }

            $baseTiers = $tiers->sortBy('sort_order')->values()->map(function($tier) {
                return [
                    'id' => (int) $tier->id,
                    'name' => (string) $tier->name,
                    'min_payslips' => (int) $tier->min_payslips,
                    'max_payslips' => is_null($tier->max_payslips) ? null : (int) $tier->max_payslips,
                    'price_per_payslip' => number_format((float) $tier->price_per_payslip, 2, '.', ''),
                ];
            })->toArray();
        @endphp

        <x-rc-table title="{{ __('Pricing Tiers') }}" class="mb-4">
            <x-slot name="headerActions">
                <div class="d-flex gap-2">
                    <button type="button" id="add-tier-btn" class="btn btn-rc-outline btn-sm">
                        <i class="ti ti-plus me-1"></i>{{ __('Add Tier Row') }}
                    </button>
                    <button type="button" id="reset-tiers-btn" class="btn btn-rc-outline btn-sm" disabled>
                        <i class="ti ti-refresh me-1"></i>{{ __('Reset') }}
                    </button>
                    <button type="submit" id="save-tiers-btn" form="tiers-bulk-form" class="btn btn-rc-primary btn-sm" disabled>
                        <i class="ti ti-device-floppy me-1"></i>{{ __('Save Changes') }}
                    </button>
                </div>
            </x-slot>
            <x-rc-table.content>
                @if($errors->any())
                    <div class="alert alert-danger mb-3 py-2 px-3">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('billing.tiers.bulk-save') }}" method="POST" id="tiers-bulk-form">
                    @csrf
                <table class="rc-table">
                    <thead>
                        <tr>
                            <th class="col-sno">{{ __('Order') }}</th>
                            <th>{{ __('Tier Name') }}</th>
                            <th>{{ __('Min Payslips') }}</th>
                            <th>{{ __('Max Payslips') }}</th>
                            <th class="col-amount">{{ __('Price/Payslip') }}</th>
                            <th class="col-actions">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="tiers-editor-body">
                        @foreach($editorTiers as $index => $tier)
                        <tr class="tier-row">
                            <td class="col-sno">
                                <span class="badge bg-secondary tier-order">{{ $index + 1 }}</span>
                                <input type="hidden" name="tiers[{{ $index }}][id]" value="{{ $tier['id'] }}">
                            </td>
                            <td>
                                <input type="text" name="tiers[{{ $index }}][name]" value="{{ $tier['name'] }}" class="form-control" required>
                            </td>
                            <td>
                                <input type="number" min="1" name="tiers[{{ $index }}][min_payslips]" value="{{ $tier['min_payslips'] }}" class="form-control" required>
                            </td>
                            <td>
                                <input type="number" min="1" name="tiers[{{ $index }}][max_payslips]" value="{{ $tier['max_payslips'] }}" class="form-control" placeholder="{{ __('Leave empty for open-ended') }}">
                            </td>
                            <td class="col-amount">
                                <input type="number" min="0" step="0.01" name="tiers[{{ $index }}][price_per_payslip]" value="{{ $tier['price_per_payslip'] }}" class="form-control" required>
                            </td>
                            <td class="col-actions">
                                <button type="button" class="rc-table-action rc-table-action-delete remove-tier-btn" title="{{ __('Remove') }}">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </form>
            </x-rc-table.content>
        </x-rc-table>
    </div>

    <!-- Pricing Calculator Preview -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Pricing Calculator Preview') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group mb-0">
                                    <label class="form-label">{{ __('Number of Payslips') }}</label>
                                    <input type="number" id="payslip-calculator" class="form-control" 
                                        placeholder="{{ __('Enter payslip count') }}" min="1" value="50">
                                </div>
                            </div>
                            <div class="col-md-4 align-self-end">
                                <button type="button" id="calculate-price" class="btn btn-rc-primary w-100">
                                    <i class="ti ti-calculator me-1"></i>{{ __('Calculate') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div id="price-result" class="bg-light rounded p-3">
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">{{ __('Total Amount') }}</small>
                                    <h4 class="mb-0 text-primary" id="calculated-total">R0.00</h4>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">{{ __('Avg per Payslip') }}</small>
                                    <h4 class="mb-0 text-success" id="calculated-avg">R0.00</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="price-breakdown" class="mt-3" style="display: none;">
                    <h6>{{ __('Breakdown by Tier') }}</h6>
                    <div id="breakdown-content"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    var removeLabel = @json(__('Remove'));
    var maxPlaceholder = @json(__('Leave empty for open-ended'));
    var atLeastOneTierMessage = @json(__('At least one tier is required.'));
    var warningLabel = @json(__('Warning'));
    var resetConfirmMessage = @json(__('Discard all unsaved changes?'));
    var resetTargetUrl = @json(route('billing.tiers.index'));
    var baseTiers = @json($baseTiers);
    var saveBtn = $('#save-tiers-btn');
    var resetBtn = $('#reset-tiers-btn');
    var isDirty = false;

    function setDirty(dirty) {
        isDirty = dirty;
        saveBtn.prop('disabled', !dirty);
        resetBtn.prop('disabled', !dirty);
    }

    function getCurrentTiers() {
        var rows = [];
        $('#tiers-editor-body .tier-row').each(function() {
            var idRaw = $(this).find('input[name$="[id]"]').val();
            var minRaw = $(this).find('input[name$="[min_payslips]"]').val();
            var maxRaw = $(this).find('input[name$="[max_payslips]"]').val();
            var priceRaw = $(this).find('input[name$="[price_per_payslip]"]').val();

            rows.push({
                id: idRaw ? parseInt(idRaw, 10) : null,
                name: ($(this).find('input[name$="[name]"]').val() || '').trim(),
                min_payslips: minRaw ? parseInt(minRaw, 10) : null,
                max_payslips: maxRaw ? parseInt(maxRaw, 10) : null,
                price_per_payslip: priceRaw === '' ? '' : (parseFloat(priceRaw).toFixed(2)),
            });
        });
        return rows;
    }

    function refreshDirtyState() {
        var current = JSON.stringify(getCurrentTiers());
        var baseline = JSON.stringify(baseTiers);
        setDirty(current !== baseline);
    }

    function reindexTierRows() {
        $('#tiers-editor-body .tier-row').each(function(index) {
            $(this).find('.tier-order').text(index + 1);
            $(this).find('input[name$="[id]"]').attr('name', 'tiers[' + index + '][id]');
            $(this).find('input[name$="[name]"]').attr('name', 'tiers[' + index + '][name]');
            $(this).find('input[name$="[min_payslips]"]').attr('name', 'tiers[' + index + '][min_payslips]');
            $(this).find('input[name$="[max_payslips]"]').attr('name', 'tiers[' + index + '][max_payslips]');
            $(this).find('input[name$="[price_per_payslip]"]').attr('name', 'tiers[' + index + '][price_per_payslip]');
        });
    }

    $('#add-tier-btn').on('click', function() {
        var rowCount = $('#tiers-editor-body .tier-row').length;
        var rowHtml = '<tr class="tier-row">'
            + '<td class="col-sno"><span class="badge bg-secondary tier-order">' + (rowCount + 1) + '</span><input type="hidden" name="tiers[' + rowCount + '][id]" value=""></td>'
            + '<td><input type="text" name="tiers[' + rowCount + '][name]" class="form-control" required></td>'
            + '<td><input type="number" min="1" name="tiers[' + rowCount + '][min_payslips]" class="form-control" required></td>'
            + '<td><input type="number" min="1" name="tiers[' + rowCount + '][max_payslips]" class="form-control" placeholder="' + maxPlaceholder + '"></td>'
            + '<td class="col-amount"><input type="number" min="0" step="0.01" name="tiers[' + rowCount + '][price_per_payslip]" class="form-control" required></td>'
            + '<td class="col-actions"><button type="button" class="rc-table-action rc-table-action-delete remove-tier-btn" title="' + removeLabel + '"><i class="ti ti-trash"></i></button></td>'
            + '</tr>';

        $('#tiers-editor-body').append(rowHtml);
        reindexTierRows();
        refreshDirtyState();
    });

    $(document).on('click', '.remove-tier-btn', function() {
        if ($('#tiers-editor-body .tier-row').length <= 1) {
            toastrs(warningLabel, atLeastOneTierMessage, 'warning');
            return;
        }
        $(this).closest('.tier-row').remove();
        reindexTierRows();
        refreshDirtyState();
    });

    $(document).on('input change', '#tiers-editor-body input', function() {
        refreshDirtyState();
    });

    $('#reset-tiers-btn').on('click', function() {
        if (!isDirty) {
            return;
        }
        openRcConfirmDialog('Are you sure?', resetConfirmMessage).then((result) => {
            if (result.isConfirmed) {
                window.location.href = resetTargetUrl;
            }
        });
    });

    refreshDirtyState();

    // Pricing calculator
    $('#calculate-price').on('click', function() {
        var count = parseInt($('#payslip-calculator').val()) || 0;
        if (count < 1) {
            toastrs('Warning', '{{ __("Please enter a valid payslip count") }}', 'warning');
            return;
        }
        
        // Calculate based on tiers (cumulative) - matches PHP logic
        var tiers = @json($tiers->sortBy('sort_order')->values());
        var total = 0;
        var remaining = count;
        var currentPayslip = 1;
        var breakdown = [];
        var lastTier = null;
        
        tiers.forEach(function(tier) {
            if (remaining <= 0) return;
            
            lastTier = tier;
            
            var tierMax = tier.max_payslips || Number.MAX_SAFE_INTEGER;
            var payslipsInTier = Math.min(remaining, tierMax - currentPayslip + 1);
            
            if (payslipsInTier > 0) {
                var tierCost = payslipsInTier * parseFloat(tier.price_per_payslip);
                total += tierCost;
                remaining -= payslipsInTier;
                
                breakdown.push({
                    name: tier.name,
                    count: payslipsInTier,
                    rate: tier.price_per_payslip,
                    cost: tierCost
                });
                
                currentPayslip = (tier.max_payslips || (currentPayslip + payslipsInTier)) + 1;
            }
        });
        
        // If payslips remain after all tiers, charge at last tier's rate
        if (remaining > 0 && lastTier) {
            var overflowCost = remaining * parseFloat(lastTier.price_per_payslip);
            total += overflowCost;
            breakdown.push({
                name: lastTier.name + ' ({{ __("overflow") }})',
                count: remaining,
                rate: lastTier.price_per_payslip,
                cost: overflowCost
            });
        }
        
        var avg = count > 0 ? total / count : 0;
        
        $('#calculated-total').text('R' + total.toFixed(2));
        $('#calculated-avg').text('R' + avg.toFixed(2));
        
        // Show breakdown
        if (breakdown.length > 0) {
            var html = '<table class="table table-sm"><tbody>';
            breakdown.forEach(function(item) {
                html += '<tr>';
                html += '<td>' + item.name + '</td>';
                html += '<td class="text-center">' + item.count + ' × R' + parseFloat(item.rate).toFixed(2) + '</td>';
                html += '<td class="text-end fw-bold">R' + item.cost.toFixed(2) + '</td>';
                html += '</tr>';
            });
            html += '</tbody></table>';
            $('#breakdown-content').html(html);
            $('#price-breakdown').show();
        }
    });
    
    // Trigger calculation on load
    $('#calculate-price').click();
});
</script>
@endpush
@endsection
