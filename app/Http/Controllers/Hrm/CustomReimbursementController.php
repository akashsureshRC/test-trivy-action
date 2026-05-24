<?php

namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\CustomReimbursement;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CustomReimbursementController extends Controller
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
        return view('hrm.custom-reimbursements.create');
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
            'input_type' => 'required|in:different_on_every_payslip,once_off,custom_rate_quantity',
            'different_rate_for_every_employee' => 'boolean',
            'custom_rate' => 'nullable|numeric|min:0',
        ]);

        CustomReimbursement::create($request->all());

        return redirect()->route('custom-reimbursements.create')->with('success', 'Reimbursement added successfully.');
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
        $customReimbursement = CustomReimbursement::findOrFail($id);
        return view('hrm.custom-reimbursements.edit', compact('customReimbursement'));
    }
   

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $customReimbursement =  CustomReimbursement::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'input_type' => 'required|in:different_on_every_payslip,once_off,custom_rate_quantity',
            'different_rate_for_every_employee' => 'boolean',
            'custom_rate' => 'nullable|numeric|min:0',
        ]);

        $customReimbursement->update($request->all());

        return redirect()->route('custom-reimbursements.edit',$id)->with('success', 'Reimbursement updated successfully.');
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
