<?php

namespace App\Http\Controllers\Hrm;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use App\Models\Hrm\Employee;
use App\Models\Hrm\LeaveRecord;
use App\Models\Hrm\LeavePartial;
use App\Models\Hrm\LeaveManagement;
use App\Models\Hrm\EmployeeEntitlementPolicy;
use App\Services\LeaveAccrualService;
use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class LeaveRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $workspaceId = getActiveWorkspace();

        $leaveTypes = LeaveManagement::where('workspace_id', $workspaceId)->get();
        $employees = Employee::where('workspace_id', $workspaceId)->get();
        return view('hrm.filing.leaverecord', compact('employees', 'leaveTypes'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create($employeeId)
    {
        $workspaceId = getActiveWorkspace();

        $employee = Employee::where('workspace_id', $workspaceId)
            ->findOrFail($employeeId);

        $leaveTypes = LeaveManagement::where('workspace_id', $workspaceId)->get();
        return view('hrm.filing.leaverecord', compact('employee', 'leaveTypes'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        try {
            $workspaceId = getActiveWorkspace();
            $request->validate([
                'employee_id' => [
                    'required',
                    Rule::exists('employees', 'id')->where('workspace_id', $workspaceId),
                ],
                'leave_type_id' => [
                    'required',
                    Rule::exists('leave_managements', 'id')->where('workspace_id', $workspaceId),
                ],
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'is_partial' => 'nullable|boolean',
                'partial_hours' => 'nullable|array',
                'partial_hours.*' => 'nullable|numeric|min:0|max:24',
            ]);
            $start = Carbon::parse($request->start_date);
            $end = Carbon::parse($request->end_date);
            $totalDays = 0;
            $totalHours = 0;

            if ($request->has('partial_hours')) {
                foreach ($request->partial_hours as $hours) {
                    if (!is_null($hours)) {
                        $totalHours += (float)$hours;
                    }
                }
            }

            $fullDays = $start->diffInDays($end) + 1;
            $totalDays = $fullDays - ($totalHours / 8);

            $entitlementPolicy = EmployeeEntitlementPolicy::where('employee_id', $request->employee_id)
                ->where('leave_management_id', $request->leave_type_id)
                ->where('workspace', $workspaceId)
                ->first();

            if (!$entitlementPolicy) {
                return back()->with('error', 'Entitlement policy not found for this employee and leave type.');
            }

            $employee = Employee::find($request->employee_id);
            $leaveManagement = LeaveManagement::find($request->leave_type_id);

            if (!$employee || !$leaveManagement) {
                return back()->with('error', 'Invalid employee or leave type.');
            }

            // Check for entitlement_after_months
            $entitlementPolicyDetails = $entitlementPolicy->entitlementPolicy;
            if ($entitlementPolicyDetails && $entitlementPolicyDetails->entitlement_after_months > 0) {
                $eligibilityDate = Carbon::parse($employee->date_of_appointment)->addMonths($entitlementPolicyDetails->entitlement_after_months);
                if (Carbon::now()->lt($eligibilityDate)) {
                    return back()->with('error', 'Employee is not yet eligible for this leave type.');
                }
            }

            $accrualService = app(LeaveAccrualService::class);
            $balanceSnapshot = $accrualService->getBalanceSnapshot($entitlementPolicy, $employee, $end);
            if (!$balanceSnapshot['eligible']) {
                return back()->with('error', 'Employee is not yet eligible for this leave type.');
            }

            $availableBalance = $balanceSnapshot['available'];
            if (!$leaveManagement->unpaid_leave && $totalDays > $availableBalance) {
                return back()->with('error', 'Insufficient leave balance. Available: ' . $availableBalance . ' day(s).');
            }

            $isPartialRequest = !empty(array_filter($request->partial_hours ?? []));
            $leaveRecord = LeaveRecord::create([
                'employee_id' => $request->employee_id,
                'leave_type_id' => $request->leave_type_id,
                'start_date' => $start,
                'end_date' => $end,
                'total_days' => $totalDays,
                'is_partial' => $isPartialRequest,
                'partial_hours' => $isPartialRequest ? json_encode($request->partial_hours) : null,
                'workspace_id' => $workspaceId,
            ]);

            if ($isPartialRequest && is_array($request->partial_hours)) {
                foreach ($request->partial_hours as $date => $hours) {
                    if ($hours > 0) {
                        LeavePartial::create([
                            'leave_record_id' => $leaveRecord->id,
                            'date' => $date,
                            'hours' => $hours,
                        ]);
                    }
                }
            }
            return redirect()->route('filing.leaverecord')->with('success', 'Leave recorded and leave balance updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function getEmployeeLeaveBalances($employeeId)
    {
        $workspaceId = getActiveWorkspace();
        $employee = Employee::find($employeeId);

        if (!$employee) {
            return response()->json([]);
        }

        $leaveBalances = EmployeeEntitlementPolicy::with(['leaveManagement', 'entitlementPolicy'])
            ->where('workspace', $workspaceId)
            ->where('employee_id', $employeeId)
            ->get()
            ->map(function ($policy) use ($employee) {
                $leaveManagement = $policy->leaveManagement;
                if (!$leaveManagement) {
                    return null;
                }

                $snapshot = app(LeaveAccrualService::class)->getBalanceSnapshot($policy, $employee, Carbon::now());
                if (!$snapshot['eligible']) {
                    return null;
                }

                return [
                    'leave_management_id' => $policy->leave_management_id,
                    'leave_name' => $leaveManagement->leave_name,
                    'remaining_balance' => $snapshot['available'],
                    'updated_at' => Carbon::parse($policy->updated_at)->format('Y-m-d'),
                ];
            })
            ->filter()
            ->values();

        return response()->json($leaveBalances);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($employeeId)
    {
        $employee = Employee::findOrFail($employeeId);
        $leaves = LeaveRecord::where('employee_id', $employeeId)->get();
        return view('hrm.filing.leaverecord', compact('employee', 'leaves'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('hrm.edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function getCycleStartDate(LeaveManagement $leaveManagement, Employee $employee)
    {
        return app(LeaveAccrualService::class)->getCycleStartDate($leaveManagement, $employee, Carbon::now());
    }
}
