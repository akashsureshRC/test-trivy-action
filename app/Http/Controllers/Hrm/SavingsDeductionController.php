<?php

namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\SavingsDeduction;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use App\Models\Hrm\Employee;
use App\Models\Hrm\Payroll;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
class SavingsDeductionController extends Controller
{
    protected function findWorkspaceEmployeeOrFail(int $employeeId): Employee
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            abort(403, 'Unauthorized employee access.');
        }

        return $employee;
    }

    protected function findWorkspaceSavingsDeductionOrFail(int $id): SavingsDeduction
    {
        return SavingsDeduction::whereHas('employee', function ($query) {
            $query->where('id', '>', 0);
        })->findOrFail($id);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $deductions = SavingsDeduction::whereHas('employee', function ($query) {
            $query->where('id', '>', 0);
        })->paginate(10);
        return view('hrm.savings_deductions.index', compact('deductions'));
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
            return redirect()->back()->with('error', 'Employee ID is required.');
        }

        $employee = $this->findWorkspaceEmployeeOrFail((int) $employeeId);
        return view('hrm.savings_deductions.create',compact('employee','term'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        
        Log::info('Incoming Savings Deduction Request:', $request->all());

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'regular_deduction' => 'required|numeric|min:0',
            'term' => 'required'
        ]);

        $this->findWorkspaceEmployeeOrFail((int) $validated['employee_id']);

       
        $savingsDeduction = SavingsDeduction::create($validated);
        Log::info('Savings Deduction Created:', $savingsDeduction->toArray());

       
        $payroll = Payroll::updateOrCreate(
            ['employee_id' => $validated['employee_id']],
            ['regular_deduction' => $validated['regular_deduction'], 'term' => $request->term]
        );

        Log::info('Payroll Updated:', $payroll->toArray());

        return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $request->term])
            ->with('success', 'Savings Deduction Created Successfully.');
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
       
        $savings_deduction = $this->findWorkspaceSavingsDeductionOrFail((int) $id);
        $employee = $this->findWorkspaceEmployeeOrFail((int) $savings_deduction->employee_id);
        $term = request('term');
        return view('hrm.savings_deductions.edit', compact('savings_deduction','employee','term'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'regular_deduction' => 'required|numeric|min:0',
        ]);
        $term = $request->input('term');
        $this->findWorkspaceEmployeeOrFail((int) $validated['employee_id']);

        $savings_deduction = $this->findWorkspaceSavingsDeductionOrFail((int) $id);
        $savings_deduction->update($validated);

       
        Payroll::updateOrCreate(
            ['employee_id' => $validated['employee_id']],
            ['regular_deduction' => $validated['regular_deduction']],
            
        );

        return redirect()->route('payroll.index', ['employee_id' => $validated['employee_id'],'term' => $term])
            ->with('success', 'Savings Deduction Updated Successfully!');
    }

    public function destroy($id,$term)
    {
        $deduction = $this->findWorkspaceSavingsDeductionOrFail((int) $id);
        $deduction->delete();
        $employee_id = $deduction->employee_id;
        return redirect()->route('payroll.index', ['employee_id' => $employee_id,'term' => $term])->with('success', 'Savings Deduction Deleted Successfully.');
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id'       => 'required|exists:employees,id',
            'regular_deduction' => 'required|numeric|min:0',
            'term'              => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }

    public function ajaxValidateUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'employee_id'       => 'required|exists:employees,id',
            'regular_deduction' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }
}