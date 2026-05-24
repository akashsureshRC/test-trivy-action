<?php

namespace App\Http\Controllers\Hrm;

use App\Models\Hrm\TaxDirective;
use App\Models\Hrm\Payroll;
use App\Models\Hrm\Employee;
use App\Models\Hrm\TaxDirectiveEntry;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TaxDirectiveEntryController extends Controller
{
    protected function findWorkspaceEmployeeOrFail(int $employeeId): Employee
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            abort(403, 'Unauthorized employee access.');
        }

        return $employee;
    }

    protected function findWorkspaceTaxDirectiveEntryOrFail(int $id): TaxDirectiveEntry
    {
        return TaxDirectiveEntry::whereHas('employee', function ($query) {
            $query->where('id', '>', 0);
        })->findOrFail($id);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $entries = TaxDirectiveEntry::whereHas('employee', function ($query) {
            $query->where('id', '>', 0);
        })->with('taxDirective')->paginate(10);
        return view('hrm.tax-directive-entries.index', compact('entries'));
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

        $employee = $this->findWorkspaceEmployeeOrFail((int) $employeeId);
        $taxDirectives = TaxDirective::all();
        return view('hrm.tax-directive-entries.create', compact('taxDirectives', 'employee', 'term'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id'                   => 'required|exists:employees,id',
            'directive_number'              => 'required|string|max:255',
            'tax_directive_id'              => 'required|string',
            'directive_income_source_code'  => 'nullable|string|max:255',
            'directive_income_amount'       => 'nullable|numeric|min:0',
            'amount_of_tax_to_deduct'       => 'nullable|numeric|min:0',
            'directive_issue_date'          => 'nullable|date',
            'percentage'                    => 'nullable|numeric|min:0|max:100',
        ]);

        /*if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }*/
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $this->findWorkspaceEmployeeOrFail((int) $request->input('employee_id'));

        try {

            DB::beginTransaction();


            $taxDirectiveEntry = TaxDirectiveEntry::create($request->only([
                'employee_id',
                'directive_number',
                'tax_directive_id',
                'directive_income_source_code',
                'directive_income_amount',
                'amount_of_tax_to_deduct',
                'directive_issue_date',
                'percentage',
                'term'
            ]));


            Payroll::updateOrCreate(
                [
                    'employee_id' => $request->input('employee_id')
                ],
                [
                    'directive_number' => $request->input('directive_number'),
                    'tax_directive_id' => $request->input('tax_directive_id'),
                    'directive_income_source_code' => $request->input('directive_income_source_code'),
                    'directive_income_amount' => $request->input('directive_income_amount'),
                    'amount_of_tax_to_deduct' => $request->input('amount_of_tax_to_deduct'),
                    'directive_issue_date' => $request->input('directive_issue_date'),
                    'percentage' => $request->input('percentage'),
                ]
            );


            DB::commit();

            return redirect()
                ->route('payroll.index', ['employee_id' => $request->employee_id, 'term' => $request->term])
                ->with('success', 'Tax Directive Entry Created Successfully.');
        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'message' => 'Error storing records',
                'error' => $e->getMessage(),
            ], 500);
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
    public function edit($id)
    {
        $taxDirectiveEntry = $this->findWorkspaceTaxDirectiveEntryOrFail((int) $id);
        $taxDirectives = TaxDirective::all();
        $employee = $this->findWorkspaceEmployeeOrFail((int) $taxDirectiveEntry->employee_id);
        $term = request()->get('term');
        return view('hrm.tax-directive-entries.edit', compact('taxDirectiveEntry', 'taxDirectives', 'term'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'employee_id'                   => 'required|exists:employees,id',
            'directive_number'              => 'required|string|max:255',
            'tax_directive_id'              => 'required|string',
            'directive_income_source_code'  => 'nullable|string|max:255',
            'directive_income_amount'       => 'nullable|numeric|min:0',
            'amount_of_tax_to_deduct'       => 'nullable|numeric|min:0',
            'directive_issue_date'          => 'nullable|date',
            'percentage'                    => 'nullable|numeric|min:0|max:100',
            'term'                          => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $this->findWorkspaceEmployeeOrFail((int) $request->input('employee_id'));

            DB::beginTransaction();


            $taxDirectiveEntry = $this->findWorkspaceTaxDirectiveEntryOrFail((int) $id);
            $taxDirectiveEntry->update($request->only([
                'employee_id',
                'directive_number',
                'tax_directive_id',
                'directive_income_source_code',
                'directive_income_amount',
                'amount_of_tax_to_deduct',
                'directive_issue_date',
                'percentage',
            ]));


            $payroll = Payroll::where('employee_id', $request->employee_id)->first();

            if ($payroll) {

                $payroll->update($request->only([
                    'directive_number',
                    'tax_directive_id',
                    'directive_income_source_code',
                    'directive_income_amount',
                    'amount_of_tax_to_deduct',
                    'directive_issue_date',
                    'percentage',
                ]));
            } else {

                Payroll::create($request->only([
                    'employee_id',
                    'directive_number',
                    'tax_directive_id',
                    'directive_income_source_code',
                    'directive_income_amount',
                    'amount_of_tax_to_deduct',
                    'directive_issue_date',
                    'percentage',
                ]));
            }


            DB::commit();

            return redirect()
                ->route('payroll.index', ['employee_id' => $taxDirectiveEntry->employee_id, 'term' => $request->term,])
                ->with('success', 'Tax Directive Entry Updated Successfully');
        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'message' => 'Error updating records',
                'error'   => 'Something went wrong. Please try again later.',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id, $term)
    {
        $taxDirectiveEntry = $this->findWorkspaceTaxDirectiveEntryOrFail((int) $id);
        $taxDirectiveEntry->delete();
        $employee_id = $taxDirectiveEntry->employee_id;
        return redirect()->route('payroll.index', ['employee_id' => $employee_id, 'term' => $term])->with('success', 'Tax Directive Entry Deleted Successfully.');
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'directive_number' => 'required|string|max:255',
            'tax_directive_id' => 'required|string',
            'directive_income_source_code' => 'nullable|string|max:255',
            'directive_income_amount' => 'nullable|numeric|min:0',
            'amount_of_tax_to_deduct' => 'nullable|numeric|min:0',
            'directive_issue_date' => 'nullable|date',
            'percentage' => 'nullable|numeric|min:0|max:100',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return response()->json(['success' => true]);
    }

    public function ajaxValidateUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'directive_number' => 'required|string|max:255',
            'tax_directive_id' => 'required|string',
            'directive_income_source_code' => 'nullable|string|max:255',
            'directive_income_amount' => 'nullable|numeric|min:0',
            'amount_of_tax_to_deduct' => 'nullable|numeric|min:0',
            'directive_issue_date' => 'nullable|date',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'term' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return response()->json(['success' => true]);
    }
}
