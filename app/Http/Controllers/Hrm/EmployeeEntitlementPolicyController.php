<?php
namespace App\Http\Controllers\Hrm;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Hrm\EmployeeEntitlementPolicy;
use App\Models\Hrm\Employee;

class EmployeeEntitlementPolicyController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $employees = Employee::where('workspace_id', getActiveWorkspace())->get();
        $selectedEmployee = $request->input('employee_id');
        $query = EmployeeEntitlementPolicy::with(['employeeProfile', 'leaveManagement', 'entitlementPolicy'])
            ->where('workspace', getActiveWorkspace());

        if ($selectedEmployee) {
            $query->where('employee_id', $selectedEmployee);
        }

        $employeePolicy = $query->paginate($perPage)->appends($request->query());

        return view('hrm.employeeEntitlement.index', compact('employeePolicy', 'employees', 'selectedEmployee'));
    }

    public function create()
    {
        return view('hrm.employeeEntitlement.create');
    }

    public function store(Request $request)
    {
        // Validate and store the entitlement policy
    }

    public function edit($id)
    {
        $policy = EmployeeEntitlementPolicy::findOrFail($id);
        return view('hrm.employeeEntitlement.edit', compact('policy'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'entitlement_id' => 'required|exists:entitlement_policies,id',
            'default_entitlement' => 'required|numeric|min:0',
        ]);
        $policy = EmployeeEntitlementPolicy::findOrFail($id);
        $policy->entitlement_id = $request->entitlement_id;
        $policy->default_entitlement = $request->default_entitlement;
        $policy->save();

        return redirect()->back()->with('success', 'Entitlement updated successfully.');
    }

    public function destroy($id)
    {
        // Delete the entitlement policy
    }
}