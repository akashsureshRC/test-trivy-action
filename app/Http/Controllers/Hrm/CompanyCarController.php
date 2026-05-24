<?php

namespace App\Http\Controllers\Hrm;

use App\Models\Hrm\CompanyCar;
use App\Models\Hrm\Employee;
use App\Models\Hrm\CompanyCarTaxableType;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Hrm\Payroll;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CompanyCarController extends Controller
{
    protected function findWorkspaceEmployeeOrFail(int $employeeId): Employee
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            abort(403, 'Unauthorized employee access.');
        }

        return $employee;
    }

    protected function findWorkspaceCompanyCarOrFail(int $id): CompanyCar
    {
        return CompanyCar::whereHas('employee', function ($query) {
            $query->where('id', '>', 0);
        })->findOrFail($id);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
{
    $employeeId = $request->input('employee_id');

    if (!$employeeId) {
        return redirect()->back()->with('error', 'Employee ID is missing.');
    }

    $employee = $this->findWorkspaceEmployeeOrFail((int) $employeeId);

    $companyCar = CompanyCar::where('employee_id', $employeeId)->first();
    $deemedValue = floatval($companyCar->deemed_value ?? 0);
    $includesMaintenancePlan = $companyCar->includes_maintenance_plan ?? 0;
    $taxablePercentageId = $companyCar->taxable_percentage_id ?? 0;

    $taxablePercentageValue = floatval(DB::table('company_car_taxable_types')
        ->where('id', $taxablePercentageId)
        ->value('percentage') ?? 0);

    $taxableAmount = $deemedValue * ($taxablePercentageValue / 100);
    $totalAmount = $deemedValue + $taxableAmount;

    $payroll = Payroll::firstOrNew(['employee_id' => $employeeId]);
    $payroll->fill([
        'company_car_deemed_value' => $deemedValue,
        'company_car_taxable_percentage' => $taxablePercentageValue,
        'includes_maintenance_plan' => $includesMaintenancePlan,
    ])->save();

    return view('payroll.index', compact(
        'employee', 'payroll', 'deemedValue', 'taxablePercentageValue', 'totalAmount', 'includesMaintenancePlan'
    ));
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

        $taxableTypes = CompanyCarTaxableType::all();

        return view('hrm.company-cars.create', compact('taxableTypes', 'employee','term'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        Log::info('Form data:', $request->all());

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'deemed_value' => 'required|numeric|min:0',
            'taxable_percentage_id' => 'required|exists:company_car_taxable_types,id',
            'includes_maintenance_plan' => 'nullable|boolean',
            'term' => 'required|date',
        ]);

        $employeeId = $request->input('employee_id');
        $term = $request->term;
        $deemedValue = floatval($request->input('deemed_value', 0));
        $taxablePercentageId = intval($request->input('taxable_percentage_id', 0));
        $includesMaintenancePlan = $request->boolean('includes_maintenance_plan');

        $this->findWorkspaceEmployeeOrFail((int) $employeeId);


        $taxablePercentageValue = floatval(DB::table('company_car_taxable_types')
            ->where('id', $taxablePercentageId)
            ->value('percentage') ?? 0);

        $finalTaxablePercentage = $includesMaintenancePlan ? 3.25 : $taxablePercentageValue;


        $taxableAmount = floatval($deemedValue) * (floatval($finalTaxablePercentage) / 100);
        $totalAmount = floatval($deemedValue) + floatval($taxableAmount);

        Log::info("Storing Company Car Details: Employee ID - {$employeeId}, Deemed Value - {$deemedValue}, Taxable Percentage - {$finalTaxablePercentage}");
        Log::info("Taxable Amount: {$taxableAmount}, Total Amount: {$totalAmount}");

        DB::beginTransaction();
        try {

            CompanyCar::create([
                'employee_id' => $employeeId,
                'deemed_value' => $deemedValue,
                'taxable_percentage_id' => $taxablePercentageId,
                'taxable_percentage' => $finalTaxablePercentage,
                'includes_maintenance_plan' => $includesMaintenancePlan,
                'term' => $term,
            ]);


            Payroll::updateOrCreate(
                ['employee_id' => $employeeId],
                [
                    'deemed_value' => $deemedValue,
                    'taxable_percentage_id' => $taxablePercentageId,
                    'taxable_percentage' => $finalTaxablePercentage,
                    'includes_maintenance_plan' => $includesMaintenancePlan,
                    'company_car_taxable_amount' => $taxableAmount,
                    'company_car_total_amount' => $totalAmount,
                ]
            );

            Log::info("Payroll Record Saved: Employee ID - {$employeeId}, Taxable Amount - {$taxableAmount}");

            DB::commit();
            return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $request->term])
                ->with('success', 'Company Car  Stored Successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error storing Company Car details: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }


    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $employee = $this->findWorkspaceEmployeeOrFail((int) $id);

        $companyCarData = CompanyCar::where('employee_id', $id)->first();
        $deemed_value = $companyCarData ? $companyCarData->deemed_value : 0;
        $includes_maintenance_plan = $companyCarData ? $companyCarData->includes_maintenance_plan : false;
        $taxable_percentage_Id = $companyCarData ? $companyCarData->taxable_percentage_id : '3.5';

        if ($includes_maintenance_plan) {
            $taxable_percentage_id = '3.25';
        }

        $taxable_value = ($deemed_value * ($taxable_percentage_id / 100));

        return view('hrm.show', compact(
            'employee',
            'deemed_value',
            'includes_maintenance_plan',
            'taxable_percentage_id'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id, Request $request)
    {
        $companyCar = $this->findWorkspaceCompanyCarOrFail((int) $id);
        $employee = $this->findWorkspaceEmployeeOrFail((int) $companyCar->employee_id);

        $taxableTypes = DB::table('company_car_taxable_types')->get();
//$term = $request->query('term');
  $term = $request->query('term', $companyCar->term); 
        return view('hrm.company-cars.edit', compact('companyCar', 'taxableTypes','employee','term'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
{
    //dd($request->all());
        $companyCar = $this->findWorkspaceCompanyCarOrFail((int) $id);
    //$term = $request->input('term');
    $request->validate([
        'deemed_value' => 'required|numeric|min:0',
        'includes_maintenance_plan' => 'sometimes|boolean',
        'taxable_percentage_id' => 'required|exists:company_car_taxable_types,id',
         'term' => 'required|date',
    ]);

    $deemedValue = floatval($request->deemed_value);
    $includesMaintenancePlan = $request->boolean('includes_maintenance_plan');
    $term = $request->input('term');

    $this->findWorkspaceEmployeeOrFail((int) $companyCar->employee_id);

    $taxablePercentageValue = floatval(DB::table('company_car_taxable_types')
        ->where('id', $request->taxable_percentage_id)
        ->value('percentage') ?? 0);

    $finalTaxablePercentage = $includesMaintenancePlan ? 3.25 : $taxablePercentageValue;


    $taxableAmount = floatval($deemedValue) * (floatval($finalTaxablePercentage) / 100);
    $totalAmount = floatval($deemedValue) + floatval($taxableAmount);

    $companyCar->update([
        'deemed_value' => $deemedValue,
        'includes_maintenance_plan' => $includesMaintenancePlan,
        'taxable_percentage_id' => $request->taxable_percentage_id,
        'taxable_percentage' => $finalTaxablePercentage,
        'term' => $term,
    ]);

    $payroll = Payroll::where('employee_id', $companyCar->employee_id)->first();
    if ($payroll) {
        $payroll->update([
            'deemed_value' => $deemedValue,
            'includes_maintenance_plan' => $includesMaintenancePlan,
            'taxable_percentage_id' => $finalTaxablePercentage,
            'company_car_taxable_amount' => $taxableAmount,
            'company_car_total_amount' => $totalAmount,
        ]);

        return redirect()->route('payroll.index', ['employee_id' => $companyCar->employee_id,'term' => $term,])
            ->with('success', 'Company Car Updated Successfully!');
    } else {
        return redirect()->route('payroll.index')->with('error', 'Payroll record not found for this employee.');
    }
}
public function updatePayroll(Request $request, $id)
{
    $payroll = Payroll::find($id);

    if (!$payroll) {
        return response()->json(['error' => 'Payroll record not found'], 404);
    }

    $deemedValue = floatval($request->input('deemed_value', 0));
    $finalTaxablePercentage = floatval($request->input('taxable_percentage', 0));

    $taxableAmount = floatval($deemedValue) * (floatval($finalTaxablePercentage) / 100);
    $totalAmount = floatval($deemedValue) + floatval($taxableAmount);

    $payroll->update([
        'company_car_taxable_amount' => $taxableAmount,
        'company_car_total_amount' => $totalAmount
    ]);

    return response()->json(['message' => 'Company Car updated successfully'], 200);
}
    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id,$term)
    {
        $companyCar = $this->findWorkspaceCompanyCarOrFail((int) $id);
        $employee_id =  $companyCar->employee_id;
        $companyCar->delete();
        return redirect()->route('payroll.index', ['employee_id' => $employee_id,'term' => $term])->with('success', 'Company Car Deleted Successfully!');
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id'              => 'required|exists:employees,id',
            'deemed_value'             => 'required|numeric|min:0',
            'taxable_percentage_id'    => 'required|exists:company_car_taxable_types,id',
            'includes_maintenance_plan'=> 'nullable|boolean',
            'term'                     => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }

    public function ajaxValidateUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'deemed_value'             => 'required|numeric|min:0',
            'includes_maintenance_plan'=> 'sometimes|boolean',
            'taxable_percentage_id'    => 'required|exists:company_car_taxable_types,id',
            'term'                     => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }
}
