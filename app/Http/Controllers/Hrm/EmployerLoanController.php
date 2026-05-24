<?php

namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\Payroll;
use App\Models\Hrm\EmployerLoan;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use App\Models\Hrm\Employee;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
class EmployerLoanController extends Controller
{
    protected function findWorkspaceEmployeeOrFail(int $employeeId): Employee
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            abort(403, 'Unauthorized employee access.');
        }

        return $employee;
    }

    protected function findWorkspaceEmployerLoanOrFail(int $id): EmployerLoan
    {
        return EmployerLoan::whereHas('employee', function ($query) {
            $query->where('id', '>', 0);
        })->findOrFail($id);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $loans = EmployerLoan::whereHas('employee', function ($query) {
            $query->where('id', '>', 0);
        })->get();
        return view('hrm.employer-loans.index', compact('loans'));
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
    //$employee = \App\Models\Hrm\Employee::all(); 
    return view('hrm.employer-loans.create', compact('employee','term'));
}

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        
        Log::info('Request Data:', $request->all());
    
        if (!$request->employee_id) {
            return back()->withErrors(['employee_id' => 'Employee is required'])->withInput();
        }
    
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'interest_rate' => 'required|numeric|min:0',
            'regular_repayment' => 'required|numeric|min:0',
            'calculate_interest_benefit' => 'nullable|boolean',
            'interest_benefit_amount' => 'nullable|numeric|min:0',
            'term' => 'required|date',
        ]);
    
        $validatedData['calculate_interest_benefit'] = (int) $request->input('calculate_interest_benefit', 0) === 1 ? 1 : 0;
        $validatedData['interest_benefit_amount'] = $validatedData['calculate_interest_benefit']
            ? round((((float) $validatedData['regular_repayment']) * (((float) $validatedData['interest_rate']) / 100)) / 12, 2)
            : 0;
        $employee_id = $request->employee_id;

        $this->findWorkspaceEmployeeOrFail((int) $validatedData['employee_id']);
    
       
        Log::info('Validated Employer Loan Data: ', $validatedData);
    
       
        $loan = EmployerLoan::create($validatedData);
        Log::info('Employer Loan Created:', $loan->toArray());
       
        Payroll::updateOrCreate(
            ['employee_id' => $validatedData['employee_id']],
            [
                'employer_loan' => $validatedData['regular_repayment'] ?? 0,
                'interest_benefit_amount' => $validatedData['calculate_interest_benefit'] ? ($validatedData['interest_benefit_amount'] ?? 0) : 0
            ]
        );
    
        return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $request->term])->with('success', 'Employer Loan Store Successfully!');
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
        $employer_loan = $this->findWorkspaceEmployerLoanOrFail((int) $id);
        $employee = $this->findWorkspaceEmployeeOrFail((int) $employer_loan->employee_id);
        $term = request('term');
        return view('hrm.employer-loans.edit', compact('employer_loan','term'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'regular_repayment' => 'required|numeric|min:0',
            'calculate_interest_benefit' => 'nullable|in:0,1',
            'interest_benefit_amount' => 'nullable|numeric|min:0',
             'term' => 'required|date',
        ]);
    
        $calculateInterestBenefit = (int) $request->input('calculate_interest_benefit', 0) === 1 ? 1 : 0;

        $employer_loan = $this->findWorkspaceEmployerLoanOrFail((int) $id);

        $this->findWorkspaceEmployeeOrFail((int) $request->input('employee_id'));
    
        $employer_loan->update([
            'employee_id' => $request->input('employee_id'),
            'interest_rate' => $request->input('interest_rate'),
            'regular_repayment' => $request->input('regular_repayment'),
            'calculate_interest_benefit' => $calculateInterestBenefit,
            'interest_benefit_amount' => $calculateInterestBenefit
                ? round((((float) $request->input('regular_repayment', 0)) * (((float) $request->input('interest_rate', 0)) / 100)) / 12, 2)
                : 0,
        ]);
    
       
        Payroll::updateOrCreate(
            ['employee_id' => $request->input('employee_id')],
            [
                'employer_loan' => $request->input('regular_repayment'),
                'interest_benefit_amount' => $calculateInterestBenefit 
                    ? ($request->input('interest_benefit_amount', 0)) 
                    : 0,
            ]
        );
        $employeeId =  $employer_loan->employee_id;
        return redirect()->route('payroll.index', ['employee_id' => $employeeId,'term' => $request->term])
        ->with('success', 'Employer Loan Updated Successfully!');
    }
    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id,$term)
    {
        $employer_loan = $this->findWorkspaceEmployerLoanOrFail((int) $id);
        $employee_id = $employer_loan->employee_id;
        $employer_loan->delete();
        return redirect()->route('payroll.index', ['employee_id' => $employee_id,'term' => $term])->with('success', 'Employer Loan Deleted Successfully.');
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id'                 => 'required|exists:employees,id',
            'interest_rate'               => 'required|numeric|min:0',
            'regular_repayment'           => 'required|numeric|min:0',
            'calculate_interest_benefit'  => 'nullable|boolean',
            'interest_benefit_amount'     => 'nullable|numeric|min:0',
            'term'                        => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }

    public function ajaxValidateUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'employee_id'                 => 'required|exists:employees,id',
            'interest_rate'               => 'required|numeric|min:0|max:100',
            'regular_repayment'           => 'required|numeric|min:0',
            'calculate_interest_benefit'  => 'nullable|in:0,1',
            'interest_benefit_amount'     => 'nullable|numeric|min:0',
            'term'                        => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }
}
