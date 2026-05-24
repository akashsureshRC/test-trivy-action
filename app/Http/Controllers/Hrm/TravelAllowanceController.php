<?php

namespace App\Http\Controllers\Hrm;

use App\Models\Hrm\TravelAllowance;
use App\Models\Hrm\Payroll;
use App\Models\Hrm\Employee;
use App\Models\Hrm\BasicSalary;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Models\TaxYear;

class TravelAllowanceController extends Controller
{
    protected function findWorkspaceEmployeeOrFail(int $employeeId): Employee
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            abort(403, 'Unauthorized employee access.');
        }

        return $employee;
    }

    protected function findWorkspaceTravelAllowanceOrFail(int $id): TravelAllowance
    {
        return TravelAllowance::whereHas('employee', function ($query) {
            $query->where('id', '>', 0);
        })->findOrFail($id);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $employee_id = $request->input('employee_id') ?? session('employee_id');


        if (!$employee_id) {
            return redirect()->route('some.error.route')->with('error', 'Employee ID is missing.');
        }


        $employee = $this->findWorkspaceEmployeeOrFail((int) $employee_id);


        $travelAllowances = TravelAllowance::where('employee_id', $employee_id)->get();

        return view('hrm.travel-allowances.create', compact('employee', 'travelAllowances'));
    }


    public function create(Request $request)
    {
        $employeeId = $request->employee_id;
        $term = $request->term;

        if (!$employeeId) {
            return redirect()->back()->with('error', 'Employee ID is required.');
        }

        $employee = $this->findWorkspaceEmployeeOrFail((int) $employeeId);
        return view('hrm.travel-allowances.create', compact('employee', 'term'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id'        => 'required|exists:employees,id',
            'term'               => 'required|string|max:255',
            'fixed_allowance'    => 'boolean',
            'fixed_amount'       => 'nullable|required_if:fixed_allowance,1|numeric|min:0',
            'reimbursed_expenses'=> 'boolean',
            'company_petrol_card'=> 'boolean',
            'reimbursed_per_km'  => 'boolean',
            'rate_per_km'        => 'nullable|required_if:reimbursed_per_km,1|numeric|min:0',
            'subject_to_20_tax'  => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $employee_id = $validated['employee_id'];
            $term = $validated['term'];

            $this->findWorkspaceEmployeeOrFail((int) $employee_id);

            $travelAllowance = TravelAllowance::updateOrCreate(
                [
                    'employee_id' => $employee_id,
                    'term' => $term,
                ],
                [
                    'fixed_allowance'    => $request->input('fixed_allowance', 0),
                    'fixed_amount'       => $request->input('fixed_amount', 0),
                    'reimbursed_expenses'=> $request->input('reimbursed_expenses', 0),
                    'company_petrol_card'=> $request->input('company_petrol_card', 0),
                    'reimbursed_per_km'  => $request->input('reimbursed_per_km', 0),
                    'rate_per_km'        => $request->input('rate_per_km', 0),
                    'subject_to_20_tax'  => $request->input('subject_to_20_tax', 0),
                ]
            );

            Payroll::updateOrCreate(
                ['employee_id' => $employee_id],
                ['travel_allowance' => $travelAllowance->fixed_amount]
            );

            DB::commit();
            return redirect()->route('payroll.index', ['employee_id' => $request->employee_id, 'term' => $request->term])
                ->with('success', 'Travel Allowance saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to add Travel Allowance: ' . $e->getMessage());
        }
    }



    private function updatePayroll($employee_id)
    {

        $employee = Employee::where('employee_id', $employee_id)->first();
        if (!$employee) return;


        $travelAllowance = TravelAllowance::where('employee_id', $employee_id)->first();


        $payroll = Payroll::firstOrNew(['employee_id' => $employee_id]);


        $basic_salary = $employee->basic_salary ?? 0;
        $income_policy = $payroll->income_policy ?? 0;
        $travel_allowance = $travelAllowance->fixed_amount ?? 0;
        $term = $travelAllowance?->term;
        $taxYear = $term ? TaxYear::resolveForTerm($term) : null;
        $uifRate = $taxYear ? $taxYear->uif_rate : 0.01;
        $travelTaxRate = $taxYear ? $taxYear->travel_allowance_tax_rate : 0.10;
        $uif = $basic_salary * $uifRate;
        $pay_tax = ($basic_salary + $travel_allowance) * $travelTaxRate;


        $net_pay = ($basic_salary + $income_policy + $travel_allowance) - ($uif + $pay_tax);


        $payroll->basic_salary = $basic_salary;
        $payroll->income_policy = $income_policy;
        $payroll->travel_allowance = $travel_allowance;
        $payroll->uif = $uif;
        $payroll->pay_tax = $pay_tax;
        $payroll->net_pay = $net_pay;
        $payroll->save();
    }




    /**
     * Display the specified resource.
     */
    public function show(TravelAllowance $travelAllowance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id, Request $request)
    {
        $term = $request->query('term');
        $travelAllowance = $this->findWorkspaceTravelAllowanceOrFail((int) $id);
        $employee = $this->findWorkspaceEmployeeOrFail((int) $travelAllowance->employee_id);

        return view('hrm.travel-allowances.edit', compact('travelAllowance', 'employee', 'term'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $travelAllowance = $this->findWorkspaceTravelAllowanceOrFail((int) $id);
        $term = $request->input('term');
        $request->validate([
            'fixed_allowance' => 'boolean',
            'fixed_amount' => 'required|required_if:fixed_allowance,1|numeric',
            'reimbursed_expenses' => 'boolean',
            'company_petrol_card' => 'boolean',
            'reimbursed_per_km' => 'boolean',
            'rate_per_km' => 'nullable|required_if:reimbursed_per_km,1|numeric',
            'subject_to_20_tax' => 'boolean',
        ]);

        $this->findWorkspaceEmployeeOrFail((int) $travelAllowance->employee_id);

        $travelAllowance->update($request->all());
        $employeeId = $travelAllowance->employee_id;

        return redirect()->route('payroll.index', ['employee_id' => $employeeId, 'term' => $term,])
            ->with('success', 'Travel Allowance Updated Successfully!');
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, $term)
    {
        $travelAllowance = $this->findWorkspaceTravelAllowanceOrFail((int) $id);
        $employee_id = $travelAllowance->employee_id;
        $travelAllowance->delete();
        return redirect()->route('payroll.index', ['employee_id' => $employee_id, 'term' => $term])->with('success', 'Travel Allowance Deleted Successfully.');
    }

    public function changeStatus($id)
    {
        $travelAllowance = $this->findWorkspaceTravelAllowanceOrFail((int) $id);
        $travelAllowance->status = $travelAllowance->status === 'Active' ? 'Inactive' : 'Active';
        $travelAllowance->save();

        return response()->json(['success' => 'Status updated successfully!']);
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'term' => 'required|string|max:255',
            'fixed_allowance' => 'boolean',
            'fixed_amount' => 'nullable|required_if:fixed_allowance,1|numeric|min:0',
            'reimbursed_expenses' => 'boolean',
            'company_petrol_card' => 'boolean',
            'reimbursed_per_km' => 'boolean',
            'rate_per_km' => 'nullable|required_if:reimbursed_per_km,1|numeric|min:0',
            'subject_to_20_tax' => 'boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return response()->json(['success' => true]);
    }

    public function ajaxValidateUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'fixed_allowance' => 'boolean',
            'fixed_amount' => 'required|required_if:fixed_allowance,1|numeric',
            'reimbursed_expenses' => 'boolean',
            'company_petrol_card' => 'boolean',
            'reimbursed_per_km' => 'boolean',
            'rate_per_km' => 'nullable|required_if:reimbursed_per_km,1|numeric',
            'subject_to_20_tax' => 'boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return response()->json(['success' => true]);
    }
}
