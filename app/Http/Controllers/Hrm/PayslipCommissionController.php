<?php

namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\PayslipCommission;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Hrm\Employee;

class PayslipCommissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $commissions = PayslipCommission::all();
        return view('hrm.payslip-commissions.index', compact('commissions'));
    }

    /**
     * Show the form for creating a new resource.
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
        return view('hrm.payslip-commissions.create',compact('employee','term'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            // 'name_payslip' => 'required|unique:payslip_commissions,name_payslip',
            'employee_id' => 'required|exists:employees,id',
            'commission_amount' => 'required|numeric|min:0',
            'commission_type' => ['required', Rule::in(['percentage', 'flat'])],
            'status' => 'required|in:Active,Inactive',
        ], [
            // 'name_payslip.required' => 'The payslip name is required.',
            // 'name_payslip.unique' => 'This payslip name already exists.',
            'commission_amount.required' => 'The commission amount is required.',
            'commission_amount.numeric' => 'The commission amount must be a number.',
            'commission_type.required' => 'The commission type is required.',
            'commission_type.in' => 'The commission type must be either percentage or flat.',
            'status.required' => 'The status is required.',
            'status.in' => 'The status must be either Active or Inactive.',
        ]);

        PayslipCommission::create($request->all());

        return redirect()->route('payroll.index', ['employee_id' => $request->employee_id, 'term' => $request->term])
            ->with('success', 'Payslip commission added successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(PayslipCommission $payslipCommission)
    {
        return view('payslip-commissions.show', compact('payslipCommission'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $payslipCommission = PayslipCommission::findOrFail($id);
        $term = request('term');
        return view('hrm.payslip-commissions.edit', compact('payslipCommission','term'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $payslipCommission = PayslipCommission::findOrFail($id);
        $request->validate([
            // 'name_payslip' => 'required|unique:payslip_commissions,name_payslip,' . $payslipCommission->id,
            'commission_amount' => 'required|numeric|min:0',
            'commission_type' => ['required', Rule::in(['percentage', 'flat'])],
            'status' => 'required|in:Active,Inactive',
        ]);

        $payslipCommission->update($request->all());

        return redirect()->route('payroll.index', ['employee_id' => $payslipCommission->employee_id ,'term' => $request->term])
                         ->with('success', 'Payslip commission updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id,$term)
    {
        $payslipCommission = PayslipCommission::findOrFail($id);
        $employee_id = $payslipCommission->employee_id;
        $payslipCommission->delete();

        return redirect()->route('payroll.index', ['employee_id' => $employee_id, 'term' => $term ])
                         ->with('success', 'Payslip commission deleted successfully!');
    }


    public function updateStatus(Request $request, PayslipCommission $payslipCommission)
    {
        $request->validate([
            'status' => 'required|in:Active,Inactive',
        ]);

        // Update the status field
        $payslipCommission->status = $request->status;
        $payslipCommission->save();

        // Return a JSON response for the AJAX call
        return response()->json(['status' => strtolower($payslipCommission->status)]);
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id'       => 'required|exists:employees,id',
            'commission_amount' => 'required|numeric|min:0',
            'commission_type'   => ['required', Rule::in(['percentage', 'flat'])],
            'status'            => 'required|in:Active,Inactive',
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
            'commission_type'   => ['required', Rule::in(['percentage', 'flat'])],
            'status'            => 'required|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }
}
