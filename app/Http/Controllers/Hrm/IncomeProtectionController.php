<?php

namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\IncomeProtection;
use App\Models\Hrm\Employee;
use App\Models\Hrm\Payroll;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class IncomeProtectionController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $payrolls = Payroll::with(['employeeProfile', 'incomeProtection'])->get();
    
        return view('payroll.index', compact('payrolls'));
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

        $employee = Employee::find($employeeId);

        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found.');
        }
        return view('hrm.income-protection.create',compact('employee','term'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
   
        public function store(Request $request)
        {
            $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'amount' => 'required|numeric|min:0',
                'amount_deducted' => 'required|numeric|min:0',
                'amount_paid' => 'required|numeric|min:0',
                'employer_own' => 'nullable|boolean',
                'term' => 'required|date', 
            ]);
    
           $term = $request->term;
            $incomeProtection = IncomeProtection::where('employee_id', $request->employee_id)->first();
    
            if ($incomeProtection) {
                return redirect()->route('payroll.index', ['employee_id' => $request->employee_id, 'term' => $term])
                ->with('success', 'Income Protection Store Successfully.');
            }
    
          
            IncomeProtection::create([
                'employee_id' => $request->employee_id,
                'amount' => $request->amount ?? 0,
                'amount_deducted' => $request->amount_deducted ?? 0,
                'amount_paid' => $request->amount_paid ?? 0,
                'employer_own' => $request->has('employer_own'),
                'term' => $term,
            ]);
    
            
            $payroll = Payroll::where('employee_id', $request->employee_id)->first();
    
            if ($payroll) {
              
                $new_net_pay = max($payroll->net_pay - $request->amount_deducted, 0);
    
                $payroll->update([
                    'income_protection_paid_by_employee' => $request->amount ?? 0,
                    'income_protection_deducted_from_employee' => $request->amount_deducted ?? 0,
                    'income_protection_paid_by_employer' => $request->amount_paid ?? 0,
                    'income_protection_ownership' => $request->has('employer_own'),
                    'net_pay' => $new_net_pay,
                ]);
            }
    
            return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $request->term])
                             ->with('success', 'Income Protection Added Successfully.');
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
    public function edit($id, Request $request)
    {
        $incomeProtection = IncomeProtection::findOrFail($id);
         $term = $request->query('term');  
        return view('hrm.income-protection.edit', compact('incomeProtection','term'));
    }

    /**
     * Update the specified resource in storage._table 
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'amount_deducted' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'term' => 'required|date',
        ], [
            'amount.required' => 'This field is required.',
            'amount_deducted.required' => 'This field is required.',
            'amount_paid.required' => 'This field is required.',
        ]);
    
       $term = $request->term;
        $incomeProtection = IncomeProtection::findOrFail($id);
        $incomeProtection->update([
            'amount' => $request->amount,
            'amount_deducted' => $request->amount_deducted,
            'amount_paid' => $request->amount_paid,
            'employer_own' => $request->has('employer_own'),  
            'term' => $term,
        ]);
    
        return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $term])
                             ->with('success', 'Income Protection Updated Successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id,$term)
    {
        $incomeProtection = IncomeProtection::findOrFail($id);
        $incomeProtection->delete();
        $employee_id =  $incomeProtection->employee_id;
        return redirect()->route('payroll.index', ['employee_id' => $employee_id,'term' => $term])->with('success', 'Income Protection Deleted Successfully!');
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0',
            'amount_deducted' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'employer_own' => 'nullable|boolean',
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
            'amount' => 'required|numeric|min:0',
            'amount_deducted' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'term' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return response()->json(['success' => true]);
    }
}
