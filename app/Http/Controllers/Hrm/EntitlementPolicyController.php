<?php

namespace App\Http\Controllers\Hrm;

use App\Models\Hrm\EntitlementPolicyRange;
use App\Models\Hrm\LeaveManagement;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Hrm\EmployeeEntitlementPolicy;
use App\Models\Hrm\Employee;
use App\Models\Hrm\EntitlementPolicy;

class EntitlementPolicyController extends Controller
{
    public function index(Request $request)
    {
        $leaveId = $request->query('leave');
        $leave = LeaveManagement::find($leaveId);
        if (!$leave) {
            return redirect()->back()->with('error', 'Invalid leave ID.');
        }
        $policies = EntitlementPolicy::where('leave_management_id', $leaveId)->get();
        $ranges = EntitlementPolicyRange::whereIn('entitlement_policy_id', $policies->pluck('id'))
            ->where('status', 'Active')
            ->get();

        return view('hrm.entitlement-policies.index', compact('policies', 'leave', 'ranges'));
    }

    // public function create()
    //{
    //$leave = LeaveManagement::first(); 
    // return view('hrm.entitlement-policies.create', compact('leave'));
    /// }
    public function create(Request $request)
    {
        $leaveManagement = null;

        // Priority: ID over name
        if ($request->filled('leave')) {
            $leaveManagement = \App\Models\Hrm\LeaveManagement::find($request->leave);
        } elseif ($request->filled('leave_type')) {
            $leaveManagement = \App\Models\Hrm\LeaveManagement::where('leave_name', $request->leave_type)->first();
        }

        return view('hrm.entitlement-policies.create', compact('leaveManagement'));
    }
    public function store(Request $request)
    {
        $leaveManagement = LeaveManagement::findOrFail($request->leave_management_id);

        $request->validate([
            'leave_management_id' => 'required|exists:leave_managements,id',
            'custom_name' => 'required_if:use_custom_name,1|nullable|string|max:255',
            'entitlement_after_months' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) use ($leaveManagement) {
                    if ($value >= $leaveManagement->cycle_length) {
                        $fail("Entitlement only available after must be less than the cycle length of {$leaveManagement->cycle_length} months.");
                    }
                }
            ],
            'first_cycle.*' => 'nullable|integer',
            'last_cycle.*' => 'nullable|numeric',
            'entitlement.*' => 'nullable|numeric',
        ], [
            'custom_name.required_if' => 'The custom name field is required when Use Custom Name is checked.',
        ]);

        // Prepare the cycle-specific rules
        $cycleRules = [];
        if (
            is_array($request->first_cycle) &&
            is_array($request->last_cycle) &&
            is_array($request->entitlement)
        ) {
            foreach ($request->first_cycle as $index => $first) {
                // Check if all values at this index exist and are non-null
                if (
                    isset($request->last_cycle[$index], $request->entitlement[$index]) &&
                    $first !== null && $request->last_cycle[$index] !== null && $request->entitlement[$index] !== null
                ) {
                    $cycleRules[] = [
                        'first_cycle' => $first,
                        'last_cycle' => $request->last_cycle[$index],
                        'entitlement' => $request->entitlement[$index],
                    ];
                }
            }
        }

        // Save the Entitlement Policy
        $policy = EntitlementPolicy::create([
            'leave_management_id' => $request->leave_management_id,
            'use_custom_name' => $request->boolean('use_custom_name'),
            'custom_name' => $request->custom_name,
            'use_hours_worked' => $request->boolean('use_hours_worked'),
            'hours_per_leave' => $request->hours_per_leave,
            'paid_leave_contributes' => $request->boolean('paid_leave_contributes'),
            'default_entitlement' => $request->default_entitlement ?? 0,
            'entitlement_after_months' => $request->entitlement_after_months,
            'use_upfront_accrual' => $request->boolean('use_upfront_accrual'),
            'allow_carry_forward' => $request->boolean('allow_carry_forward'),
            'carry_forward_expiry_months' => $request->carry_forward_expiry_months,
            'limit_type' => $request->limit_type,
            'limit_value' => $request->limit_value,
            'cycle_specific_rules' => json_encode($cycleRules),
        ]);

        EntitlementPolicyRange::create([
            'entitlement_policy_id' => $policy->id,
            'start_date' => null,
            'end_date' => null
        ]);

        $employees = Employee::where('workspace_id', getActiveWorkspace())
            ->get();
        foreach ($employees as $employee) {
            $existingPolicy = EmployeeEntitlementPolicy::where([
                'employee_id' => $employee->id,
                'leave_management_id' => $leaveManagement->id,
            ])->first();
            if (!$existingPolicy) {
                EmployeeEntitlementPolicy::create([
                    'employee_id' => $employee->id,
                    'leave_management_id' => $leaveManagement->id,
                    'entitlement_id' => $policy->id,
                    'default_entitlement' => $policy->default_entitlement,
                    'workspace' => getActiveWorkspace(),
                    'created_by' => creatorId(),
                ]);
            }
        }
        return redirect()->route('entitlement-policies.index', ['leave' => $leaveManagement->id])->with('success', 'Entitlement policy created successfully!');
    }


    public function show($id)
    {
        $entitlementPolicy = EntitlementPolicy::findOrFail($id);
        return view('hrm.entitlement-policies.show', compact('entitlementPolicy'));
    }

    public function edit($id)
    {
        $entitlementPolicy = EntitlementPolicy::findOrFail($id);
        $latestPolicy = $entitlementPolicy;
        $policies = EntitlementPolicy::all();
        $leave = LeaveManagement::find($entitlementPolicy->leave_management_id);
        $leaveManagements = LeaveManagement::all();
        $rules = json_decode($latestPolicy->cycle_specific_rules, true) ?? [];

        return view('hrm.entitlement-policies.edit', compact(
            'entitlementPolicy',
            'latestPolicy',
            'policies',
            'leave',
            'leaveManagements',
            'rules'
        ))->with('editing', true);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'leave_management_id' => 'required|exists:leave_managements,id',
            'default_entitlement' => 'required|numeric',
            'custom_name' => 'required_if:use_custom_name,1|nullable|string|max:255',
            'first_cycle.*' => 'nullable|integer',
            'last_cycle.*' => 'nullable|integer',
            'entitlement.*' => 'nullable|numeric',
        ], [
            'custom_name.required_if' => 'The custom name field is required when Use Custom Name is checked.',
        ]);

        $cycleRules = [];
        if (
            is_array($request->first_cycle) &&
            is_array($request->last_cycle) &&
            is_array($request->entitlement)
        ) {
            foreach ($request->first_cycle as $index => $first) {
                $cycleRules[] = [
                    'first_cycle' => $first,
                    'last_cycle' => $request->last_cycle[$index],
                    'entitlement' => $request->entitlement[$index],
                ];
            }
        }

        $policy = EntitlementPolicy::findOrFail($id);

        $policy->update([
            'leave_management_id' => $request->leave_management_id,
            'use_custom_name' => $request->boolean('use_custom_name'),
            'custom_name' => $request->custom_name,
            'use_hours_worked' => $request->boolean('use_hours_worked'),
            'hours_per_leave' => $request->hours_per_leave,
            'paid_leave_contributes' => $request->boolean('paid_leave_contributes'),
            'default_entitlement' => $request->default_entitlement,
            'entitlement_after_months' => $request->entitlement_after_months,
            'use_upfront_accrual' => $request->boolean('use_upfront_accrual'),
            'allow_carry_forward' => $request->boolean('allow_carry_forward'),
            'carry_forward_expiry_months' => $request->carry_forward_expiry_months,
            'limit_type' => $request->limit_type,
            'limit_value' => $request->limit_value,
            'cycle_specific_rules' => json_encode($cycleRules),
        ]);

        // Sync default_entitlement to all employee records linked to this policy
        EmployeeEntitlementPolicy::where('entitlement_id', $policy->id)
            ->update(['default_entitlement' => $request->default_entitlement]);

        return redirect()->route('entitlement-policies.index', ['leave' => $request->leave_management_id])->with('success', 'Entitlement policy updated successfully!');
    }

    public function showSickLeave()
    {
        return view('leave.show', ['leaveTypeTitle' => 'Sick Leave']);
    }

    public function showFamilyLeave()
    {
        return view('leave.show', ['leaveTypeTitle' => 'Family Leave']);
    }
    public function destroy(string $id)
    {
        $entitlementPolicy = EntitlementPolicy::findOrFail($id);
        $leave = $entitlementPolicy->leave_management_id;
        $entitlementPolicy->delete();
        return redirect()->route('entitlement-policies.index', ['leave' => $leave])->with('success', 'Entitlement policy deleted successfully.');
    }

    public function storeRange(Request $request)
    {
        $data = $request->validate([
            'entitlement_policy_id' => 'required|exists:entitlement_policies,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);
        $nullRange = EntitlementPolicyRange::whereNull('start_date')
            ->whereNull('end_date')
            ->where('entitlement_policy_id', $data['entitlement_policy_id'])
            ->first();
        if ($nullRange) {
            return redirect()->back()->with('error', 'A policy range with no start and end dates already exists. Please update or delete it before adding a new range.');
        }
        // Check if the policy already has a range for the same dates
        $existingRange = EntitlementPolicyRange::where('entitlement_policy_id', $data['entitlement_policy_id'])
            ->where(function ($query) use ($data) {
                $query->where('start_date', $data['start_date'])
                    ->orWhere('end_date', $data['end_date']);
            })->first();
        if ($existingRange) {
            return redirect()->back()->with('error', 'A policy range with the same start and end dates already exists.');
        }
        EntitlementPolicyRange::create($data);
        $entitlementPolicy = EntitlementPolicy::find($request->entitlement_policy_id);
        return redirect()->route('entitlement-policies.index', ['leave' => $entitlementPolicy->leave_management_id])->with('success', 'Policy range created successfully');
    }
    public function updateRange(Request $request)
    {
        $data = $request->validate([
            'entitlement_policy_id' => 'required|exists:entitlement_policies,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $existingRange = EntitlementPolicyRange::where('entitlement_policy_id', $data['entitlement_policy_id'])
            ->where(function ($query) use ($data) {
                $query->where('start_date', $data['start_date'])
                    ->orWhere('end_date', $data['end_date']);
            })->first();
        if ($existingRange) {
            return redirect()->back()->with('error', 'A policy range with the same start and end dates already exists.');
        }
        $policyRange = EntitlementPolicyRange::find($request->id);
        $policyRange->update($data);
        $entitlementPolicy = EntitlementPolicy::find($request->entitlement_policy_id);
        return redirect()->route('entitlement-policies.index', ['leave' => $entitlementPolicy->leave_management_id])->with('success', 'Policy range updated successfully');
    }
    public function deleteRange(Request $request)
    {
        $policyRange = EntitlementPolicyRange::findOrFail($request->id);
        $entitlementPolicy = EntitlementPolicy::find($policyRange->entitlement_policy_id);
        $policyRange->delete();
        return redirect()->route('entitlement-policies.index', ['leave' => $entitlementPolicy->leave_management_id])->with('success', 'Policy range deleted successfully');
    }
}
