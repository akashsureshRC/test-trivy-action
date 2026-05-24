<?php

namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\TaxOverDeduction;
use App\Models\Hrm\Payroll;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Hrm\Employee;

class TaxOverDeductionController extends Controller
{
    protected function findWorkspaceEmployeeOrFail(int $employeeId): Employee
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            abort(403, 'Unauthorized employee access.');
        }

        return $employee;
    }

    protected function findWorkspaceTaxOverDeductionOrFail(int $id): TaxOverDeduction
    {
        return TaxOverDeduction::whereHas('employee', function ($query) {
            $query->where('id', '>', 0);
        })->findOrFail($id);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $taxOverDeductions = TaxOverDeduction::whereHas('employee', function ($query) {
            $query->where('id', '>', 0);
        })->latest()->paginate(10);
        return view('hrm.tax-over-deduction.index', compact('taxOverDeductions'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
    {
       
        $employeeId = $request->employee_id;
        $term = $request->term;
        if (!$employeeId) {
            return redirect()->route('payroll.index')->with('error', 'Employee ID is required.');
        }

        $employee = $this->findWorkspaceEmployeeOrFail((int) $employeeId);
        return view('hrm.tax-over-deduction.create', compact('employee','term'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'per_period' => 'required|numeric|min:0',
            'term' => 'required|date',
        ], [
            'employee_id.required' => 'The Employee ID is required.',
            'employee_id.exists' => 'The Employee does not exist.',
            'per_period.required' => 'The Voluntary Tax Over-Deduction field is required.',
            'per_period.numeric' => 'The value must be a valid number.',
            'per_period.min' => 'The value must be a positive number.',
        ]);

        $this->findWorkspaceEmployeeOrFail((int) $validated['employee_id']);


        TaxOverDeduction::create([
            'employee_id' => $validated['employee_id'],
            'per_period' => $validated['per_period'],
            'term' => $validated['term'], 
        ]);


        // Payroll::where('employee_id', $validatedData['employee_id'])->update([
        //     'voluntary_tax_over_deduction' => $validatedData['per_period'],
        // ]);

        return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $request->term])->with('success', 'Tax Over-Deduction  Created Successfully.');
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
        $taxOverDeductions = $this->findWorkspaceTaxOverDeductionOrFail((int) $id);
        $term = request('term');
        $employee = $this->findWorkspaceEmployeeOrFail((int) $taxOverDeductions->employee_id);

        return view('hrm.tax-over-deduction.edit', compact('taxOverDeductions','employee','term'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
{
    $validatedData = $request->validate([
        'per_period' => 'required|numeric|min:0.01',
        'term' => 'required|date',
    ], [
        'per_period.required' => 'The Amount per Period field is required.',
        'per_period.numeric' => 'The value must be a valid number.',
        'per_period.min' => 'The value must be greater than zero.',
    ]);

  
    $taxOverDeduction = $this->findWorkspaceTaxOverDeductionOrFail((int) $id);

    $this->findWorkspaceEmployeeOrFail((int) $taxOverDeduction->employee_id);

    
    $taxOverDeduction->update($validatedData);

    
    Payroll::where('employee_id', $taxOverDeduction->employee_id)
        ->update(['voluntary_tax_over_deduction' => $validatedData['per_period']]);

        return redirect()->route('payroll.index', ['employee_id' => $taxOverDeduction->employee_id,'term' => $validatedData['term'], ])
        ->with('success', 'TaxOverDeduction Updated Successfully!');
}


    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id,$term)
    {
        $taxOverDeduction = $this->findWorkspaceTaxOverDeductionOrFail((int) $id);
        $employee_id = $taxOverDeduction->employee_id;
        $taxOverDeduction->delete();
        return redirect()->route('payroll.index', ['employee_id' => $employee_id,'term' => $term])->with('success', 'Tax Over Deduction Deleted Successfully.');
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'per_period' => 'required|numeric|min:0',
            'term' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return response()->json(['success' => true]);
    }

    public function ajaxValidateUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'per_period' => 'required|numeric|min:0.01',
            'term' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return response()->json(['success' => true]);
    }
}
