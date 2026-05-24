<?php

namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\MaintenanceOrder;
use App\Models\Hrm\Payroll;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Hrm\Employee;
use Illuminate\Support\Facades\Validator;

class MaintenanceOrderController extends Controller
{
    protected function findWorkspaceEmployeeOrFail(int $employeeId): Employee
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            abort(403, 'Unauthorized employee access.');
        }

        return $employee;
    }

    protected function findWorkspaceMaintenanceOrderOrFail(int $id): MaintenanceOrder
    {
        return MaintenanceOrder::whereHas('employee', function ($query) {
            $query->where('id', '>', 0);
        })->findOrFail($id);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $maintenanceorders = MaintenanceOrder::whereHas('employee', function ($query) {
            $query->where('id', '>', 0);
        })->with('employee')->paginate(10);
        return view('hrm.payroll.index', compact('maintenanceorders'));
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

        $employee = $this->findWorkspaceEmployeeOrFail((int) $employeeId);
        return view('hrm.maintenance-order.create', compact('employee','term'));
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
            'installment' => 'required|numeric|min:1',
             'term' => 'required|date',
        ], [
            'employee_id.required' => 'Employee ID is required.',
            'employee_id.exists' => 'Selected employee does not exist.',
            'installment.required' => 'Installment amount is required.',
            'installment.numeric' => 'Installment must be a number.',
            'installment.min' => 'Installment must be at least 1.',
        ]);

        $term = $request->term;
        $this->findWorkspaceEmployeeOrFail((int) $request->employee_id);

        $payroll = Payroll::where('employee_id', $request->employee_id)->first();

        if (!$payroll) {
            
            $payroll = Payroll::create([
                'employee_id' => $request->employee_id,
                'basicsalary' => 0, 
                'tax_pay' => 0,    
                'net_pay' => 0, 
                'total_deductions' => 0, 
                
            ]);
        }

       
        $maintenanceOrder = MaintenanceOrder::create([
            'employee_id' => $request->employee_id,
            'payroll_id' => $payroll->id,
            'installment' => $request->installment,
            'term' => $term,
        ]);

      
        $payroll->total_deductions += $request->installment;  
        $payroll->net_pay -= $request->installment; 
        $payroll->save();

       
        return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $request->term])
                         ->with('success', 'Maintenance Order Created Successfully.');
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
        $maintenance_order = $this->findWorkspaceMaintenanceOrderOrFail((int) $id);
        $employee = $this->findWorkspaceEmployeeOrFail((int) $maintenance_order->employee_id);
        $term = $request->query('term');
        return view('hrm.maintenance-order.edit', compact('maintenance_order', 'employee','term'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $maintenance_order = $this->findWorkspaceMaintenanceOrderOrFail((int) $id);

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'installment' => 'required|numeric|min:1',
            'term' => 'required|date',
        ], [
            'employee_id.exists' => 'The selected employee does not exist.',
            'installment.min' => 'The installment must be at least 1.',
        ]);
$term = $request->term;
    $this->findWorkspaceEmployeeOrFail((int) $request->employee_id);

        $maintenance_order->update([
            'employee_id' => $request->employee_id,
            'installment' => $request->installment,
            'term' => $term,
        ]);

       
        return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $term])
        ->with('success', 'Maintenance Order Updated Successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id,$term)
    {
        $maintenance_order = $this->findWorkspaceMaintenanceOrderOrFail((int) $id);
        $maintenance_order->delete();
        $employee_id =  $maintenance_order->employee_id;
        return redirect()->route('payroll.index', ['employee_id' => $employee_id,'term' => $term])->with('success', 'Maintenance Order Deleted Successfully!');
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id'  => 'required|exists:employees,id',
            'installment'  => 'required|numeric|min:1',
            'term'         => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }

    public function ajaxValidateUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'employee_id'  => 'required|exists:employees,id',
            'installment'  => 'required|numeric|min:1',
            'term'         => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }
}
