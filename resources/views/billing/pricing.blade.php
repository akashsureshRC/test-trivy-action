@extends('layouts.main')

@section('page-title')
    {{ __('Pricing') }}
@endsection

@section('page-breadcrumb')
    {{ __('My Billing') }},{{ __('Pricing') }}
@endsection

@push('css')
<style>
.page-header {
    display: none !important;
}

.pricing-card {
    border: 2px solid #e6e6e6;
    border-radius: 16px;
    transition: all 0.3s ease;
    height: 100%;
    background-color: #fff;
}
.pricing-card:hover {
    border-color: #3956ca;
    box-shadow: 0 10px 30px rgba(57, 86, 202, 0.15);
    transform: translateY(-5px);
}
.pricing-header {
    text-align: center;
    padding: 30px 20px 20px;
    border-bottom: 1px solid #e6e6e6;
}
.pricing-header h4 {
    font-weight: 700;
    margin-bottom: 10px;
}
.pricing-header .price {
    font-size: 36px;
    font-weight: 800;
    color: #3956ca;
}
.pricing-header .price small {
    font-size: 16px;
    font-weight: 400;
    color: #6c757d;
}
.pricing-body {
    padding: 20px;
}
.pricing-body .range {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 8px;
    text-align: center;
}
.calculator-card {
    border-radius: 16px;
    border: 1px solid #e6e6e6;
}
.calculator-result {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    padding: 25px;
}
.calculator-result .total {
    font-size: 42px;
    font-weight: 800;
}
.breakdown-table td {
    padding: 10px;
    border-bottom: 1px solid #e6e6e6;
}
.breakdown-table tr:last-child td {
    border-bottom: none;
}
.tier-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 8px;
}
.tier-1 { background: #3b82f6; }
.tier-2 { background: #22c55e; }
.tier-3 { background: #f59e0b; }
.tier-4 { background: #ec4899; }
.tier-5 { background: #8b5cf6; }
</style>
@endpush

@section('content')
<div class="row">
    <!-- Pricing Header -->
    <div class="col-12 mb-4">
        <div class="text-center">
            <h2 class="mb-2">{{ __('Simple, Transparent Pricing') }}</h2>
            <p class="text-muted">{{ __('Pay only for what you use. Lower rates as your volume increases.') }}</p>
        </div>
    </div>

    <!-- Pricing Tiers -->
    @foreach($tiers as $index => $tier)
    <div class="col-lg col-md-6 mb-4">
        <div class="pricing-card">
            <div class="pricing-header">
                <h4>{{ $tier->name }}</h4>
                <div class="price">
                    {{ $currencySymbol }}{{ number_format($tier->price_per_payslip, 2) }}
                    <small>/ {{ __('payslip') }}</small>
                </div>
            </div>
            <div class="pricing-body">
                <div class="range">
                    <span class="text-muted">{{ __('Payslip Range') }}</span>
                    <div class="fw-bold">
                        @if($tier->max_payslips)
                            {{ $tier->min_payslips }} - {{ $tier->max_payslips }}
                        @else
                            {{ $tier->min_payslips }}+
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <!-- Price Calculator -->
    <div class="col-12 mt-4">
        <div class="card calculator-card table-card">
            <div class="card-header bg-transparent border-0 pt-4">
                <h4 class="card-title mb-0">
                    <i class="ti ti-calculator me-2"></i>{{ __('Price Calculator') }}
                </h4>
                <p class="text-muted mt-2 mb-0">{{ __('Enter the number of payslips to calculate your cost') }}</p>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <label class="form-label fw-bold">{{ __('Number of Payslips') }}</label>
                        <input type="number" id="payslipCount" class="form-control form-control-lg" 
                               min="1" max="10000" value="50" placeholder="{{ __('Enter payslip count') }}">
                        <div class="form-text">{{ __('Enter a number between 1 and 10,000') }}</div>
                        
                        <button type="button" id="calculateBtn" class="btn btn-rc-primary btn-lg w-100 mt-3">
                            <i class="ti ti-calculator me-2"></i>{{ __('Calculate') }}
                        </button>
                    </div>
                    <div class="col-lg-8">
                        <div class="calculator-result mb-4">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <span class="opacity-75">{{ __('Estimated Total') }}</span>
                                    <div class="total" id="totalAmount">{{ $currencySymbol }}0.00</div>
                                    @if($taxEnabled)
                                        <small class="opacity-75" id="taxInfo">({{ __('excl.') }} {{ $taxPercentage }}% {{ __('VAT') }})</small>
                                    @endif
                                </div>
                                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                    <div class="opacity-75">{{ __('Average per Payslip') }}</div>
                                    <div class="fs-3 fw-bold" id="avgPerPayslip">{{ $currencySymbol }}0.00</div>
                                </div>
                            </div>
                        </div>

                        <!-- Breakdown Table -->
                        <h6 class="mb-3">{{ __('Tier Breakdown') }}</h6>
                        <div class="table-responsive">
                            <table class="table breakdown-table" id="breakdownTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Tier') }}</th>
                                        <th>{{ __('Range') }}</th>
                                        <th class="text-center">{{ __('Quantity') }}</th>
                                        <th class="text-end">{{ __('Rate') }}</th>
                                        <th class="text-end">{{ __('Amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="breakdownBody">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            {{ __('Enter payslip count and click Calculate') }}
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light" id="breakdownFooter" style="display: none;">
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>{{ __('Subtotal') }}</strong></td>
                                        <td class="text-end"><strong id="subtotalAmount">R0.00</strong></td>
                                    </tr>
                                    @if($taxEnabled)
                                    <tr>
                                        <td colspan="4" class="text-end">{{ __('VAT') }} ({{ $taxPercentage }}%)</td>
                                        <td class="text-end" id="vatAmount">R0.00</td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td colspan="4" class="text-end"><strong>{{ __('Total') }}</strong></td>
                                        <td class="text-end"><strong id="grandTotal">R0.00</strong></td>
                                    </tr>
                                    @endif
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Section -->
    <div class="col-12 mt-4">
        <div class="card">
            <div class="card-body">
                <h5><i class="ti ti-info-circle me-2"></i>{{ __('How Cumulative Pricing Works') }}</h5>
                <p class="text-muted mb-3">
                    {{ __('Our pricing is cumulative, meaning you pay different rates for different portions of your payslip volume:') }}
                </p>
                <ul class="mb-0">
                    <li class="mb-2">{{ __('The first payslips (Tier 1) are charged at the base rate.') }}</li>
                    <li class="mb-2">{{ __('As you generate more payslips, subsequent payslips move to lower-priced tiers.') }}</li>
                    <li class="mb-2">{{ __('This means the more payslips you generate, the lower your average cost per payslip.') }}</li>
                    <li>{{ __('Billing cycles reset monthly, so your cumulative count resets at the start of each billing period.') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calculateBtn = document.getElementById('calculateBtn');
    const payslipInput = document.getElementById('payslipCount');
    const currencySymbol = '{{ $currencySymbol }}';
    const taxEnabled = {{ $taxEnabled ? 'true' : 'false' }};
    const taxPercentage = {{ $taxPercentage }};

    const tierColors = ['tier-1', 'tier-2', 'tier-3', 'tier-4', 'tier-5'];

    calculateBtn.addEventListener('click', function() {
        const count = parseInt(payslipInput.value);
        
        if (isNaN(count) || count < 1 || count > 10000) {
            alert('{{ __("Please enter a valid number between 1 and 10,000") }}');
            return;
        }

        // Show loading state
        calculateBtn.disabled = true;
        calculateBtn.innerHTML = '<i class="ti ti-loader me-2"></i>{{ __("Calculating...") }}';

        fetch('{{ route("my-billing.calculate-estimate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ payslip_count: count })
        })
        .then(response => response.json())
        .then(data => {
            // Update total
            document.getElementById('totalAmount').textContent = currencySymbol + formatNumber(data.total);
            
            // Update average
            const avg = data.total / count;
            document.getElementById('avgPerPayslip').textContent = currencySymbol + formatNumber(avg);

            // Update breakdown table
            let breakdownHtml = '';
            data.breakdown.forEach((item, index) => {
                const tierClass = tierColors[index % tierColors.length];
                breakdownHtml += `
                    <tr>
                        <td>
                            <span class="tier-indicator ${tierClass}"></span>
                            ${item.tier_name}
                        </td>
                        <td>${item.min_payslips} - ${item.max_payslips || '∞'}</td>
                        <td class="text-center">${item.quantity}</td>
                        <td class="text-end">${currencySymbol}${formatNumber(item.unit_price)}</td>
                        <td class="text-end fw-bold">${currencySymbol}${formatNumber(item.amount)}</td>
                    </tr>
                `;
            });
            document.getElementById('breakdownBody').innerHTML = breakdownHtml;
            document.getElementById('breakdownFooter').style.display = '';
            document.getElementById('subtotalAmount').textContent = currencySymbol + formatNumber(data.subtotal);
            
            if (taxEnabled) {
                document.getElementById('vatAmount').textContent = currencySymbol + formatNumber(data.tax_amount);
                document.getElementById('grandTotal').textContent = currencySymbol + formatNumber(data.total);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("An error occurred. Please try again.") }}');
        })
        .finally(() => {
            calculateBtn.disabled = false;
            calculateBtn.innerHTML = '<i class="ti ti-calculator me-2"></i>{{ __("Calculate") }}';
        });
    });

    // Allow Enter key to trigger calculation
    payslipInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            calculateBtn.click();
        }
    });

    function formatNumber(num) {
        return parseFloat(num).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    // Initial calculation
    calculateBtn.click();
});
</script>
@endpush
