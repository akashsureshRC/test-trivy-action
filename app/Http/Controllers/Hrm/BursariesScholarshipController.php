<?php

namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\BursariesScholarship;
use App\Models\Hrm\Payroll;
use App\Models\Hrm\Employee;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class BursariesScholarshipController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $bursaries = BursariesScholarship::paginate(10);
        return view('hrm.bursaries-scholarships.index', compact('bursaries'));
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
            return redirect()->back()->with('error', 'Employee ID is required.');
        }

        $employee = Employee::find($employeeId);

        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found.');
        }
        return view('hrm.bursaries-scholarships.create',compact('employee','term'));
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
            'bursary_type' => 'required|string',
            'employee_handles_payment' => 'required|boolean',
            'to_disabled_person' => 'required|string',
            'term' => 'required|date',
        ]);

        $bursaries = BursariesScholarship::create([
            'employee_id' => $request->employee_id,
            'taxable_portion' => $request->taxable_portion,
            'exempt_portion' => $request->exempt_portion,
            'bursary_type' => $request->bursary_type,
            'employee_handles_payment' => $request->employee_handles_payment,
            'to_disabled_person' => $request->to_disabled_person,
            'term' => $request->term, 
        ]);

        Payroll::updateOrCreate(
            ['employee_id' => $bursaries->employee_id],
            [
                'taxable_portion' => $bursaries->taxable_portion,
                'exempt_portion' => $bursaries->exempt_portion,
                'bursary_type' => $bursaries->bursary_type,
                'employee_handles_payment' => $bursaries->employee_handles_payment,
                'to_disabled_person' => $bursaries->to_disabled_person,
            ]
        );

        return redirect()
            ->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $request->term])
            ->with('success', 'Bursaries Scholarship Added Successfully!');
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
    public function edit($id, Request $request)
    {
        $bursaries_scholarships = BursariesScholarship::find($id);
         $term = $request->query('term');
        return view('hrm.bursaries-scholarships.edit', compact('bursaries_scholarships','term'));
    }
    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $bursaries_scholarships = BursariesScholarship::findOrFail($id);
        $term = $request->input('term');
        $request->validate([
            'taxable_portion' => 'required|numeric|min:0',
            'exempt_portion' => 'required|numeric|min:0',
            'bursary_type' => 'required|string',
            'employee_handles_payment' => 'nullable|string',
            'to_disabled_person' => 'nullable|string',
        ]);

        $bursaries_scholarships->update([
            'taxable_portion' => $request->taxable_portion,
            'exempt_portion' => $request->exempt_portion,
            'bursary_type' => $request->bursary_type,
            'employee_handles_payment' => $request->has('employee_handles_payment') ? '1' : '0',
            'to_disabled_person' => $request->has('to_disabled_person') ? 'Yes' : 'No',
        ]);

        //return redirect()->route('payroll.index', $bursaries_scholarships->id)->with('success', 'Record updated successfully!');
        return redirect()
        ->route('payroll.index', ['employee_id' => $bursaries_scholarships->employee_id,'term' => $term,])
        ->with('success', 'Bursaries Scholarship Updated Successfully!');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id,$term)
    {
        $bursaries_scholarships = BursariesScholarship::findOrFail($id);
        $employee_id = $bursaries_scholarships->employee_id;
        $bursaries_scholarships->delete();
        return redirect()->route('payroll.index', ['employee_id' => $employee_id,'term' => $term])->with('success', 'Bursaries Scholarship Deleted Successfully!');
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'taxable_portion' => 'required|numeric|min:0',
            'exempt_portion' => 'required|numeric|min:0',
            'bursary_type' => 'required|string',
            'employee_handles_payment' => 'required|boolean',
            'to_disabled_person' => 'required|string',
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
            'taxable_portion' => 'required|numeric|min:0',
            'exempt_portion' => 'required|numeric|min:0',
            'bursary_type' => 'required|string',
            'employee_handles_payment' => 'nullable|string',
            'to_disabled_person' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return response()->json(['success' => true]);
    }
}
