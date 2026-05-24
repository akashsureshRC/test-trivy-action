<?php

namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\Employee;
use App\Models\Hrm\UnionMembershipFee;
use App\Models\Hrm\Payroll;
use App\Models\Hrm\BasicSalary;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Hrm\PayrollFilterController;

class UnionMembershipFeeController extends Controller
{
    protected $payrollFilterController;

    protected function findWorkspaceEmployeeOrFail(int $employeeId): Employee
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            abort(403, 'Unauthorized employee access.');
        }

        return $employee;
    }

    protected function findWorkspaceUnionMembershipFeeOrFail(int $id): UnionMembershipFee
    {
        return UnionMembershipFee::whereHas('employee', function ($query) {
            $query->where('id', '>', 0);
        })->findOrFail($id);
    }

    
    public function __construct(PayrollFilterController $payrollFilterController)
    {
        $this->payrollFilterController = $payrollFilterController;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $employeeId = $request->employee_id; 
        $employee = $this->findWorkspaceEmployeeOrFail((int) $employeeId);
    
        $basicSalaryData = BasicSalary::where('employee_id', $employeeId)->first(); 
    
        return view('hrm.payroll.index', compact('employee', 'basicSalaryData'));
    }
    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
{
    $employee_id = $request->employee_id; 
    $term = $request->term;

        $this->findWorkspaceEmployeeOrFail((int) $employee_id);

    return view('hrm.union-membership.create', compact('employee_id','term'));
}

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
{
    DB::enableQueryLog();
    $term = $request->input('term');
    DB::transaction(function () use ($request,$term) {
       
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount_per_period' => 'required|numeric|min:0.01',
            'term' => 'required|date',
        ]);

        $this->findWorkspaceEmployeeOrFail((int) $validatedData['employee_id']);

      
        if (UnionMembershipFee::where('employee_id', $validatedData['employee_id'])
                    ->where('term', $term)

        ->exists()) {
            return redirect()->route('union-membership.create')
                ->with('error', 'Union Membership Fee already exists for this employee.');
        }

        
        $payroll = Payroll::firstOrCreate(
            ['employee_id' => $validatedData['employee_id']],
            ['net_pay' => 0, 'union_membership_fee' => 0] 
        );

      
        UnionMembershipFee::create([
            'employee_id' => $validatedData['employee_id'],
           
            'amount_per_period' => $validatedData['amount_per_period'],
             'term' => $term,
        ]);

       
        $payroll->update([
            'union_membership_fee' => $validatedData['amount_per_period'],
        ]);
    });

    return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $term])
        ->with('success', 'Union Membership Fee Added Successfully.');
}



    public function updatePayroll(Payroll $payroll)
    {
        $unionFeeRecord = UnionMembershipFee::where('employee_id', $payroll->employee_id)->first();
        $unionFee = $unionFeeRecord ? $unionFeeRecord->amount_per_period : 0; 

        $basicSalary = $payroll->basic_salary ?? 0;

        $payroll->update([
            'union_membership_fee' => $unionFee,
            'tax_pay' => $basicSalary * 0.15,  
            'net_pay' => $basicSalary - $unionFee,  
        ]);
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
        $unionMembershipFee = $this->findWorkspaceUnionMembershipFeeOrFail((int) $id);
        $term = request('term');
        return view('hrm.union-membership.edit', compact('unionMembershipFee','term'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
{
    $validatedData = $request->validate([
        'amount_per_period' => 'required|numeric|min:0.01',
        'term' => 'required|date',
    ], [
        'amount_per_period.required' => 'The Amount per Period field is required.',
        'amount_per_period.numeric' => 'The value must be a valid number.',
        'amount_per_period.min' => 'The value must be greater than zero.',
    ]);

    $term = $validatedData['term'];
    $unionMembershipFee = $this->findWorkspaceUnionMembershipFeeOrFail((int) $id);
    $this->findWorkspaceEmployeeOrFail((int) $unionMembershipFee->employee_id);
    $unionMembershipFee->update($validatedData);

   
    Payroll::updateOrCreate(
        [
            'employee_id' => $request->input('employee_id'),
        ],
        [
            'union_membership_fee' => $validatedData['amount_per_period'],
            'term' => $term,
        ]
    );

    return redirect()->route('payroll.index', ['employee_id' => $request->input('employee_id'),'term' => $term,])
        ->with('success', 'Union Membership Fee Updated Successfully.');
}


    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id,$term)
    {
       
        $unionMembershipFee = $this->findWorkspaceUnionMembershipFeeOrFail((int) $id);
        $employee_id =   $unionMembershipFee->employee_id;
        $unionMembershipFee->delete();
        return redirect()->route('payroll.index', ['employee_id' => $employee_id,'term' => $term])->with('success', 'Union Membership Fee Deleted Successfully!');
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'amount_per_period' => 'required|numeric|min:0.01',
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
            'amount_per_period' => 'required|numeric|min:0.01',
            'term' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return response()->json(['success' => true]);
    }
}
