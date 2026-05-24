<?php

namespace App\Http\Controllers\Hrm;

use Carbon\Carbon;
use App\Models\Hrm\AnnualPayment;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Hrm\Employee;
use App\Models\Hrm\PaySlip;

class AnnualPaymentController extends Controller
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
        return view('hrm.annual-payments.create',compact('employee','term'));
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
            'annual_amount' => 'required|numeric|min:0',
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
            AnnualPayment::create([
                'employee_id' => $request->employee_id,
                'annual_amount' => $request->annual_amount,
                'term' => $term,
            ]);

            // $this->calculatePayroll($request->employee_id);

            DB::commit();
            return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $term])->with('success', 'Annual Payment added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
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
        $annualPayment = AnnualPayment::findOrFail($id);
        $term = $request->query('term', $annualPayment->term);
        return view('hrm.annual-payments.edit', compact('annualPayment', 'term'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $annualPayment = AnnualPayment::findOrFail($id);
        $request->validate([
            'annual_amount' => 'required|numeric|min:0',
            'term'          => 'required|date',
        ]);

        $annualPayment->update([
            'annual_amount' => $request->annual_amount,
            'term'          => $request->term,
        ]);

        return redirect()->route('payroll.index', ['employee_id' => $annualPayment->employee_id,'term' => $annualPayment->term])
            ->with('success', 'Annual Payment updated successfully.');

    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $annualPayment = AnnualPayment::findOrFail($id);
        $employee_id = $annualPayment->employee_id;
        $term = $annualPayment->term;
        $annualPayment->delete();

        return redirect()->route('payroll.index', ['employee_id' => $employee_id,'term' =>$term])->with('success', 'Annual Payment deleted successfully.');
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'annual_amount' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return response()->json(['success' => true]);
    }

    public function ajaxValidateUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'annual_amount' => 'required|numeric|min:0',
            'term' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return response()->json(['success' => true]);
    }
}
