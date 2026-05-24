<?php

namespace App\Http\Controllers\Hrm;

use Illuminate\Http\Request;
use App\Models\Hrm\IncomePolicy;
use App\Models\Hrm\Payroll;
use App\Models\Hrm\BasicSalary;
use App\Models\Hrm\Employee;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class IncomePolicyController extends Controller
{
    protected function findWorkspaceEmployeeOrFail(int $employeeId): Employee
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            abort(403, 'Unauthorized employee access.');
        }

        return $employee;
    }

    protected function findWorkspaceIncomePolicyOrFail(int $id): IncomePolicy
    {
        return IncomePolicy::whereHas('employee', function ($query) {
            $query->where('id', '>', 0);
        })->findOrFail($id);
    }

    /**
     * Display payroll calculations.
     */
    public function index()
    {
        $employeeId = request()->employee_id;

        if (!$employeeId) {
            return redirect()->route('payroll.index')->with('error', 'Employee ID is required.');
        }

        $this->findWorkspaceEmployeeOrFail((int) $employeeId);

        $payroll = Payroll::where('employee_id', $employeeId)->first();
        $incomePolicy = IncomePolicy::all();
        $basicSalary = BasicSalary::where('employee_id', $employeeId)->with('incomePolicies')->first();

        if (!$payroll || !$basicSalary) {
            return redirect()->route('payroll.index')->with('error', 'Payroll details not found for the employee.');
        }


        $incomePolicyTotal = $basicSalary->incomePolicies->sum('payout_amount') ?? 0;

        return view('hrm.payroll.index', compact(
            'payroll',
            'basicSalary',
            'incomePolicyTotal'
        ));
    }

    /**
     * Show the form to create an income policy.
     */
    public function create(Request $request)
    {
        $employeeId = $request->employee_id;
        $term = $request->term;
        if (!$employeeId) {
            return redirect()->route('payroll.index')->with('error', 'Employee ID is required.');
        }

        $employee = $this->findWorkspaceEmployeeOrFail((int) $employeeId);

        return view('hrm.income-policies.create', compact('employee', 'term'));
    }

    /**
     * Store a newly created income policy.
     */

    public function store(Request $request)
    {

        Log::info('Store function called', ['request' => $request->all()]);

        // Validate request
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'payout_amount' => 'required|numeric|min:0',
            'term' => 'required|date',
        ]);

        $employee_id = $validated['employee_id'];
        $term = $validated['term']; // Get the current term from the request

        $this->findWorkspaceEmployeeOrFail((int) $employee_id);

        DB::beginTransaction();
        try {
            // Insert income policy
            $inserted = DB::table('income_policies')->insert([
                'employee_id' => $validated['employee_id'],
                'payout_amount' => $validated['payout_amount'],
                'term' => $term,
            ]);

            if ($inserted) {
                Log::info('Income Policy inserted successfully into DB');

                Payroll::firstOrCreate(['employee_id' => $employee_id]);

                Payroll::updateOrCreate(
                    ['employee_id' => $employee_id],
                    ['loss_of_income_policy_payout' => $validated['payout_amount']]
                );

                Log::info('Payroll table updated with income policy payout.');

                DB::commit();


                return redirect()->route('payroll.index', [
                    'employee_id' => $employee_id,
                    'term' => $term
                ])->with('success', 'Loss Of Income Policy Saved Successfully!');
            } else {
                Log::error('Income Policy insert failed');
                throw new \Exception('Failed to insert Income Policy.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Database insert error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Database insert error: ' . $e->getMessage());
        }
    }

    /**
     * Calculate payroll for an employee.
     */

    public function calculatePayroll($employeeId)
    {
        $employee = $this->findWorkspaceEmployeeOrFail((int) $employeeId);


        $basicSalaryData = BasicSalary::where('employee_id', $employeeId)->first();
        $basicSalary = $basicSalaryData ? $basicSalaryData->amount : 0;


        $incomePolicyPayout = IncomePolicy::where('employee_id', $employeeId)->sum('payout_amount');


        $uif = $basicSalary * 0.01;


        $payTax = $basicSalary > 5000 ? $basicSalary * 0.10 : 0;


        $totalIncome = $basicSalary + $incomePolicyPayout;


        $totalDeductions = $uif + $payTax;


        $netPay = $totalIncome - $totalDeductions;


        Log::info("Payroll Calculation for Employee ID: {$employeeId}", [
            'basicSalary' => $basicSalary,
            'incomePolicyPayout' => $incomePolicyPayout,
            'uif' => $uif,
            'payTax' => $payTax,
            'totalIncome' => $totalIncome,
            'totalDeductions' => $totalDeductions,
            'netPay' => $netPay
        ]);


        Payroll::updateOrCreate(
            ['employee_id' => $employeeId],
            [
                'basic_salary' => $basicSalary,
                'loss_of_income_policy_payout' => $incomePolicyPayout,
                'uif_amount' => $uif,
                'tax_pay' => $payTax,
                'total_income' => $totalIncome,
                'deductions' => $totalDeductions,
                'net_pay' => $netPay,
            ]
        );

        return redirect()->route('payroll.index')->with('success', 'Payroll calculated successfully.');
    }





    /**
     * Show income policy edit form.
     */
    public function edit($id, Request $request)
    {
        $term = $request->query('term');
        $incomePolicy = IncomePolicy::with('employee')->whereHas('employee', function ($query) {
            $query->where('id', '>', 0);
        })->find($id);

        if (!$incomePolicy) {
            return redirect()->route('income-policies.index')->with('error', 'Record not found.');
        }

        return view('hrm.income-policies.edit', compact('incomePolicy', 'term'));
    }

    /**
     * Update an income policy.
     */
    public function update(Request $request, $id)
    {

        $employeeId = $request->input('employee_id');
        $term = $request->input('term');

        $incomePolicy = $this->findWorkspaceIncomePolicyOrFail((int) $id);


        $validated = $request->validate([
            'payout_amount' => 'required|numeric|min:0',
        ]);


        DB::beginTransaction();
        try {

            $incomePolicy->update(['payout_amount' => $validated['payout_amount']]);


            $this->calculatePayroll($incomePolicy->employee_id);


            DB::commit();


            return redirect()->route('payroll.index', ['employee_id' => $employeeId, 'term' => $term])
                ->with('success', 'Loss OF Income Policy updated successfully.');
        } catch (\Exception $e) {

            DB::rollBack();


            return redirect()->route('payroll.index', ['employee_id' => $incomePolicy->employee_id, 'term' => $term,])
                ->with('success', 'Loss OF Income Policy updated successfully!');
        }
    }





    public function showRegularInputs($employeeId)
    {

        $employee = $this->findWorkspaceEmployeeOrFail((int) $employeeId);

        $basicSalary = $employee->basic_salary;
        $incomePolicy = $employee->income_policy;


        $uif = $basicSalary * 0.01;


        $payTax = $basicSalary * 0.10;


        $netPay = $basicSalary - $uif - $payTax;


        return view('payroll.regularinputs', compact('employee', 'basicSalary', 'incomePolicy', 'uif', 'payTax', 'netPay'));
    }
    /**
     * Delete an income policy and update payroll.
     */
    public function destroy($id, $term)
    {
        $income_policy = $this->findWorkspaceIncomePolicyOrFail((int) $id);
        $employee_id = $income_policy->employee_id;
        $income_policy->delete();
        return redirect()->route('payroll.index', ['employee_id' => $employee_id, 'term' => $term])->with('success', 'Income Policy Deleted Successfully.');
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id'    => 'required|exists:employees,id',
            'payout_amount'  => 'required|numeric|min:0',
            'term'           => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }

    public function ajaxValidateUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'payout_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }
}
