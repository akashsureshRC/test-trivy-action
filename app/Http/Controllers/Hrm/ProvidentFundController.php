<?php

namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\ProvidentFundPayroll;
use App\Models\Hrm\ProvidentFund;
use App\Models\Hrm\Employee;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
class ProvidentFundController extends Controller
{
    protected function findWorkspaceEmployeeOrFail(int $employeeId): Employee
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            abort(403, 'Unauthorized employee access.');
        }

        return $employee;
    }

    protected function findWorkspaceProvidentFundOrFail(int $id): ProvidentFund
    {
        return ProvidentFund::whereHas('employee', function ($query) {
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

    $employee = $this->findWorkspaceEmployeeOrFail((int) $employeeId);

    return view('hrm.provident-fund.create', compact( 'employee', 'term'));
}

    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id', 
            'contribution' => 'required|in:fixed_amount,percentage_rfi', 
            'fixed_contribution_employee' => 'nullable|numeric|min:0',
            'fixed_contribution_employer' => 'nullable|numeric|min:0',
            'percentage_rfi_employee' => 'nullable|numeric|min:0|max:100',
            'percentage_rfi_employer' => 'nullable|numeric|min:0|max:100',
            'category' => 'nullable|numeric|min:0',
            'term' => 'required|date',
        ]);
    
        DB::beginTransaction();
    
        try {
            $this->findWorkspaceEmployeeOrFail((int) $validated['employee_id']);
           
            $providentFund = ProvidentFund::create([
                'employee_id' => $validated['employee_id'],
                'contribution' => $validated['contribution'],
                'fixed_contribution_employee' => $validated['contribution'] == 'fixed_amount' ? $validated['fixed_contribution_employee'] : 0,
                'fixed_contribution_employer' => $validated['contribution'] == 'fixed_amount' ? $validated['fixed_contribution_employer'] : 0,
                'percentage_rfi_employee' => $validated['contribution'] == 'percentage_rfi' ? $validated['percentage_rfi_employee'] : 0,
                'percentage_rfi_employer' => $validated['contribution'] == 'percentage_rfi' ? $validated['percentage_rfi_employer'] : 0,
                'category' => $validated['category'],
                'term' => $request->term,
            ]);
    
           
            ProvidentFundPayroll::create([
                'employee_id' => $providentFund->employee_id,
                'contribution' => $providentFund->contribution,
                'fixed_contribution_employee' =>$providentFund->fixed_contribution_employee,
                'fixed_contribution_employer' => $providentFund->fixed_contribution_employer,
                'percentage_rfi_employee' => $providentFund->percentage_rfi_employee,
                'percentage_rfi_employer' => $providentFund->percentage_rfi_employer,
                'category' =>$providentFund->category,
                'term' => $request->term,
            ]);
    
            DB::commit();
    
            return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $request->term])
                ->with('success', 'Provident Fund Created Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create Provident Fund. ' . $e->getMessage());
        }
    }
    

   public function index(Request $request)
    {
        $employeeId = $request->get('employee_id');
        $employee = $this->findWorkspaceEmployeeOrFail((int) $employeeId);
        $providentFund = ProvidentFund::where('employee_id', $employeeId)->first();  
        return view('hrm.payroll.index', compact('employee', 'providentFund'));
    }
        
        /**public function index(Request $request)
        {
            $employeeId = $request->get('employee_id');
            $employee = Employee::find($employeeId);
        
           
            if (!$employee) {
                return redirect()->back()->with('error', 'Employee not found.');
            }
        
           
            $providentFund = ProvidentFund::where('employee_id', $employeeId)->first();
        
            return view('hrm.payroll.index', compact('employee', 'providentFund'));
        }
        */

    
   /**public function edit($id)
    {
        $providentFund = ProvidentFund::findOrFail($id);
        $employee = Employee::find($providentFund->employee_id);
        $employees = Employee::all();
        return view('hrm.provident-fund.edit', compact('providentFund', 'employees','employee'));
    }*/
    public function edit($id, Request $request)
    {
      
        $provident_fund = ProvidentFund::whereHas('employee', function ($query) {
            $query->where('id', '>', 0);
        })->find($id);
    
      
        if (!$provident_fund) {
            $employeeId = request('employee_id');
    
            
            $provident_fund = ProvidentFund::whereHas('employee', function ($query) {
                $query->where('id', '>', 0);
            })->where('employee_id', $employeeId)->first();
    
            
            if (!$provident_fund) {
                return redirect()->route('payroll.index', ['employee_id' => $employeeId])
                                 ->with('error', 'No Provident Fund entry found.');
            }
        }
       $term = $request->query('term');
            $employee = $this->findWorkspaceEmployeeOrFail((int) ($provident_fund->employee_id ?? request('employee_id')));
        return view('hrm.provident-fund.edit', compact('provident_fund', 'employee','term'));
    }
    

    public function update(Request $request, $id)
    {
      
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'contribution' => 'required|in:fixed_amount,percentage_rfi',
            'fixed_contribution_employee' => 'nullable|numeric|min:0',
            'fixed_contribution_employer' => 'nullable|numeric|min:0',
            'percentage_rfi_employee' => 'nullable|numeric|min:0|max:100',
            'percentage_rfi_employer' => 'nullable|numeric|min:0|max:100',
            'category' => 'nullable|numeric|min:0',
            'term' => 'required|date',
        ]);
    
        DB::beginTransaction();
    
        try {
                $this->findWorkspaceEmployeeOrFail((int) $validated['employee_id']);
           
              $provident_fund = $this->findWorkspaceProvidentFundOrFail((int) $id);
            $provident_fund->update([
                'employee_id' => $validated['employee_id'],
                'contribution' => $validated['contribution'],
                'fixed_contribution_employee' => $validated['contribution'] == 'fixed_amount' ? $validated['fixed_contribution_employee'] : 0,
                'fixed_contribution_employer' => $validated['contribution'] == 'fixed_amount' ? $validated['fixed_contribution_employer'] : 0,
                'percentage_rfi_employee' => $validated['contribution'] == 'percentage_rfi' ? $validated['percentage_rfi_employee'] : 0,
                'percentage_rfi_employer' => $validated['contribution'] == 'percentage_rfi' ? $validated['percentage_rfi_employer'] : 0,
                'category' => $validated['category'],
                'term' => $validated['term'],
            ]);
    
           
            $providentFundPayroll = ProvidentFundPayroll::where('employee_id', $provident_fund->employee_id)->first();

            if ($providentFundPayroll) {
                $providentFundPayroll->update([
                    'employee_id' => $provident_fund->employee_id,
                    'contribution' => $provident_fund->contribution,
                    'fixed_contribution_employee' => $provident_fund->fixed_contribution_employee,
                    'fixed_contribution_employer' => $provident_fund->fixed_contribution_employer,
                    'percentage_rfi_employee' => $provident_fund->percentage_rfi_employee,
                    'percentage_rfi_employer' => $provident_fund->percentage_rfi_employer,
                    'category' => $provident_fund->category,
                    'term' => $validated['term'],
                ]);
            } else {
                ProvidentFundPayroll::create([
                    'employee_id' => $provident_fund->employee_id,
                    'contribution' => $provident_fund->contribution,
                    'fixed_contribution_employee' => $provident_fund->fixed_contribution_employee,
                    'fixed_contribution_employer' => $provident_fund->fixed_contribution_employer,
                    'percentage_rfi_employee' => $provident_fund->percentage_rfi_employee,
                    'percentage_rfi_employer' => $provident_fund->percentage_rfi_employer,
                    'category' => $provident_fund->category,
                     'term' => $request->term,
                ]);
            }
    
            DB::commit();
    
            return redirect()->route('payroll.index', ['employee_id' => $request->employee_id, 'term' => $request->term])
            ->with('success', 'Provident Fund Updated Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update Provident Fund. ' . $e->getMessage());
        }
    }
    

   
    /**public function destroy($id)
    {
        $providentFund = ProvidentFund::findOrFail($id);
        $providentFund->delete();

        return redirect()->route('payroll.index')
            ->with('success', 'Provident fund has been deleted successfully.');
    }*/
    public function destroy(Request $request)
    {
        $employeeId = $request->employee_id;
        $term = $request->term;

        $this->findWorkspaceEmployeeOrFail((int) $employeeId);
    
        DB::beginTransaction();
    
        try {
            // Try to delete ProvidentFund if it exists
            $providentFund = ProvidentFund::whereHas('employee', function ($query) {
                $query->where('id', '>', 0);
            })->where('employee_id', $employeeId)->first();
            if ($providentFund) {
                $providentFund->delete();
            }
    
            // Delete all related payroll records
            ProvidentFundPayroll::where('employee_id', $employeeId)
                ->delete();
    
            DB::commit();
    
          return redirect()->route('payroll.index', [
    'employee_id' => $request->input('employee_id'),
    'term' => $request->input('term'),
])->with('success', 'Provident Fund and Payroll records deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'contribution' => 'required|in:fixed_amount,percentage_rfi',
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
            'contribution' => 'required|in:fixed_amount,percentage_rfi',
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

    

