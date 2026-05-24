<?php

namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\CustomBenefit;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CustomBenefitController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('hrm.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('hrm.custom-benefits.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'input_type' => 'required|string',
            'amount' => 'nullable|numeric|required_if:input_type,fixed_amount',
            'rate_factor' => 'nullable|numeric|required_if:input_type,hourly_rate_factor_hours',
            'custom_rate' => 'nullable|numeric|required_if:input_type,custom_rate_quantity',
            'percentage_income' => 'nullable|numeric|required_if:input_type,percentage_income|min:0|max:100',
            'formula' => 'nullable|string|required_if:input_type,formula',
            'monthly_amount' => 'nullable|numeric|required_if:input_type,monthly(for non_monthly employees)',
            'selected_income_items' => 'nullable|array',
        ]);

        CustomBenefit::create($request->all());

        return redirect()->route('custom-benefits.create')->with('success', 'CustomBenefit added successfully.');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('hrm.show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $customBenefit = CustomBenefit::findOrFail($id);
        return view('hrm.custom-benefits.edit', compact('customBenefit'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $customBenefit = CustomBenefit::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'input_type' => 'required|string',
            'amount' => 'nullable|numeric|required_if:input_type,fixed_amount',
            'rate_factor' => 'nullable|numeric|required_if:input_type,hourly_rate_factor_hours',
            'custom_rate' => 'nullable|numeric|required_if:input_type,custom_rate_quantity',
            'percentage_income' => 'nullable|numeric|required_if:input_type,percentage_income|min:0|max:100',
            'formula' => 'nullable|string|required_if:input_type,formula',
            'monthly_amount' => 'nullable|numeric|required_if:input_type,monthly(for non_monthly employees)',
            'selected_income_items' => 'nullable|array',
        ]);

        $customBenefit->update($request->all());

        return redirect()->route('custom-benefits.edit', $id)->with('success', 'Reimbursement updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
