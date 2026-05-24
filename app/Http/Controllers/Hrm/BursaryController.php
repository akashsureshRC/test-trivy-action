<?php

namespace App\Http\Controllers\Hrm;

use Carbon\Carbon;
use App\Models\Hrm\Bursary;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Hrm\Employee;
use App\Models\Hrm\PaySlip;

class BursaryController extends Controller
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
        return view('hrm.bursaries.create',compact('employee','term'));
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
            'taxable_portion' => 'required|numeric|min:0',
            'exempt_portion' => 'required|numeric|min:0',
            'type' => 'required|string',
        ], [
            'taxable_portion.required' => 'Taxable Portion is required.',
            'exempt_portion.required' => 'Exempt Portion is required.',
            'type.required' => 'Type is required.',
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
            Bursary::create([
                'employee_id' => $request->employee_id,
                'term' => $term,
                'taxable_portion' => $request->taxable_portion,
                'exempt_portion' => $request->exempt_portion,
                'type' => $request->type,
                'employee_handles_payment' => $request->employee_handles_payment ?? 0
            ]);

            DB::commit();
            return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $term])
                    ->with('success', 'Bursaries And Scholarships added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('payroll.index', ['employee_id' => $request->employee_id, 'term' => $term])->with('error', 'Error: ' . $e->getMessage())->withInput();
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
        $bursary =  Bursary::findOrFail($id);
         $term = $request->query('term', $bursary->term);
        return view('hrm.bursaries.edit', compact('bursary','term'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $bursary =  Bursary::findOrFail($id);
        $request->validate([
            'taxable_portion' => 'required|numeric|min:0',
            'exempt_portion' => 'required|numeric|min:0',
            'type' => 'required|string',
            'term'               => 'required|date',
        ]);

        $bursary ->update($request->all());
        return redirect()->route('payroll.index', ['employee_id' => $bursary->employee_id,'term' => $bursary->term])
                    ->with('success', 'Bursaries And Scholarships updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $bursary = Bursary::findOrFail($id);
        $employee_id = $bursary->employee_id;
        $term = $bursary->term;
        $bursary->delete();

        return redirect()->route('payroll.index', ['employee_id' => $employee_id,'term'=>$term])
            ->with('success', 'Bursary deleted successfully.');
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id'      => 'required|exists:employees,id',
            'taxable_portion'  => 'required|numeric|min:0',
            'exempt_portion'   => 'required|numeric|min:0',
            'type'             => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }

    public function ajaxValidateUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'taxable_portion' => 'required|numeric|min:0',
            'exempt_portion'  => 'required|numeric|min:0',
            'type'            => 'required|string',
            'term'            => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }
}
