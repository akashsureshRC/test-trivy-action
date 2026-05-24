<?php

namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\CustomIncome;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CustomIncomeController extends Controller
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
        return view('hrm.custom-incomes.create');
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
            'input_type' => 'required|in:fixed_amount,hourly_rate_factor_hours,custom_rate_quantity,monthly',
            'amount' => 'nullable|numeric',
            'rate_factor' => 'nullable|numeric',
            'employee_work_factor' => 'nullable|numeric',
            'hours_work_factor' => 'nullable|numeric',
            'custom_rate' => 'nullable|numeric',
            'percentage_income' => 'nullable|numeric|min:0|max:100',
            'selected_income_items' => 'nullable|array',
            'monthly_amount' => 'nullable|numeric',
        ]);

        CustomIncome::create($request->all());

        return redirect()->route('custom-incomes.create')->with('success', 'Custom Income added successfully.');
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
        $customIncome = CustomIncome::findOrFail($id);
        return view('hrm.custom-incomes.edit', compact('customIncome'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $customIncome = CustomIncome::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'input_type' => 'required|in:fixed_amount,hourly_rate_factor_hours,custom_rate_quantity,monthly',
            'amount' => 'nullable|numeric',
            'rate_factor' => 'nullable|numeric',
            'employee_work_factor' => 'nullable|numeric',
            'hours_work_factor' => 'nullable|numeric',
            'custom_rate' => 'nullable|numeric',
            'percentage_income' => 'nullable|numeric|min:0|max:100',
            'selected_income_items' => 'nullable|array',
            'monthly_amount' => 'nullable|numeric',
        ]);

        $customIncome->update($request->all());

        return redirect()->route('custom-incomes.edit',$id)->with('success', 'Custom Income updated successfully.');
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
