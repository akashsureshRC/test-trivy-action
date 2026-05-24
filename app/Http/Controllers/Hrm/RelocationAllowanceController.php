<?php

namespace App\Http\Controllers\Hrm;

use Carbon\Carbon;
use App\Models\Hrm\RelocationAllowance;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Hrm\Employee;
use App\Models\Hrm\PaySlip;

class RelocationAllowanceController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('hrm.relocation-allowances.index');
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
        return view('hrm.relocation-allowances.create',compact('employee','term'));
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
            'taxable_allowance' => 'required|numeric|min:0',
            'non_taxable_allowance' => 'required|numeric|min:0',
            'taxable_items_paid_by_employer' => 'required|numeric|min:0',
        ], [
            'taxable_allowance.required' => 'Relocation Allowance - Taxable is required.',
            'non_taxable_allowance.required' => 'Relocation Allowance - Non-Taxable is required.',
            'taxable_items_paid_by_employer.required' => 'Taxable items paid by employer is required.',
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
            RelocationAllowance::create([
                'employee_id' => $request->employee_id,
                'term' => $term,
                'taxable_allowance' => $request->taxable_allowance,
                'non_taxable_allowance' => $request->non_taxable_allowance,
                'taxable_items_paid_by_employer' => $request->taxable_items_paid_by_employer,
            ]);

            DB::commit();
            return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $term])
                    ->with('success', 'Relocation Allowance added successfully.');
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
        $relocationAllowance = RelocationAllowance::findOrFail($id);
        $term = request('term', $relocationAllowance->term);
        return view('hrm.relocation-allowances.edit', compact('relocationAllowance','term'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $relocationAllowance = RelocationAllowance::findOrFail($id);
        $request->validate([
            'taxable_allowance' => 'required|numeric|min:0',
            'non_taxable_allowance' => 'required|numeric|min:0',
            'taxable_items_paid_by_employer' => 'required|numeric|min:0',
            'term'               => 'required|date',
        ]);

        $relocationAllowance->update($request->all());

        return redirect()->route('payroll.index', ['employee_id' => $relocationAllowance->employee_id,'term' => $relocationAllowance->term])
                    ->with('success', 'Relocation Allowance updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $relocationAllowance =  RelocationAllowance::findOrFail($id);
        $employee_id = $relocationAllowance->employee_id;
        $term = $relocationAllowance->term;
        $relocationAllowance->delete();
        return redirect()->route('payroll.index', ['employee_id' => $employee_id,'term' => $term])
            ->with('success', 'Relocation Allowance deleted successfully.');
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'taxable_allowance' => 'required|numeric|min:0',
            'non_taxable_allowance' => 'required|numeric|min:0',
            'taxable_items_paid_by_employer' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return response()->json(['success' => true]);
    }

    public function ajaxValidateUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'taxable_allowance' => 'required|numeric|min:0',
            'non_taxable_allowance' => 'required|numeric|min:0',
            'taxable_items_paid_by_employer' => 'required|numeric|min:0',
            'term' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return response()->json(['success' => true]);
    }
}
