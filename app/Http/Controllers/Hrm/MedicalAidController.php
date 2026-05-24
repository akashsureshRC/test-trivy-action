<?php

namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\MedicalAid;
use App\Models\Hrm\Payroll;
use App\Models\Hrm\BasicSalary;
use App\Models\Hrm\Employee;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\TaxYear;

class MedicalAidController extends Controller
{
    protected function findWorkspaceEmployeeOrFail(int $employeeId): Employee
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            abort(403, 'Unauthorized employee access.');
        }

        return $employee;
    }

    protected function findWorkspaceMedicalAidOrFail(int $id): MedicalAid
    {
        return MedicalAid::whereHas('employee', function ($query) {
            $query->where('id', '>', 0);
        })->findOrFail($id);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $medicalAids = MedicalAid::whereHas('employee', function ($query) {
            $query->where('id', '>', 0);
        })->with('employee')->paginate(10);
        return view('hrm.payroll.index', compact('medicalAids'));
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
        $employees = Employee::all(); 
        return view('hrm.medical-aid.create', compact('employees','employee','term'));
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
            'total_amount' => 'required|numeric|min:0',
            'employer_contribution' => 'required|numeric|min:0',
            'members' => 'required|integer|min:1',
            'term' => 'required|date', 
        ]);

        $this->findWorkspaceEmployeeOrFail((int) $request->employee_id);

        $term = $request->input('term');
        $payroll = Payroll::firstOrCreate(
            ['employee_id' => $request->employee_id],
            [
                'basic_salary' => BasicSalary::where('employee_id', $request->employee_id)->value('fixed_salary') ?? 0,
                'tax_pay' => 0,
                'net_pay' => 0,
            ]
        );

        
        $payroll->update([
            'basic_salary' => BasicSalary::where('employee_id', $request->employee_id)->value('fixed_salary') ?? 0,
        ]);

       
        $employee_payment = $request->total_amount - $request->employer_contribution;

        
        $medicalAid = MedicalAid::create([
            'employee_id' => $request->employee_id,
            'payroll_id' => $payroll->id,
            'total_amount' => $request->total_amount,
            'employer_contribution' => $request->employer_contribution,
            'employee_payment' => $employee_payment,
            'apply_tax_credits' => $request->apply_tax_credits ?? 0,
            'members' => $request->members,
            'term' => $term,
        ]);

        
        $this->updatePayroll($payroll, $term);

        return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $term])
        ->with('success', 'Medical Aid Added Successfully');
    }


private function updatePayroll($payroll, $term = null)
{
    
    $medicalAids = MedicalAid::where('payroll_id', $payroll->id)->get();

    
    $totalMedicalAidDeduction = $medicalAids->sum('employee_payment');

    
    $basicSalary = $payroll->basic_salary;

    $resolvedTerm = $term ?? $medicalAids->first()?->term;
    $taxYear = $resolvedTerm ? TaxYear::resolveForTerm($resolvedTerm) : null;
    $medicalAidTaxRate = $taxYear ? $taxYear->medical_aid_tax_rate : 0.10;

    
    $payroll->update([
        'tax_pay' => $totalMedicalAidDeduction * $medicalAidTaxRate, 
        'net_pay' => $basicSalary - $totalMedicalAidDeduction, 
    ]);
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
        $medicalAid = $this->findWorkspaceMedicalAidOrFail((int) $id);
        $this->findWorkspaceEmployeeOrFail((int) $medicalAid->employee_id);
        $employees = Employee::all(); 
        $term = $request->query('term');
        return view('hrm.medical-aid.edit', compact('medicalAid', 'employees','term'));
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
            'total_amount' => 'required|numeric|min:0',
            'employer_contribution' => 'nullable|numeric|min:0',
            'employee_id' => 'required|exists:employees,id', 
            'apply_tax_credits' => 'boolean',
            'members' => 'required|integer|min:1',
            'term' => 'required|date',
        ]);
 $term = $request->input('term');

        $this->findWorkspaceEmployeeOrFail((int) $request->employee_id);
       
        $medicalAid = $this->findWorkspaceMedicalAidOrFail((int) $id);
        $medicalAid->update([
            'total_amount' => $request->total_amount,
            'employer_contribution' => $request->employer_contribution ?? 0,
            'employee_payment' => $request->has('employee_payment') ? 1 : 0,
            'apply_tax_credits' => $request->has('apply_tax_credits') ? 1 : 0,
            'employee_id' => $request->employee_id,
            'members' => $request->members,
            'term' => $term,
        ]);

        return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $term,])
        ->with('success', 'Medical Aid Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id,$term)
    {
        $medicalAid = $this->findWorkspaceMedicalAidOrFail((int) $id);
        $medicalAid->delete();
        $employee_id =  $medicalAid->employee_id;
        return redirect()->route('payroll.index', ['employee_id' => $employee_id,'term' => $term])->with('success', 'Medical Aid Deleted Successfully!');

    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'total_amount' => 'required|numeric|min:0',
            'employer_contribution' => 'required|numeric|min:0',
            'members' => 'required|integer|min:1',
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
            'total_amount' => 'required|numeric|min:0',
            'employer_contribution' => 'nullable|numeric|min:0',
            'employee_id' => 'required|exists:employees,id',
            'apply_tax_credits' => 'boolean',
            'members' => 'required|integer|min:1',
            'term' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return response()->json(['success' => true]);
    }
}
