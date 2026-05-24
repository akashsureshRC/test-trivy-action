<?php

namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\Employee;
use App\Models\Hrm\EmployerLoan;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Hrm\Garnishee;
use App\Models\Hrm\Payroll;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
class GarnisheeController extends Controller
{
    protected function ensureEmployeeInWorkspace(int $employeeId): Employee
    {
        $employee = Employee::where('id', $employeeId)->first();

        if (!$employee) {
            abort(403, 'Unauthorized employee access.');
        }

        return $employee;
    }

    protected function findWorkspaceGarnisheeOrFail(int $id): Garnishee
    {
        return Garnishee::whereHas('employee', function ($query) {
            $query->where('id', '>', 0);
        })->findOrFail($id);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $payrolls = Payroll::with('employeeProfile', 'garnishee')
            ->whereHas('employeeProfile', function ($query) {
                $query->where('id', '>', 0);
            })->get();
    
        return view('payroll.index', compact('payrolls'));
        //dd($payrolls->toArray()); 
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

        $employee = Employee::where('id', $employeeId)->first();

        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found.');
        }
        return view('hrm.garnishee.create', compact('employee','term'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
   
        
     
     public function store(Request $request)
     {
                 $this->ensureEmployeeInWorkspace((int) $request->employee_id);

         $request->validate([
             'employee_id' => 'required|exists:employees,id',
             'installment' => 'required|numeric|min:1',
             'term' => 'required|date',
         ]);
     
        
         $garnishee = Garnishee::create([
             'employee_id' => $request->employee_id,
             'installment' => $request->installment,
             'term' => $request->term,
         ]);
     
         $payroll = Payroll::where('employee_id', $request->employee_id)->first();
     
         if ($payroll) {
             
             $net_pay = max($payroll->net_pay - $request->installment, 0);
     
            
             $payroll->update([
                 'installment' => $request->installment, 
                 'net_pay' => $net_pay,
             ]);
         } else {
             
             Payroll::create([
                 'employee_id' => $request->employee_id,
                 'installment' => $request->installment,
                 'net_pay' => 0, 
             ]);
         }
     
         return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $request->term])
         ->with('success', 'Garnishee Added Updated Successfully.');
     }
     

     /**
      * Show the specified resource.
      */
    
   
    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $garnishee = $this->findWorkspaceGarnisheeOrFail((int) $id);
        return view('hrm.garnishee.show', compact('garnishee'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id, Request $request)
{
        $garnishee = $this->findWorkspaceGarnisheeOrFail((int) $id);
        $employee = $this->ensureEmployeeInWorkspace((int) $garnishee->employee_id);
    $term = $request->query('term');
    return view('hrm.garnishee.edit', compact('garnishee', 'employee','term'));
}
    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
{
        $garnishee = $this->findWorkspaceGarnisheeOrFail((int) $id);
    $this->ensureEmployeeInWorkspace((int) $request->employee_id);


    $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'installment' => 'required|numeric|min:1',
        'term' => 'required|date',
    ]);

   
    $garnishee->update([
        'installment' => $request->installment,
        'term' => $request->term,
    ]); 
    $payroll = Payroll::where('employee_id', $request->employee_id)->first();
    if ($payroll) {
        $payroll->net_pay = max($payroll->gross_salary - $this->calculateDeductions($request->employee_id), 0);
        $payroll->update(['net_pay' => $payroll->net_pay]);
    }

    return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $request->term])
    ->with('success', 'Garnishee updated successfully.');
}


    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id,$term)
    {
        $garnishee = $this->findWorkspaceGarnisheeOrFail((int) $id);
        $garnishee->delete();
        $employee_id =  $garnishee->employee_id;
        return redirect()->route('payroll.index', ['employee_id' => $employee_id,'term' => $term])->with('success', 'Garnishee Deleted Successfully!');
    }


    private function calculateDeductions($employeeId)
{
    $totalDeductions = 0;

    // 🔹 Garnishee Deduction
    $garnishee = Garnishee::where('employee_id', $employeeId)->latest('id')->first();
    if ($garnishee) {
        $totalDeductions += $garnishee->installment;
    }

    // 🔹 Loan Repayment Deduction (If applicable)
    $employerloan = EmployerLoan::where('employee_id', $employeeId)->latest('id')->first();
    if ($employerloan) {
        $totalDeductions += $employerloan->monthly_installment;
    }

    return $totalDeductions;
}

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id'  => 'required|exists:employees,id',
            'installment'  => 'required|numeric|min:1',
            'term'         => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }

    public function ajaxValidateUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'employee_id'  => 'required|exists:employees,id',
            'installment'  => 'required|numeric|min:1',
            'term'         => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }
}
