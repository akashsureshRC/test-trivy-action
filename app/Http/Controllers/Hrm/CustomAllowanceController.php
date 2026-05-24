<?php

namespace App\Http\Controllers\Hrm;

use App\Models\Hrm\CustomAllowance;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CustomAllowanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customAllowances = CustomAllowance::all();
        return view('hrm.custom-allowances.index', compact('customAllowances'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('hrm.custom-allowances.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'rate_factor' => 'nullable|numeric',
            'employee_work_factor' => 'nullable|numeric',
            'hours_work_factor' => 'nullable|numeric',
            'custom_rate' => 'nullable|numeric',
        ]);
    
        CustomAllowance::create([
            'rate_factor' => $request->rate_factor,
            'employee_work_factor' => $request->employee_work_factor,
            'hours_work_factor' => $request->hours_work_factor,
            'custom_rate' => $request->custom_rate,
        ]);
    
        return redirect()->back()->with('success', 'Data saved successfully!');
    }

    /**
     * Show the specified resource.
     */
    public function show(CustomAllowance $customAllowance)
    {
        return view('hrm.custom-allowances.show', compact('customAllowance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $customAllowance =  CustomAllowance::findOrFail($id);
        return view('hrm.custom-allowances.edit', compact('customAllowance'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $customAllowance = CustomAllowance::findOrFail($id);
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'input_type' => 'required|string',
            'amount' => 'nullable|numeric|required_if:input_type,fixed_amount',
            'rate_factor' => 'nullable|numeric|required_if:input_type,hourly_rate_factor_hours',
            'employee_work_factor' => 'nullable|numeric|required_if:input_type,hourly_rate_factor_hours',
            'custom_rate' => 'nullable|numeric|required_if:input_type,custom_rate_quantity',
            'hours_work_factor' => 'nullable|numeric|required_if:input_type,custom_rate_quantity',
            'monthly_amount' => 'nullable|numeric|required_if:input_type,monthly',
        ]);

        $customAllowance->update($validatedData);

        return redirect()->route('custom-allowances.edit',$id)->with('success', 'Custom Allowance updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomAllowance $customAllowance)
    {
        $customAllowance->delete();

        return redirect()->route('custom-allowances.index')->with('success', 'Custom Allowance deleted successfully.');
    }
}
