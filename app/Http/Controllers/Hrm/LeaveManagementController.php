<?php

namespace App\Http\Controllers\Hrm;

use App\Models\Hrm\EntitlementPolicy;
use App\Models\Hrm\LeaveManagement;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class LeaveManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    /**public function index()
    {
        $leaveManagements = LeaveManagement::all();
        return view('hrm.leave-management.index', compact('leaveManagements'));
    }*/
    public function index()
    {
        $perPage = request()->get('per_page', 10);
        $leaveManagements = LeaveManagement::where('workspace_id', getActiveWorkspace())
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)->appends(request()->query());
    
        return view('hrm.leave-management.index', [
            'leaveManagements' => $leaveManagements
        ]);
    }
    
    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('hrm.leave-management.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        // Normalize checkbox values (checkboxes send "on" or are missing)
        $request->merge([
            'set_min_balance_rule' => $request->input('set_min_balance_rule', 0),
            'unpaid_leave' => $request->input('unpaid_leave', 0),
            'show_on_payslip' => $request->input('show_on_payslip', 0),
            'show_leave_expiry' => $request->input('show_leave_expiry', 0),
            'hide_balances' => $request->input('hide_balances', 0),
        ]);

        $rules = [
            'leave_name' => 'required|string|max:255|unique:leave_managements,leave_name,NULL,id,workspace_id,' . getActiveWorkspace(),
            'cycle_length' => 'required|integer|min:1',
            'cycle_start_type' => 'required|in:appointment,january,custom',
            'custom_cycle_date' => 'required_if:cycle_start_type,custom|date|nullable',
            'visible_for' => 'required|string|in:everyone,employees',
            'unpaid_leave' => 'nullable|boolean',
            'show_on_payslip' => 'nullable|boolean',
            'show_leave_expiry' => 'nullable|boolean',
            'set_min_balance_rule' => 'nullable|boolean',
            'allow_rule_override' => 'required|string|in:not allowed,admins,approvers admin',
            'hide_balances' => 'nullable|boolean',
        ];

        if($request->input('set_min_balance_rule')) {
            $rules['minimum_balance'] = 'required|numeric|lte:0';
        }

        $messages = [
            'minimum_balance.required_if' => 'Minimum balance is required when the rule is enabled.',
            'minimum_balance.numeric' => 'The minimum balance must be a number.',
            'minimum_balance.lte' => 'Minimum balance must be less than or equal to 0.',
        ];

        $validated = Validator::make($request->all(), $rules, $messages)->validate();
        $validated['workspace_id'] = getActiveWorkspace();

        // Store in DB
        $leaveManagement = LeaveManagement::create($validated);

        // Redirect
        return redirect()->route('entitlement-policies.create', [
            'leave' => $leaveManagement->id
        ])->with('success', 'Leave Module created successfully.');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    // public function show($id)
    // {
    //     $leaveManagement = LeaveManagement::findOrFail($id);
    //     return view('hrm.leave-management.show', compact('leaveManagement'));
    // }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
       // $latestPolicy = EntitlementPolicy::findOrFail($id);
        $leaveManagement = LeaveManagement::findOrFail($id);
        return view('hrm.leave-management.edit', compact('leaveManagement'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $leaveManagement = LeaveManagement::findOrFail($id);

        // Validate form input
        $validated = $request->validate([
            'leave_name' => 'required|string|max:255|unique:leave_managements,leave_name,' . $leaveManagement->id . ',id,workspace_id,' . getActiveWorkspace(),
            'cycle_length' => 'required|integer|min:1',
            'cycle_start_type' => 'required|in:appointment,january,custom',
            'visible_for' => 'required|in:everyone,employees',
            'minimum_balance' => 'nullable|numeric|min:0',
            'allow_rule_override' => 'nullable|in:not allowed,admins,approvers admin',
            'unpaid_leave' => 'boolean',
            'show_on_payslip' => 'boolean',
            'show_leave_expiry' => 'boolean',
            'set_min_balance_rule' => 'boolean',
            'hide_balances' => 'boolean',
        ]);

        // Update the leave management record
        $leaveManagement->update([
            'leave_name' => $request->leave_name,
            'cycle_length' => $request->cycle_length,
            'cycle_start_type' => $request->cycle_start_type,
            'visible_for' => $request->visible_for,
            'unpaid_leave' => $request->boolean('unpaid_leave'),
            'show_on_payslip' => $request->boolean('show_on_payslip'),
            'show_leave_expiry' => $request->boolean('show_leave_expiry'),
            'set_min_balance_rule' => $request->boolean('set_min_balance_rule'),
            'minimum_balance' => $request->minimum_balance,
            'allow_rule_override' => $request->allow_rule_override,
            'hide_balances' => $request->boolean('hide_balances'),
        ]);

        return redirect()->route('hrm.leave-management.index')
            ->with('success', 'Leave Module updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $leaveManagement = LeaveManagement::findOrFail($id);
          $isUsed = \App\Models\Hrm\EmployeeEntitlementPolicy::where('leave_management_id', $leaveManagement->id)->exists();

    if ($isUsed) {
        return redirect()->route('hrm.leave-management.index')
            ->with('error', 'Cannot delete. This Leave Module is associated with employees.');
    }
        $leaveManagement->delete();

        return redirect()->route('hrm.leave-management.index')
            ->with('success', 'Leave Module deleted successfully.');
    }
}
