<?php

namespace App\Http\Controllers\Hrm;

use App\Models\Hrm\PensionFund;
use App\Models\Hrm\Employee;
use Illuminate\Contracts\Support\Renderable;
use App\Models\Hrm\PensionFundPayroll;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
class PensionFundController extends Controller
{
    protected function ensureEmployeeInWorkspace(int $employeeId): Employee
    {
        $employee = Employee::where('id', $employeeId)->first();

        if (!$employee) {
            abort(403, 'Unauthorized employee access.');
        }

        return $employee;
    }

    protected function findWorkspacePensionFundOrFail(int $id): PensionFund
    {
        return PensionFund::whereHas('employee', function ($query) {
            $query->where('id', '>', 0);
        })->findOrFail($id);
    }

    
   
   
    public function create(Request $request)
    {
        $employeeId = $request->employee_id;
        $term = $request->term;

        if (!$employeeId) {
            return redirect()->back()->with('error', 'Employee ID is required.');
        }

        $employee = Employee::where('id', $employeeId)->first();

        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found.');
        }
        return view('hrm.pension-fund.create', compact('employee','term'));
    }

    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id', 
            'pension' => 'required|in:fixed_amount,percentage_rfi', 
            'fixed_contribution_employee' => 'nullable|numeric|min:0',
            'fixed_contribution_employer' => 'nullable|numeric|min:0',
            'percentage_rfi_employee' => 'nullable|numeric|min:0|max:100',
            'percentage_rfi_employer' => 'nullable|numeric|min:0|max:100',
            'category' => 'nullable|numeric|min:0',
            'term' => 'required|date', 
        ]);
    
        $this->ensureEmployeeInWorkspace((int) $validated['employee_id']);

        DB::beginTransaction();
    
        try {
            $term = $request->input('term');
            $pensionFund = PensionFund::create([
                'employee_id' => $validated['employee_id'],
                'pension' => $validated['pension'],
                'fixed_contribution_employee' => $validated['pension'] == 'fixed_amount' ? $validated['fixed_contribution_employee'] : 0,
                'fixed_contribution_employer' => $validated['pension'] == 'fixed_amount' ? $validated['fixed_contribution_employer'] : 0,
                'percentage_rfi_employee' => $validated['pension'] == 'percentage_rfi' ? $validated['percentage_rfi_employee'] : 0,
                'percentage_rfi_employer' => $validated['pension'] == 'percentage_rfi' ? $validated['percentage_rfi_employer'] : 0,
                'category' => $validated['category'],
                'term' => $term,
            ]);
    
            
            PensionFundPayroll::create([
                'employee_id' => $pensionFund->employee_id,
                'pension' => $pensionFund->pension,
                'fixed_contribution_employee' => $pensionFund->fixed_contribution_employee,
                'fixed_contribution_employer' => $pensionFund->fixed_contribution_employer,
                'percentage_rfi_employee' => $pensionFund->percentage_rfi_employee,
                'percentage_rfi_employer' => $pensionFund->percentage_rfi_employer,
                'category' => $pensionFund->category,
                'term' => $term,
            ]);
    
            DB::commit();
    
            return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $request->term])
                ->with('success', 'Pension Fund Created Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create Pension Fund. ' . $e->getMessage());
        }
    }
    

    public function index()
    {
        $pensionFunds = PensionFund::whereHas('employee', function ($query) {
            $query->where('id', '>', 0);
        })->get();
        return view('hrm.pension-fund.index', compact('pensionFunds'));
    }

    
    public function edit($id, Request $request)
    {
        $pensionFund = $this->findWorkspacePensionFundOrFail((int) $id);
        $employee = $this->ensureEmployeeInWorkspace((int) $pensionFund->employee_id);
        $term = $request->query('term');
        return view('hrm.pension-fund.edit', compact('pensionFund','employee','term'));
    }


    public function update(Request $request, $id)
    {
      //dd($request->all());
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'pension' => 'required|in:fixed_amount,percentage_rfi',
            'fixed_contribution_employee' => 'nullable|numeric|min:0',
            'fixed_contribution_employer' => 'nullable|numeric|min:0',
            'percentage_rfi_employee' => 'nullable|numeric|min:0|max:100',
            'percentage_rfi_employer' => 'nullable|numeric|min:0|max:100',
            'category' => 'nullable|numeric|min:0',
            'term' => 'required|date',
        ]);
    
        $this->ensureEmployeeInWorkspace((int) $validated['employee_id']);

        DB::beginTransaction();
    
        try {
             $term = $request->input('term');
            $pensionFund = $this->findWorkspacePensionFundOrFail((int) $id);
            $pensionFund->update([
                'employee_id' => $validated['employee_id'],
                'pension' => $validated['pension'],
                'fixed_contribution_employee' => $validated['pension'] == 'fixed_amount' ? $validated['fixed_contribution_employee'] : 0,
                'fixed_contribution_employer' => $validated['pension'] == 'fixed_amount' ? $validated['fixed_contribution_employer'] : 0,
                'percentage_rfi_employee' => $validated['pension'] == 'percentage_rfi' ? $validated['percentage_rfi_employee'] : 0,
                'percentage_rfi_employer' => $validated['pension'] == 'percentage_rfi' ? $validated['percentage_rfi_employer'] : 0,
                'category' => $validated['category'],
                'term' => $term,
            ]);
    
          
            $pensionFundPayroll = PensionFundPayroll::where('employee_id', $pensionFund->employee_id)->first();
    
            if ($pensionFundPayroll) {
                $pensionFundPayroll->update([
                    'employee_id' => $pensionFund->employee_id,
                    'pension' => $pensionFund->pension,
                    'fixed_contribution_employee' => $pensionFund->fixed_contribution_employee,
                    'fixed_contribution_employer' => $pensionFund->fixed_contribution_employer,
                    'percentage_rfi_employee' => $pensionFund->percentage_rfi_employee,
                    'percentage_rfi_employer' => $pensionFund->percentage_rfi_employer,
                    'category' => $pensionFund->category,
                    //'term' => $term,
                ]);
            } else {
             
                PensionFundPayroll::create([
                    'employee_id' => $pensionFund->employee_id,
                    'pension' => $pensionFund->pension,
                    'fixed_contribution_employee' => $pensionFund->fixed_contribution_employee,
                    'fixed_contribution_employer' => $pensionFund->fixed_contribution_employer,
                    'percentage_rfi_employee' => $pensionFund->percentage_rfi_employee,
                    'percentage_rfi_employer' => $pensionFund->percentage_rfi_employer,
                    'category' => $pensionFund->category,
                    'term' => $term,
                ]);
            }
    
            DB::commit();
    
            return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $term])
            ->with('success', 'Pension Fund Updated Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update Pension Fund. ' . $e->getMessage());
        }
    }
    

   
    public function destroy($id,$term)
    {
        $pensionFund = $this->findWorkspacePensionFundOrFail((int) $id);
        $pensionFund->delete();

        $employee_id =  $pensionFund->employee_id;
        return redirect()->route('payroll.index', ['employee_id' => $employee_id,'term' => $term])->with('success', 'Pension Fund Deleted Successfully!');
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'pension' => 'required|in:fixed_amount,percentage_rfi',
            'fixed_contribution_employee' => 'nullable|numeric|min:0',
            'fixed_contribution_employer' => 'nullable|numeric|min:0',
            'percentage_rfi_employee' => 'nullable|numeric|min:0|max:100',
            'percentage_rfi_employer' => 'nullable|numeric|min:0|max:100',
            'category' => 'nullable|numeric|min:0',
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
            'employee_id' => 'required|exists:employees,id',
            'pension' => 'required|in:fixed_amount,percentage_rfi',
            'fixed_contribution_employee' => 'nullable|numeric|min:0',
            'fixed_contribution_employer' => 'nullable|numeric|min:0',
            'percentage_rfi_employee' => 'nullable|numeric|min:0|max:100',
            'percentage_rfi_employer' => 'nullable|numeric|min:0|max:100',
            'category' => 'nullable|numeric|min:0',
            'term' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return response()->json(['success' => true]);
    }
}
