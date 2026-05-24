<?php

namespace App\Http\Controllers\Hrm;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Hrm\Employee;
class EmployeeSalaryController extends Controller
{
    protected function findWorkspaceEmployeeOrFail(int $id): Employee
    {
        $employee = Employee::where('id', $id)->first();

        if (!$employee) {
            abort(403, 'Unauthorized employee access.');
        }

        return $employee;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('hrm.employee-salary.index');
    //$employeeProfiles = Employee::all();
        //return view('hrm.employee-salary.index', compact('employeeProfiles'));
    }
   
    public function getSalaryDetail(Request $request)
    {
        $employeeId = $request->query('employee_id');

        $employee = Employee::with('salaryDetail')->where('id', $employeeId)->first();

        if ($employee && $employee->salaryDetail) {
            return response()->json([
                'calculation_type' => $employee->salaryDetail->calculation_type,
                'monthly_amount'   => $employee->salaryDetail->monthly_amount,
                'annual_amount'    => $employee->salaryDetail->annual_amount,
            ]);
        }
        return response()->json(['message' => 'No salary details found.'], 404);
    }

    /**
     * 
     *Show the form for creating a new resource.
     * @return Renderable
     */
    //public function create(Request $request)
   // {
        //$employee = Employee::findOrFail($request->employee_id);
        //return view('employee-salary.create', compact('employee'));
  //  }
  public function create()
  {
      return view('hrm.employee-salary.create');
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
        'fixed_salary' => 'required|numeric|min:0',
    ]);

    $this->findWorkspaceEmployeeOrFail((int) $request->employee_id);

    \App\Models\Hrm\BasicSalary::create([
        'employee_id' => $request->employee_id,
        'fixed_salary' => $request->fixed_salary,
    ]);

    return redirect()->route('payroll.regularinputs', ['employee_id' => $request->employee_id])
                     ->with('success', 'Basic Salary added successfully.');
}

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $employee = $this->findWorkspaceEmployeeOrFail((int) $id);
        return view('employee-salary.show', compact('employee'));
    }
    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('hrm.edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
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
