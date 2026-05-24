<?php

namespace App\Http\Controllers\Hrm;

use Carbon\Carbon;
use App\Models\Hrm\AnnualBonus;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Hrm\BasicSalary;
use App\Models\Hrm\Employee;
use App\Models\Hrm\IncomePolicy;
use App\Models\Hrm\Payroll;
use App\Models\Hrm\PaySlip;

class AnnualBonusController extends Controller
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

        return view('hrm.annual-bonuses.create',compact('employee','term'));
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
            'bonus_amount' => 'required|numeric|min:0',
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

            AnnualBonus::create([
                'employee_id' => $request->employee_id,
                'bonus_amount' => $request->bonus_amount,
                'term' => $term,
            ]);

            // $this->calculatePayroll($request->employee_id);

            DB::commit();
            return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $term])->with('success', 'Annual Bonus added successfully.');
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

        $annualBonus = AnnualBonus::findOrFail($id);
        $term = $request->query('term', $annualBonus->term);
        return view('hrm.annual-bonuses.edit', compact('annualBonus','term'));
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

            'bonus_amount' => 'required|numeric|min:0',
            'term'         => 'required|date',

        ]);

        // Find the existing bonus
        $annualBonus = AnnualBonus::findOrFail($id);
        $annualBonus->update([

            'bonus_amount' => $request->bonus_amount,
            'term'         => $request->term,

        ]);

        return redirect()->route('payroll.index', ['employee_id' => $annualBonus->employee_id,'term' => $annualBonus->term])
            ->with('success', 'Annual Bonus updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $annualBonus = AnnualBonus::findOrFail($id);
        $employee_id = $annualBonus->employee_id;
        $term = $annualBonus->term;
        $annualBonus->delete();

        return redirect()->route('payroll.index', ['employee_id' => $employee_id,'term' => $term])->with('success', 'Annual Bonus deleted successfully.');
    }
    private function calculatePayroll($employeeId)
    {
        try {

            $basicSalary = BasicSalary::where('employee_id', $employeeId)->value('fixed_salary') ?? 0;
            $incomePolicyTotal = IncomePolicy::where('employee_id', $employeeId)->sum('payout_amount') ?? 0;


            $uif = ($basicSalary + $incomePolicyTotal) * 0.01;
            $payTax = ($basicSalary + $incomePolicyTotal) * 0.15;
            $netPay = ($basicSalary + $incomePolicyTotal) - ($uif + $payTax);


            Log::info('Calculated Payroll:', [
                'employeeId' => $employeeId,
                'basicSalary' => $basicSalary,
                'incomePolicyTotal' => $incomePolicyTotal,
                'uif' => $uif,
                'payTax' => $payTax,
                'netPay' => $netPay,
            ]);


            Payroll::updateOrCreate(
                ['employee_id' => $employeeId],
                [
                    'basic_salary' => $basicSalary,
                    'income_policy' => $incomePolicyTotal,
                    'uif_amount' => $uif,
                    'tax_pay' => $payTax,
                    'net_pay' => $netPay
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error calculating payroll for employee ' . $employeeId, [
                'error' => $e->getMessage(),
            ]);

        }
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'bonus_amount' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return response()->json(['success' => true]);
    }

    public function ajaxValidateUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'bonus_amount' => 'required|numeric|min:0',
            'term' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return response()->json(['success' => true]);
    }
}
