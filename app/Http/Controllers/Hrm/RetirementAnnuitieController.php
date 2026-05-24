<?php

namespace App\Http\Controllers\Hrm;

use App\Models\Hrm\Employee;
use App\Models\Hrm\Payroll;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Hrm\RetirementAnnuitie;
use App\Models\Hrm\RetirementAnnuityFundPayroll;
use Illuminate\Support\Facades\Validator;

class RetirementAnnuitieController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $retirementannuities = RetirementAnnuityFundPayroll::with('employee')->paginate(10);
        return view('hrm.retirement-annuitie.index', compact('retirementannuities'));
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
        return view('hrm.retirement-annuitie.create', compact('employee', 'term'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0',
            'portion' => 'nullable|numeric|min:0',
            'employee_payment' => 'nullable|boolean',
            'term' => 'required|date',
        ]);
        //$term = $request->input('term');

        $basic_salary = Payroll::where('employee_id', $validated['employee_id'])->value('basic_salary') ?? 0;

        $term = $validated['term'];
        $amount = $validated['amount'] ?? 0;
        $portion = $validated['portion'] ?? 0;
        $employeePayment = isset($validated['employee_payment']) ? 1 : 0;


        RetirementAnnuityFundPayroll::updateOrCreate(
            ['employee_id' => $validated['employee_id']],
            [
                'amount' => $amount,
                'portion' => $portion,
                'employee_payment' => $employeePayment,
                'term' => $term,

            ]
        );


        RetirementAnnuitie::updateOrCreate(
            ['employee_id' => $validated['employee_id']],
            [
                'amount' => $amount,
                'portion' => $portion,
                'term' => $term,
            ]
        );


        return redirect()->route('payroll.index', ['employee_id' => $request->employee_id, 'term' => $term])
            ->with('success', 'Retirement Annuity Fund Created Successfully.');
    }



    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($employee_id, $term)
    {
        $data = RetirementAnnuitie::where('employee_id', $employee_id)
            ->where('term', $term)
            ->firstOrFail(); // returns 404 if not found

        return view('retirement-annuitie.show', compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $retirementAnnuity = RetirementAnnuityFundPayroll::findOrFail($id);
        $employee = Employee::findOrFail($retirementAnnuity->employee_id);
        $term = request('term');
        return view('hrm.retirement-annuitie.edit', compact('retirementAnnuity', 'employee', 'term'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'Portion' => 'nullable|numeric|min:0',
            'employee_id' => 'required|exists:employees,id',
            'employee_payment' => 'boolean',
        ]);
        $retirementAnnuity = RetirementAnnuityFundPayroll::findOrFail($id);
        $retirementAnnuity->update([
            'amount' => $request->amount,
            'Portion' => $request->Portion,
            'employee_payment' => $request->has('employee_payment') ? 1 : 0,
            'employee_id' => $request->employee_id,
        ]);

        $term = $request->input('term');
        return redirect()->route('payroll.index', ['employee_id' => $validated['employee_id'], 'term' => $term,])
            ->with('success', 'Retirement Annuity Fund Updated Successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id, $term)
    {
        $retirementAnnuity = RetirementAnnuityFundPayroll::findOrFail($id);
        $employee_id =  $retirementAnnuity->employee_id;
        $retirementAnnuity->delete();
        RetirementAnnuitie::where('employee_id', $employee_id)->delete();
        return redirect()->route('payroll.index', ['employee_id' => $employee_id, 'term' => $term])->with('success', 'Retirement Annuity Fund Deleted Successfully!');
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0',
            'portion' => 'nullable|numeric|min:0',
            'employee_payment' => 'nullable|boolean',
            'term' => 'required|date',
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
            'Portion' => 'nullable|numeric|min:0',
            'employee_id' => 'required|exists:employees,id',
            'employee_payment' => 'boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return response()->json(['success' => true]);
    }
}
