<?php

namespace App\Http\Controllers\Hrm;

use Carbon\Carbon;
use App\Models\Hrm\OnceOffCommission;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Hrm\Employee;
use App\Models\Hrm\PaySlip;

class OnceOffCommissionController extends Controller
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
        return view('hrm.once-off-commission.create',compact('employee','term'));
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
            'commission_amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

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
            OnceOffCommission::create([
                'employee_id' => $request->employee_id,
                'commission_amount' => $request->commission_amount,
                'term' => $term,
            ]);


            DB::commit();
            return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $term])
                    ->with('success', 'Once-Off Commission added successfully.');
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
        $commission = OnceOffCommission::findOrFail($id);
         $term = $request->query('term', $commission->term);
        return view('hrm.once-off-commission.edit', compact('commission','term'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $commission = OnceOffCommission::findOrFail($id);

        $request->validate([
            'commission_amount' => 'required|numeric|min:0',
            'term' => 'required|date',
        ]);

        $commission->update($request->all());

        return redirect()->route('payroll.index', ['employee_id' => $commission->employee_id])
                    ->with('success', 'Once-Off Commission added successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $commission = OnceOffCommission::findOrFail($id);
        $employee_id = $commission->employee_id;
        $commission->delete();

        return redirect()->route('payroll.index', ['employee_id' => $employee_id])->with('success', 'Once-Off Commission deleted successfully.');
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id'       => 'required|exists:employees,id',
            'commission_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }

    public function ajaxValidateUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'commission_amount' => 'required|numeric|min:0',
            'term'              => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }
}
