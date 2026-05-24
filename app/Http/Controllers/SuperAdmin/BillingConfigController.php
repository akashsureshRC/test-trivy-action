<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Billing\BillingSetting;
use App\Models\Billing\BillingTier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BillingConfigController extends Controller
{
    /**
     * Display billing configuration dashboard
     */
    public function index()
    {
        if (Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $settings = BillingSetting::getAllSettings();
        $tiers = BillingTier::orderBy('sort_order')->get();

        return view('super-admin.billing.index', compact('settings', 'tiers'));
    }

    /**
     * Show billing settings form
     */
    public function settings()
    {
        if (Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $settings = BillingSetting::getAllSettings();

        return view('super-admin.billing.settings', compact('settings'));
    }

    /**
     * Update billing settings
     */
    public function updateSettings(Request $request)
    {
        if (Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = Validator::make($request->all(), [
            'billing_enabled' => 'nullable|in:true,false',
            'grace_period_days' => 'nullable|integer|min:0|max:90',
            'trial_days' => 'nullable|integer|min:0|max:365',
            'trial_payslips' => 'nullable|integer|min:0|max:10000',
            'trial_payslips_limit' => 'nullable|integer|min:0|max:1000',
            'base_rate' => 'nullable|numeric|min:0|max:10000',
            'currency' => 'nullable|string|max:3',
            'invoice_due_days' => 'nullable|integer|min:1|max:90',
            'suspend_after_days' => 'nullable|integer|min:1|max:90',
            'tax_enabled' => 'nullable|in:true,false',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'invoice_prefix' => 'nullable|string|max:10',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:50',
            'company_vat_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_branch_code' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Update all settings
        $settingsToUpdate = [
            'billing_enabled',
            'grace_period_days',
            'trial_days',
            'trial_payslips',
            'trial_payslips_limit',
            'base_rate',
            'currency',
            'invoice_due_days',
            'suspend_after_days',
            'tax_enabled',
            'tax_percentage',
            'invoice_prefix',
            'company_name',
            'company_address',
            'company_email',
            'company_phone',
            'company_vat_number',
            'bank_name',
            'bank_account_name',
            'bank_account_number',
            'bank_branch_code',
        ];

        foreach ($settingsToUpdate as $key) {
            if ($request->has($key)) {
                BillingSetting::set($key, $request->input($key));
            }
        }

        return redirect()->back()->with('success', __('Billing settings updated successfully.'));
    }

    /**
     * List all billing tiers
     */
    public function tiers()
    {
        if (Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $tiers = BillingTier::orderBy('sort_order')->get();

        return view('super-admin.billing.tiers.index', compact('tiers'));
    }

    /**
     * Show create tier form
     */
    public function createTier()
    {
        if (Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        return view('super-admin.billing.tiers.create');
    }

    /**
     * Store a new tier
     */
    public function storeTier(Request $request)
    {
        if (Auth::user()->type != 'super admin') {
            if ($request->ajax()) {
                return response()->json(['error' => __('Permission denied.')], 403);
            }
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'min_payslips' => 'required|integer|min:1',
            'max_payslips' => 'nullable|integer|min:1|gt:min_payslips',
            'price_per_payslip' => 'required|numeric|min:0',
            'sort_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Gap validation: ensure no gaps in tier ranges
        $lastTier = BillingTier::orderBy('sort_order', 'desc')->first();
        
        if ($lastTier) {
            // If there are existing tiers, new tier must start where last tier ends
            $expectedMinPayslips = ($lastTier->max_payslips ?? PHP_INT_MAX) + 1;
            
            if ($request->min_payslips != $expectedMinPayslips) {
                $error = __('To prevent gaps, this tier must start at payslip :expected. Current tiers end at :end.', [
                    'expected' => number_format($expectedMinPayslips),
                    'end' => $lastTier->max_payslips ? number_format($lastTier->max_payslips) : '∞'
                ]);
                
                if ($request->ajax()) {
                    return response()->json(['error' => $error], 422);
                }
                return redirect()->back()->with('error', $error)->withInput();
            }
        } else {
            // First tier must start at 1
            if ($request->min_payslips != 1) {
                $error = __('The first tier must start at payslip 1.');
                
                if ($request->ajax()) {
                    return response()->json(['error' => $error], 422);
                }
                return redirect()->back()->with('error', $error)->withInput();
            }
        }

        BillingTier::create([
            'name' => $request->name,
            'min_payslips' => $request->min_payslips,
            'max_payslips' => $request->max_payslips,
            'price_per_payslip' => $request->price_per_payslip,
            'sort_order' => $request->sort_order,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => __('Billing tier created successfully.')]);
        }

        return redirect()->route('billing.tiers.index')->with('success', __('Billing tier created successfully.'));
    }

    /**
     * Show edit tier form
     */
    public function editTier($id)
    {
        if (Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $tier = BillingTier::findOrFail($id);

        return view('super-admin.billing.tiers.edit', compact('tier'));
    }

    /**
     * Update a tier
     */
    public function updateTier(Request $request, $id)
    {
        if (Auth::user()->type != 'super admin') {
            if ($request->ajax()) {
                return response()->json(['error' => __('Permission denied.')], 403);
            }
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $tier = BillingTier::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'min_payslips' => 'required|integer|min:1',
            'max_payslips' => 'nullable|integer|min:1',
           'price_per_payslip' => 'required|numeric|min:0',
            'sort_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Gap validation: prevent changes that create gaps
        $prevTier = BillingTier::where('sort_order', '<', $tier->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();
            
        $nextTier = BillingTier::where('sort_order', '>', $tier->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();
        
        // Check if changing min_payslips creates a gap with previous tier
        if ($prevTier) {
            $expectedMin = ($prevTier->max_payslips ?? PHP_INT_MAX) + 1;
            if ($request->min_payslips != $expectedMin) {
                $error = __('Cannot change minimum payslips. This tier must start at :expected to maintain continuity with the previous tier.', [
                    'expected' => number_format($expectedMin)
                ]);
                
                if ($request->ajax()) {
                    return response()->json(['error' => $error], 422);
                }
                return redirect()->back()->with('error', $error)->withInput();
            }
        } else {
            // First tier must start at 1
            if ($request->min_payslips != 1) {
                $error = __('The first tier must start at payslip 1.');
                
                if ($request->ajax()) {
                    return response()->json(['error' => $error], 422);
                }
                return redirect()->back()->with('error', $error)->withInput();
            }
        }
        
        // Check if changing max_payslips creates a gap with next tier
        if ($nextTier && $request->max_payslips) {
            $expectedNextMin = $request->max_payslips + 1;
            if ($nextTier->min_payslips != $expectedNextMin) {
                $error = __('Cannot change maximum payslips. The next tier starts at :next, so this tier must end at :expected.', [
                    'next' => number_format($nextTier->min_payslips),
                    'expected' => number_format($nextTier->min_payslips - 1)
                ]);
                
                if ($request->ajax()) {
                    return response()->json(['error' => $error], 422);
                }
                return redirect()->back()->with('error', $error)->withInput();
            }
        }

        $tier->update([
            'name' => $request->name,
            'min_payslips' => $request->min_payslips,
            'max_payslips' => $request->max_payslips,
            'price_per_payslip' => $request->price_per_payslip,
            'sort_order' => $request->sort_order,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => __('Billing tier updated successfully.')]);
        }

        return redirect()->route('billing.tiers.index')->with('success', __('Billing tier updated successfully.'));
    }

    /**
     * Delete a tier
     */
    public function destroyTier($id)
    {
        if (Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $tier = BillingTier::findOrFail($id);
        
        // Only allow deletion of the last tier (highest sort_order)
        $maxSortOrder = BillingTier::max('sort_order');
        
        if ($tier->sort_order != $maxSortOrder) {
            return redirect()->back()->with('error', __('Only the last tier can be deleted to prevent gaps in pricing structure. Please delete tiers in reverse order.'));
        }
        
        $tier->delete();

        return redirect()->route('billing.tiers.index')->with('success', __('Billing tier deleted successfully.'));
    }

    public function bulkSaveTiers(Request $request)
    {
        if (Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = Validator::make($request->all(), [
            'tiers' => 'required|array|min:1',
            'tiers.*.id' => 'nullable|integer|exists:billing_tiers,id',
            'tiers.*.name' => 'required|string|max:255',
            'tiers.*.min_payslips' => 'required|integer|min:1',
            'tiers.*.max_payslips' => 'nullable|integer|min:1',
            'tiers.*.price_per_payslip' => 'required|numeric|min:0',
        ], [
            'tiers.required' => __('Please add at least one tier.'),
            'tiers.array' => __('Please provide valid tier data.'),
            'tiers.min' => __('Please add at least one tier.'),
            'tiers.*.id.exists' => __('One of the submitted tiers no longer exists. Please refresh and try again.'),
            'tiers.*.name.required' => __('Tier Name is required.'),
            'tiers.*.name.max' => __('Tier Name may not be greater than :max characters.'),
            'tiers.*.min_payslips.required' => __('Min Payslips is required.'),
            'tiers.*.min_payslips.integer' => __('Min Payslips must be a whole number.'),
            'tiers.*.min_payslips.min' => __('Min Payslips must be at least 1.'),
            'tiers.*.max_payslips.integer' => __('Max Payslips must be a whole number.'),
            'tiers.*.max_payslips.min' => __('Max Payslips must be at least 1.'),
            'tiers.*.price_per_payslip.required' => __('Price per Payslip is required.'),
            'tiers.*.price_per_payslip.numeric' => __('Price per Payslip must be a valid number.'),
            'tiers.*.price_per_payslip.min' => __('Price per Payslip must be at least 0.'),
        ], [
            'tiers.*.name' => __('Tier Name'),
            'tiers.*.min_payslips' => __('Min Payslips'),
            'tiers.*.max_payslips' => __('Max Payslips'),
            'tiers.*.price_per_payslip' => __('Price per Payslip'),
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $tiers = collect($request->input('tiers', []))->values();

        foreach ($tiers as $index => $tier) {
            $min = (int) $tier['min_payslips'];
            $max = isset($tier['max_payslips']) && $tier['max_payslips'] !== '' ? (int) $tier['max_payslips'] : null;

            if (!is_null($max) && $max < $min) {
                return redirect()->back()->with('error', __('Tier :tier maximum payslips must be greater than or equal to minimum payslips.', [
                    'tier' => $index + 1,
                ]))->withInput();
            }

            if (is_null($max) && $index < $tiers->count() - 1) {
                return redirect()->back()->with('error', __('Only the last tier can have an open-ended range.'))->withInput();
            }
        }

        $firstMin = (int) data_get($tiers, '0.min_payslips', 0);
        if ($firstMin !== 1) {
            return redirect()->back()->with('error', __('The first tier must start at payslip 1.'))->withInput();
        }

        for ($index = 1; $index < $tiers->count(); $index++) {
            $previous = $tiers[$index - 1];
            $current = $tiers[$index];

            $previousMax = isset($previous['max_payslips']) && $previous['max_payslips'] !== '' ? (int) $previous['max_payslips'] : null;
            if (is_null($previousMax)) {
                return redirect()->back()->with('error', __('Tier :tier is open-ended, so no additional tiers can follow it.', [
                    'tier' => $index,
                ]))->withInput();
            }

            $expectedMin = $previousMax + 1;
            $currentMin = (int) $current['min_payslips'];

            if ($currentMin !== $expectedMin) {
                return redirect()->back()->with('error', __('Gap/overlap detected between tier :prev and tier :current. Tier :current must start at :expected.', [
                    'prev' => $index,
                    'current' => $index + 1,
                    'expected' => number_format($expectedMin),
                ]))->withInput();
            }
        }

        DB::transaction(function () use ($tiers) {
            $submittedIds = $tiers->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();

            if (!empty($submittedIds)) {
                BillingTier::whereNotIn('id', $submittedIds)->delete();
            } else {
                BillingTier::query()->delete();
            }

            foreach ($tiers as $index => $tierData) {
                $payload = [
                    'name' => $tierData['name'],
                    'min_payslips' => (int) $tierData['min_payslips'],
                    'max_payslips' => isset($tierData['max_payslips']) && $tierData['max_payslips'] !== '' ? (int) $tierData['max_payslips'] : null,
                    'price_per_payslip' => (float) $tierData['price_per_payslip'],
                    'sort_order' => $index + 1,
                ];

                if (!empty($tierData['id'])) {
                    $tier = BillingTier::find($tierData['id']);
                    if ($tier) {
                        $tier->update($payload);
                    }
                } else {
                    BillingTier::create($payload);
                }
            }
        });

        return redirect()->route('billing.tiers.index')->with('success', __('Pricing tiers saved successfully.'));
    }

    /**
     * Preview tier calculation
     */
    public function previewCalculation(Request $request)
    {
        $payslips = (int) $request->input('payslips', 0);
        
        if ($payslips <= 0) {
            return response()->json(['error' => 'Invalid payslip count'], 400);
        }

        $calculation = BillingTier::calculateCumulativePrice($payslips);
        $calculation['currency_symbol'] = BillingSetting::getCurrencySymbol();

        return response()->json($calculation);
    }
}
