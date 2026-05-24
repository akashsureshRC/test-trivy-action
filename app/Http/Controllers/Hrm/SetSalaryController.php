<?php

namespace App\Http\Controllers\Hrm;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Hrm\Allowance;
use App\Models\Hrm\Commission;
use App\Models\Hrm\CompanyContribution;
use App\Models\Hrm\Employee;
use App\Models\Hrm\OtherPayment;
use App\Models\Hrm\Overtime;
use App\Models\Hrm\SaturationDeduction;
use App\Events\Hrm\UpdateEmployeeSalary;

class SetSalaryController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (Auth::user()->isAbleTo('setsalary manage')) {
            if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
                $employees = Employee::where('user_id', Auth::user()->id)->where('workspace_id', getActiveWorkspace())->get();
            } else {
                $employees = Employee::where('workspace_id', getActiveWorkspace())->get();
            }
            return view('hrm.setsalary.index', compact('employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('hrm.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $payslip_type      = collect();
        $allowance_options = collect();
        $deduction_options = collect();
        if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
            $currentEmployee      = Employee::where('user_id', '=', \Auth::user()->id)->where('workspace_id', getActiveWorkspace())->first();
            $allowances           = Allowance::where('employee_id', $currentEmployee->id)->where('workspace', getActiveWorkspace())->get();
            $commissions          = Commission::where('employee_id', $currentEmployee->id)->where('workspace', getActiveWorkspace())->get();
            $saturationdeductions = SaturationDeduction::where('employee_id', $currentEmployee->id)->where('workspace', getActiveWorkspace())->get();
            $otherpayments        = OtherPayment::where('employee_id', $currentEmployee->id)->where('workspace', getActiveWorkspace())->get();
            $companycontributions = CompanyContribution::where('employee_id', $currentEmployee->id)->where('workspace', getActiveWorkspace())->get();
            $overtimes            = Overtime::where('employee_id', $currentEmployee->id)->where('workspace', getActiveWorkspace())->get();
            $employee             = Employee::where('user_id', '=', \Auth::user()->id)->where('workspace_id', getActiveWorkspace())->first();

            foreach ($allowances as  $value) {
                if ($value->type == 'percentage') {
                    $emp          = Employee::find($value->employee_id);
                    $empsal  = $value->amount * ($emp->salary ?? 0) / 100;
                    $value->tota_allow = $empsal;
                }
            }

            foreach ($commissions as  $value) {
                if ($value->type == 'percentage') {
                    $emp          = Employee::find($value->employee_id);
                    $empsal  = $value->amount * ($emp->salary ?? 0) / 100;
                    $value->tota_allow = $empsal;
                }
            }

            foreach ($saturationdeductions as  $value) {
                if ($value->type == 'percentage') {
                    $emp          = Employee::find($value->employee_id);
                    $empsal  = $value->amount * ($emp->salary ?? 0) / 100;
                    $value->tota_allow = $empsal;
                }
            }

            foreach ($otherpayments as  $value) {
                if ($value->type == 'percentage') {
                    $emp          = Employee::find($value->employee_id);
                    $empsal  = $value->amount * ($emp->salary ?? 0) / 100;
                    $value->tota_allow = $empsal;
                }
            }
            
            foreach ($companycontributions as  $value) {
                if ($value->type == 'percentage') {
                    $emp          = Employee::find($value->employee_id);
                    $empsal  = $value->amount * ($emp->salary ?? 0) / 100;
                    $value->tota_allow = $empsal;
                }
            }
            return view('hrm.setsalary.employee_salary', compact('employee', 'payslip_type', 'allowance_options', 'commissions', 'overtimes', 'otherpayments', 'saturationdeductions', 'deduction_options', 'allowances', 'companycontributions'));
        } else {
            $allowances           = Allowance::where('employee_id', $id)->where('workspace', getActiveWorkspace())->get();
            $commissions          = Commission::where('employee_id', $id)->where('workspace', getActiveWorkspace())->get();
            $saturationdeductions = SaturationDeduction::where('employee_id', $id)->where('workspace', getActiveWorkspace())->get();
            $otherpayments        = OtherPayment::where('employee_id', $id)->where('workspace', getActiveWorkspace())->get();
            $companycontributions = CompanyContribution::where('employee_id', $id)->where('workspace', getActiveWorkspace())->get();
            $overtimes            = Overtime::where('employee_id', $id)->where('workspace', getActiveWorkspace())->get();
            $employee             = Employee::where('id', $id)->where('workspace_id', getActiveWorkspace())->firstOrFail();

            foreach ($allowances as  $value) {
                if ($value->type == 'percentage') {
                    $emp          = Employee::find($value->employee_id);
                    $empsal  = $value->amount * ($emp->salary ?? 0) / 100;
                    $value->tota_allow = $empsal;
                }
            }

            foreach ($commissions as  $value) {
                if ($value->type == 'percentage') {
                    $emp          = Employee::find($value->employee_id);
                    $empsal  = $value->amount * ($emp->salary ?? 0) / 100;
                    $value->tota_allow = $empsal;
                }
            }

            foreach ($saturationdeductions as  $value) {
                if ($value->type == 'percentage') {
                    $emp          = Employee::find($value->employee_id);
                    $empsal  = $value->amount * ($emp->salary ?? 0) / 100;
                    $value->tota_allow = $empsal;
                }
            }

            foreach ($otherpayments as  $value) {
                if ($value->type == 'percentage') {
                    $emp          = Employee::find($value->employee_id);
                    $empsal  = $value->amount * ($emp->salary ?? 0) / 100;
                    $value->tota_allow = $empsal;
                }
            }
            
            foreach ($companycontributions as  $value) {
                if ($value->type == 'percentage') {
                    $emp          = Employee::find($value->employee_id);
                    $empsal  = $value->amount * ($emp->salary ?? 0) / 100;
                    $value->tota_allow = $empsal;
                }
            }

            return view('hrm.setsalary.employee_salary', compact('employee', 'payslip_type', 'allowance_options', 'commissions', 'overtimes', 'otherpayments', 'saturationdeductions', 'deduction_options', 'allowances', 'companycontributions'));
        }
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

    public function employeeBasicSalary($id)
    {
        if (!Auth::user()->isAbleTo('setsalary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $payslip_type = collect();
        // Workspace-scoped lookup
        $employee     = Employee::where('id', $id)->where('workspace_id', getActiveWorkspace())->firstOrFail();
        $bankAccount = [];
        if (moduleIsActive('Account')) {
            $bankAccount = BankAccount::select('*', DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('workspace', getActiveWorkspace())->get()->pluck('name', 'id');
        }
        return view('hrm.setsalary.basic_salary', compact('employee', 'payslip_type', 'bankAccount'));
    }

    public function employeeUpdateSalary(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('setsalary manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $rules = [
            'salary_type' => 'required',
            'salary' => ['required','numeric','min:0'],
        ];
        if (moduleIsActive('Account')) {
            $rules['account_type'] = 'required';
        }
        $validator = \Validator::make(
            $request->all(),
            $rules
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }
        // Workspace-scoped lookup to prevent cross-tenant access
        $employee = Employee::where('id', $id)->where('workspace_id', getActiveWorkspace())->firstOrFail();

        // Only allow safe salary-related fields — prevent mass assignment of password, ess_enabled, etc.
        $safeInput = $request->only(['salary_type', 'salary', 'account_type']);
        $employee->fill($safeInput)->save();

        event(new UpdateEmployeeSalary($request, $employee));

        return redirect()->back()->with('success', 'Employee Salary Updated.');
    }
}
