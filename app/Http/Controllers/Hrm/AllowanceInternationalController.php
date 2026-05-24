<?php

namespace App\Http\Controllers\Hrm;

use Carbon\Carbon;
use App\Models\Hrm\AllowanceInternational;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Hrm\Employee;
use App\Models\Hrm\PaySlip;

class AllowanceInternationalController extends Controller
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
        return view('hrm.allowance-internationals.create',compact('employee','term'));
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
            'paid_to_employee' => 'required|numeric|min:0',
            'deemed_amount' => 'required|numeric|min:0',
            'number_of_days' => 'required|numeric|min:0',
        ], [
            'paid_to_employee.required' => 'Relocation Allowance - Taxable is required.',
            'deemed_amount.required' => 'Relocation Allowance - Non-Taxable is required.',
            'number_of_days.required' => 'Taxable items paid by employer is required.',
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
            AllowanceInternational::create([
                'employee_id' => $request->employee_id,
                'term' => $term,
                'paid_to_employee' => $request->paid_to_employee,
                'deemed_amount' => $request->deemed_amount,
                'number_of_days' => $request->number_of_days,
            ]);

            DB::commit();
            return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $term])
                    ->with('success', 'Subsistence Allowance International added successfully.');
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
        $allowanceInternational = AllowanceInternational::findOrFail($id);
        $term = $request->query('term', $allowanceInternational->term);
        return view('hrm.allowance-internationals.edit', compact('allowanceInternational','term'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $allowanceInternational = AllowanceInternational::findOrFail($id);

        $request->validate([
            'paid_to_employee' => 'required|numeric|min:0',
            'deemed_amount' => 'required|numeric|min:0',
            'number_of_days' => 'required|numeric|min:0',
            'term'               => 'required|date',
        ]);

        $allowanceInternational->update($request->all());

        return redirect()->route('payroll.index', ['employee_id' => $allowanceInternational->employee_id,'term' => $allowanceInternational->term])
            ->with('success', 'Subsistence Allowance International updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $allowanceInternational = AllowanceInternational::findOrFail($id);
        $employee_id = $allowanceInternational->employee_id;
        $term = $allowanceInternational->term;
        $allowanceInternational->delete();

        return redirect()->route('payroll.index', ['employee_id' => $employee_id,'term' => $term])
            ->with('success', 'Allowance International deleted successfully.');
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'paid_to_employee' => 'required|numeric|min:0',
            'deemed_amount' => 'required|numeric|min:0',
            'number_of_days' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return response()->json(['success' => true]);
    }

    public function ajaxValidateUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'paid_to_employee' => 'required|numeric|min:0',
            'deemed_amount' => 'required|numeric|min:0',
            'number_of_days' => 'required|numeric|min:0',
            'term' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return response()->json(['success' => true]);
    }
}
