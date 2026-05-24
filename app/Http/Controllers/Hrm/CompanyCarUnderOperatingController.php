<?php

namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\CompanyCarTaxableType;
use App\Models\Hrm\Employee;
use App\Models\Hrm\CompanyCarUnderOperating;
use App\Models\Hrm\Payroll;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CompanyCarUnderOperatingController extends Controller
{
    protected function findWorkspaceEmployeeOrFail(int $employeeId): Employee
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            abort(403, 'Unauthorized employee access.');
        }

        return $employee;
    }

    protected function findWorkspaceCompanyCarOrFail(int $id): CompanyCarUnderOperating
    {
        return CompanyCarUnderOperating::whereHas('employee', function ($query) {
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


    $companyCarUnderOperating = CompanyCarUnderOperating::where('employee_id', $employeeId)->first();

    $amount = $companyCarUnderOperating ? $companyCarUnderOperating->amount : 0;
    $taxablePercentage = $companyCarUnderOperating ? $companyCarUnderOperating->taxable_percentage : 0;


    $taxablePercentageValue = DB::table('company_car_taxable_types')
        ->where('id', $taxablePercentage)
        ->value('percentage') ?? 0;

    $taxableAmount = $amount * ($taxablePercentageValue / 100);
    $totalAmount = $amount + $taxableAmount;

    $payroll = Payroll::firstOrNew(['employee_id' => $employeeId]);

    $payroll->fill([
        'company_car_under_operating_amount' => $amount,
        'company_car_taxable_percentage' => $taxablePercentageValue,
        'company_car_taxable_amount' => $taxableAmount,
        'company_car_total_amount' => $totalAmount,
    ])->save();

    return view('payroll.index', compact(
        'employee', 'payroll', 'amount', 'taxablePercentage', 'taxablePercentageValue', 'taxableAmount', 'totalAmount'
    ));
}



    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
{

    $employeeId = $request->get('employee_id');
    $term = $request->term;

    if (!$employeeId) {
        return redirect()->back()->with('error', 'Employee ID is missing.');
    }
    $employee = $this->findWorkspaceEmployeeOrFail((int) $employeeId);

    $taxableTypes = CompanyCarTaxableType::select('id', 'percentage')->get();


    return view('hrm.company-car-operating.create', compact('taxableTypes', 'employeeId','term','employee'));
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
        'amount' => 'required|numeric|min:0',
        'taxable_percentage' => 'required|exists:company_car_taxable_types,percentage',
        'term' => 'required|date',
    ]);


    $amount = $request->input('amount');
    $taxablePercentage = $request->input('taxable_percentage');
    $employeeId = $request->input('employee_id');
     $term = $request->term;

    $this->findWorkspaceEmployeeOrFail((int) $employeeId);

    $taxablePercentageValue = DB::table('company_car_taxable_types')
        ->where('percentage', $taxablePercentage)
        ->value('percentage');


    if ($taxablePercentageValue === null) {
        return back()->with('error', 'Invalid taxable percentage.');
    }


    Log::info("Taxable Percentage: {$taxablePercentageValue}%");
    Log::info("Amount: {$amount}");


    $payroll = Payroll::updateOrCreate(
        ['employee_id' => $employeeId],
        [
            'company_car_under_operating_amount' => $amount,
            'company_car_taxable_percentage' => $taxablePercentageValue,
        ]
    );


    DB::table('company_cars_under_operating')->insert([
        'employee_id' => $employeeId,
        'amount' => $amount,
        'taxable_percentage' => $taxablePercentageValue,
         'term' => $term,
        'created_at' => now(),
        'updated_at' => now(),
    ]);


    return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $request->term])
        ->with('success', 'Company Cars Under Operating Store Successfully!');
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
        $companyCar = $this->findWorkspaceCompanyCarOrFail((int) $id);
        $taxableTypes = CompanyCarTaxableType::select('id', 'percentage')->get();
        $employee = $this->findWorkspaceEmployeeOrFail((int) $companyCar->employee_id);
         $term = $request->query('term');
        return view('hrm.company-car-operating.edit', compact('companyCar', 'taxableTypes','employee','term'));
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
            'amount' => 'required|numeric|min:0',
            'taxable_percentage' => [
                'required',
                Rule::exists('company_car_taxable_types', 'percentage'),
               
            ],
             'term' => 'required|date',
        ], [
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The amount must be at least 0.',
            // Optionally add custom message if needed:
            // 'taxable_percentage.required' => 'The taxable percentage is required.',
            // 'taxable_percentage.exists' => 'Selected taxable percentage is invalid.',
        ]);

        $companyCar = $this->findWorkspaceCompanyCarOrFail((int) $id);
        $this->findWorkspaceEmployeeOrFail((int) $companyCar->employee_id);
        $companyCar->update([
            'amount' => $request->amount,
            'taxable_percentage' => $request->taxable_percentage,
            'term' => $request->term,
        ]);

        $employeeId =  $companyCar->employee_id;

        return redirect()->route('payroll.index', ['employee_id' => $employeeId,'term' => $request->term,])
            ->with('success', 'CompanyCarUnderOperating Updated successfully!');
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
        return redirect()->route('payroll.index', ['employee_id' => $employee_id,'term' => $term])->with('success', 'Company Car UnderOperating Deleted Successfully!');
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id'         => 'required|exists:employees,id',
            'amount'              => 'required|numeric|min:0',
            'taxable_percentage'  => 'required|exists:company_car_taxable_types,percentage',
            'term'                => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }

    public function ajaxValidateUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'amount'             => 'required|numeric|min:0',
            'taxable_percentage' => 'required|exists:company_car_taxable_types,percentage',
            'term'               => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }
}