<?php

namespace App\Http\Controllers\Hrm;

use Carbon\Carbon;
use App\Models\Hrm\EmployeeBenefit;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Hrm\Employee;
use App\Models\Hrm\PaySlip;

class EmployeeBenefitController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('hrm.index');
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

        $employee = Employee::find($employeeId);

        if (!$employee) {
            return redirect()->route('payroll.index')->with('error', 'Employee not found.');
        }
        return view('hrm.employee-benefits.create',compact('employee','term'));
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

        ]);

        try {
            if($request->term){
                $term = $request->term;
            }else{
            $payslip = PaySlip::where('employee_id',$request->employee_id)->latest('id')->first();

            if ($payslip && $payslip->salary_month) {
                if($payslip->status == 0){
                    $term = Carbon::parse($payslip->salary_month.'-01')->endOfMonth()->format('Y-m-d');
                }else{
                    $term = Carbon::parse($payslip->salary_month.'-01')->addMonth()->endOfMonth()->format('Y-m-d');
                }
            }else{
                $employee = Employee::findOrFail($request->employee_id);
                $term = Carbon::parse($employee->date_of_appointment)->endOfMonth()->format('Y-m-d');
            }
        }
             //$term = $request->term ?? $term;
            EmployeeBenefit::create([
                'employee_id' => $request->employee_id,
                'term' => $term,
                'amount' => $request->amount
            ]);

            DB::commit();
            return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $term])
                    ->with('success', 'Employee Debt Benefits added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('payroll.index')->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
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
    public function edit(Request $request, $id)
    {
        $employeeBenefit =  EmployeeBenefit::findOrFail($id);
        $term = $request->query('term', $employeeBenefit->term);
        return view('hrm.employee-benefits.edit', compact('employeeBenefit','term'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {

        $employeeBenefit = EmployeeBenefit::findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'term'               => 'required|date',
        ]);

        $employeeBenefit->update($request->all());

        return redirect()->route('payroll.index', ['employee_id' => $employeeBenefit->employee_id,'term' => $employeeBenefit->term])
                    ->with('success', 'Employee Debt Benefits updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $employeeBenefit = EmployeeBenefit::findOrFail($id);
        $employee_id = $employeeBenefit->employee_id;
        $term = $employeeBenefit->term;
        $employeeBenefit->delete();

        return redirect()->route('payroll.index', ['employee_id' => $employee_id,'term' => $term])
            ->with('success', 'Employee Debt Benefits deleted successfully.');
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0',
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
            'term' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return response()->json(['success' => true]);
    }
}
