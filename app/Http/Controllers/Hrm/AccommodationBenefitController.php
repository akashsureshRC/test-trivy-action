<?php

namespace App\Http\Controllers\Hrm;

use App\Models\Hrm\AccommodationBenefit;
use App\Models\Hrm\Employee;
use App\Models\Hrm\Payroll;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AccommodationBenefitController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    /*public function index()
    {
        $benefits = AccommodationBenefit::all();
        return view('hrm.accommodation-benefits.index', compact('benefits'));
    }*/
    public function index(Request $request)
    {
        $employee_id = $request->employee_id;
        $term = $request->term;

        if (!$employee_id || !$term) {
            return redirect()->back()->with('error', 'Employee and Term are required.');
        }

        $benefits = AccommodationBenefit::where('employee_id', $employee_id)
            ->where('term', $term)
            ->get();

        return view('hrm.accommodation-benefits.index', compact('benefits', 'employee_id', 'term'));
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
        return view('hrm.accommodation-benefits.create', compact('employee', 'term'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //$employee_id = $request->employee_id;
        //$term = $request->term;
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',  
            'term' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);
        $employee_id = $validated['employee_id'];
        $term = $validated['term'];
        DB::beginTransaction();
        try {

            $accommodationBenefits = AccommodationBenefit::updateOrCreate(
                [
                    'employee_id' => $employee_id,
                    'term' => $term,
                ],
                ['amount' => $request->amount]
            );


            // $latestAmount = $accommodationBenefits->fresh()->amount;


            // Payroll::updateOrCreate(
            //     ['employee_id' => $employee_id],
            //     ['accommodation_benefits' => $latestAmount] //
            // );

            DB::commit();
            return redirect()->route('payroll.index', ['employee_id' => $employee_id, 'term' => $request->term])
                ->with('success', 'Accommodation Benefit Saved Successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to add Accommodation Benefit: ' . $e->getMessage());
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
    public function edit($id, Request $request)
    {
        $term = $request->query('term');

        $accommodation_benefit = AccommodationBenefit::findOrFail($id);

        $employee = Employee::find($accommodation_benefit->employee_id);

        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found.');
        }

        return view('hrm.accommodation-benefits.edit', compact('accommodation_benefit', 'employee', 'term'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, AccommodationBenefit $accommodationBenefit)
    {
        $term = $request->input('term');
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ], [
            'amount.required' => 'The amount field is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The amount must be at least 0.',
        ]);
        $employeeId =  $accommodationBenefit->employee_id;

        $accommodationBenefit->update([
            'amount' => $request->amount,
        ]);

        return redirect()->route('payroll.index', ['employee_id' => $employeeId, 'term' => $term,])
            ->with('success', 'Accommodation Benefit Updated Successfully!');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id, $term)
    {
        $accommodation_benefit = AccommodationBenefit::findOrFail($id);
        $employeeId = $accommodation_benefit->employee_id;
        $accommodation_benefit->delete();

        return redirect()->route('payroll.index', ['employee_id' => $employeeId, 'term' => $term])
            ->with('success', 'Accommodation Benefit Deleted Successfully.');
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'term'        => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0',
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
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }
}
