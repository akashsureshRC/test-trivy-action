<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\TaxYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaxYearController extends Controller
{
    /**
     * List all tax year configurations.
     */
    public function index()
    {
        if (Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $taxYears = TaxYear::orderByDesc('effective_from')->paginate(15);

        return view('super-admin.tax-years.index', compact('taxYears'));
    }

    /**
     * Show form to create a new tax year.
     */
    public function create()
    {
        if (Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        return view('super-admin.tax-years.create');
    }

    /**
     * Store a new tax year configuration.
     */
    public function store(Request $request)
    {
        if (Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = $this->validateTaxYear($request);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $this->prepareTaxYearData($request);
        $data['is_locked'] = false;

        TaxYear::create($data);

        return redirect()->route('tax-years.index')
            ->with('success', __('Tax year configuration created successfully.'));
    }

    /**
     * Show form to edit a tax year.
     */
    public function edit($id)
    {
        if (Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $taxYear = TaxYear::findOrFail($id);
        $readOnly = $taxYear->is_locked;

        return view('super-admin.tax-years.edit', compact('taxYear', 'readOnly'));
    }

    /**
     * Update an existing tax year configuration.
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $taxYear = TaxYear::findOrFail($id);

        if ($taxYear->is_locked) {
            return redirect()->route('tax-years.index')
                ->with('error', __('Cannot edit a locked tax year.'));
        }

        $validator = $this->validateTaxYear($request);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $taxYear->update($this->prepareTaxYearData($request));

        return redirect()->route('tax-years.index')
            ->with('success', __('Tax year configuration updated successfully.'));
    }

    /**
     * Delete a tax year configuration.
     */
    public function destroy($id)
    {
        if (Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $taxYear = TaxYear::findOrFail($id);

        if ($taxYear->is_locked) {
            return redirect()->route('tax-years.index')
                ->with('error', __('Cannot delete a locked tax year.'));
        }

        if ($taxYear->paySlips()->exists()) {
            return redirect()->route('tax-years.index')
                ->with('error', __('Cannot delete a tax year that has associated payslips.'));
        }

        $taxYear->delete();

        return redirect()->route('tax-years.index')
            ->with('success', __('Tax year configuration deleted successfully.'));
    }

    /**
     * Lock a tax year so it can be used by payroll calculations.
     */
    public function lock($id)
    {
        if (Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $taxYear = TaxYear::findOrFail($id);

        if ($taxYear->is_locked) {
            return redirect()->route('tax-years.index')
                ->with('error', __('Tax year is already locked.'));
        }

        // Check for overlapping locked tax years
        $overlap = TaxYear::locked()
            ->where('id', '!=', $taxYear->id)
            ->where(function ($q) use ($taxYear) {
                $q->whereBetween('effective_from', [$taxYear->effective_from, $taxYear->effective_to])
                  ->orWhereBetween('effective_to', [$taxYear->effective_from, $taxYear->effective_to])
                  ->orWhere(function ($q2) use ($taxYear) {
                      $q2->where('effective_from', '<=', $taxYear->effective_from)
                         ->where('effective_to', '>=', $taxYear->effective_to);
                  });
            })
            ->exists();

        if ($overlap) {
            return redirect()->route('tax-years.index')
                ->with('error', __('Another locked tax year already covers this period. Unlock it first.'));
        }

        $taxYear->update([
            'is_locked'  => true,
            'locked_by'  => Auth::id(),
            'locked_at'  => now(),
        ]);

        return redirect()->route('tax-years.index')
            ->with('success', __('Tax year locked successfully. It will now be used for payroll calculations.'));
    }

    /**
     * Unlock a tax year (only if no payslips reference it).
     */
    public function unlock($id)
    {
        if (Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $taxYear = TaxYear::findOrFail($id);

        if (!$taxYear->is_locked) {
            return redirect()->route('tax-years.index')
                ->with('error', __('Tax year is not locked.'));
        }

        if ($taxYear->paySlips()->exists()) {
            return redirect()->route('tax-years.index')
                ->with('error', __('Cannot unlock a tax year that has associated payslips. This would compromise historical data integrity.'));
        }

        $taxYear->update([
            'is_locked'  => false,
            'locked_by'  => null,
            'locked_at'  => null,
        ]);

        return redirect()->route('tax-years.index')
            ->with('success', __('Tax year unlocked successfully.'));
    }

    /**
     * Validate incoming tax year request data.
     */
    private function validateTaxYear(Request $request)
    {
        return Validator::make($request->all(), [
            'label'                     => 'required|string|max:20',
            'effective_from'            => 'required|date',
            'effective_to'              => 'required|date|after:effective_from',
            'brackets'                  => 'required|array|min:1',
            'brackets.*.min'            => 'required|numeric|min:0',
            'brackets.*.max'            => 'required|numeric|min:0',
            'brackets.*.base_tax'       => 'required|numeric|min:0',
            'brackets.*.rate'           => 'required|numeric|min:0|max:1',
            'brackets.*.threshold'      => 'required|numeric|min:0',
            'primary_rebate'            => 'required|numeric|min:0',
            'secondary_rebate'          => 'required|numeric|min:0',
            'tertiary_rebate'           => 'required|numeric|min:0',
            'secondary_rebate_age'      => 'required|integer|min:1|max:120',
            'tertiary_rebate_age'       => 'required|integer|min:1|max:120',
            'uif_rate'                  => 'required|numeric|min:0|max:1',
            'uif_ceiling'               => 'required|numeric|min:0',
            'sdl_rate'                  => 'required|numeric|min:0|max:1',
            'eti_min_age'               => 'required|integer|min:0|max:120',
            'eti_max_age'               => 'required|integer|min:0|max:120',
            'eti_salary_cap'            => 'required|numeric|min:0',
            'eti_max_amount'            => 'required|numeric|min:0',
            'eti_rate'                  => 'required|numeric|min:0|max:1',
            'ot_multiplier'             => 'required|numeric|min:1|max:5',
            'medical_aid_tax_rate'      => 'required|numeric|min:0|max:1',
            'travel_allowance_tax_rate' => 'required|numeric|min:0|max:1',
        ]);
    }

    /**
     * Prepare tax year data from the request.
     */
    private function prepareTaxYearData(Request $request): array
    {
        // Build the brackets array from the repeater inputs
        $brackets = [];
        foreach ($request->input('brackets', []) as $bracket) {
            $brackets[] = [
                'min'       => (float) $bracket['min'],
                'max'       => (float) $bracket['max'],
                'base_tax'  => (float) $bracket['base_tax'],
                'rate'      => (float) $bracket['rate'],
                'threshold' => (float) $bracket['threshold'],
            ];
        }

        return [
            'label'                     => $request->input('label'),
            'effective_from'            => $request->input('effective_from'),
            'effective_to'              => $request->input('effective_to'),
            'tax_brackets'              => $brackets,
            'primary_rebate'            => $request->input('primary_rebate'),
            'secondary_rebate'          => $request->input('secondary_rebate'),
            'tertiary_rebate'           => $request->input('tertiary_rebate'),
            'secondary_rebate_age'      => $request->input('secondary_rebate_age'),
            'tertiary_rebate_age'       => $request->input('tertiary_rebate_age'),
            'uif_rate'                  => $request->input('uif_rate'),
            'uif_ceiling'               => $request->input('uif_ceiling'),
            'sdl_rate'                  => $request->input('sdl_rate'),
            'eti_min_age'               => $request->input('eti_min_age'),
            'eti_max_age'               => $request->input('eti_max_age'),
            'eti_salary_cap'            => $request->input('eti_salary_cap'),
            'eti_max_amount'            => $request->input('eti_max_amount'),
            'eti_rate'                  => $request->input('eti_rate'),
            'ot_multiplier'             => $request->input('ot_multiplier'),
            'medical_aid_tax_rate'      => $request->input('medical_aid_tax_rate'),
            'travel_allowance_tax_rate' => $request->input('travel_allowance_tax_rate'),
        ];
    }
}
