<?php

namespace App\Http\Controllers\Hrm\Api\Ess;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Hrm\Leave;
use App\Models\Hrm\LeaveManagement;
use App\Models\Hrm\LeaveRecord;
use App\Models\Hrm\EmployeeEntitlementPolicy;
use App\Services\LeaveAccrualService;
use Carbon\Carbon;
use App\Models\Hrm\PaySlip;

class EssLeaveApiController extends Controller
{
    /**
     * Get list of employee's leave requests
     * 
     * @queryParam status string Filter by status (Pending, Approved, Rejected). Example: Pending
     * @queryParam year integer Filter by year. Example: 2025
     * @queryParam page integer Page number. Example: 1
     * @queryParam per_page integer Items per page. Example: 10
     */
    public function index(Request $request)
    {
        try {
            $employee = $request->ess_employee;
            
            $status = $request->get('status');
            $year = $request->get('year');
            $perPage = min($request->get('per_page', 10), 50);
            
            $query = Leave::where('employee_id', $employee->id)
                ->where('workspace', $employee->workspace_id);
            
            if ($status && in_array($status, ['Pending', 'Approved', 'Rejected'])) {
                $query->where('status', $status);
            }
            
            if ($year) {
                $query->whereYear('start_date', $year);
            }
            
            $leaves = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);
            
            $formattedLeaves = $leaves->map(function ($leave) {
                $leaveType = LeaveManagement::find($leave->leave_management_id);
                return [
                    'id' => $leave->id,
                    'leave_type_id' => $leave->leave_management_id,
                    'leave_type' => $leaveType ? $leaveType->leave_name : 'Unknown',
                    'start_date' => $leave->start_date,
                    'end_date' => $leave->end_date,
                    'total_days' => (float) $leave->total_leave_days,
                    'reason' => $leave->leave_reason,
                    'remark' => $leave->remark,
                    'status' => $leave->status,
                    'applied_on' => $leave->applied_on,
                    'source' => $leave->source ?? 'web',
                    'can_cancel' => $this->canCancelLeave($leave),
                    'created_at' => $leave->created_at ? $leave->created_at->toIso8601String() : null,
                ];
            });

            return response()->json([
                'status' => 1,
                'message' => 'Leave requests retrieved successfully',
                'data' => [
                    'leaves' => $formattedLeaves,
                    'pagination' => [
                        'current_page' => $leaves->currentPage(),
                        'last_page' => $leaves->lastPage(),
                        'per_page' => $leaves->perPage(),
                        'total' => $leaves->total(),
                        'has_more' => $leaves->hasMorePages(),
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to retrieve leave requests',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get leave balances for the employee
     */
    public function balances(Request $request)
    {
        try {
            $employee = $request->ess_employee;
            $balances = $this->getLeaveBalances($employee);

            return response()->json([
                'status' => 1,
                'message' => 'Leave balances retrieved successfully',
                'data' => [
                    'balances' => $balances,
                    'as_of_date' => now()->toIso8601String(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to retrieve leave balances',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get available leave types for the employee
     */
    public function leaveTypes(Request $request)
    {
        try {
            $employee = $request->ess_employee;
            $balances = $this->getLeaveBalances($employee);
            
            // Format as simple leave types list
            $leaveTypes = collect($balances)->map(function ($balance) {
                return [
                    'id' => $balance['id'],
                    'name' => $balance['name'],
                    'available_days' => $balance['available'],
                    'is_unpaid' => $balance['is_unpaid'],
                ];
            });

            return response()->json([
                'status' => 1,
                'message' => 'Leave types retrieved successfully',
                'data' => $leaveTypes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to retrieve leave types',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Submit a new leave request
     * 
     * @bodyParam leave_type_id integer required The leave type ID. Example: 1
     * @bodyParam start_date date required Start date (Y-m-d). Example: 2025-01-15
     * @bodyParam end_date date required End date (Y-m-d). Example: 2025-01-17
     * @bodyParam leave_reason string required Reason for leave. Example: Family vacation
     */
    public function store(Request $request)
    {
        try {
            $employee = $request->ess_employee;
            
            $validator = Validator::make($request->all(), [
                'leave_type_id' => 'required|exists:leave_managements,id',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'leave_reason' => 'required|string|max:500',
            ], [
                'leave_type_id.required' => 'Please select a leave type.',
                'leave_type_id.exists' => 'Invalid leave type selected.',
                'start_date.required' => 'Start date is required.',
                'start_date.after_or_equal' => 'Start date must be today or a future date.',
                'end_date.required' => 'End date is required.',
                'end_date.after_or_equal' => 'End date must be on or after the start date.',
                'leave_reason.required' => 'Please provide a reason for your leave request.',
                'leave_reason.max' => 'Leave reason cannot exceed 500 characters.',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Get the leave management type
            $leaveManagement = LeaveManagement::find($request->leave_type_id);
            
            if (!$leaveManagement) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Invalid leave type selected.',
                    'error_code' => 'INVALID_LEAVE_TYPE'
                ], 422);
            }
            
            // Get employee's entitlement for this leave type
            $entitlementPolicy = EmployeeEntitlementPolicy::where('employee_id', $employee->id)
                ->where('leave_management_id', $leaveManagement->id)
                ->where('workspace', $employee->workspace_id)
                ->first();
            
            if (!$entitlementPolicy) {
                return response()->json([
                    'status' => 0,
                    'message' => 'You are not entitled to this leave type.',
                    'error_code' => 'NOT_ENTITLED'
                ], 422);
            }
            
            // Calculate total leave days
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $totalDays = $startDate->diffInDays($endDate) + 1;
            
            $snapshot = app(LeaveAccrualService::class)->getBalanceSnapshot(
                $entitlementPolicy,
                $employee,
                $endDate->copy()->endOfDay(),
                true
            );

            $availableBalance = $snapshot['available'];
            
            // Check if employee has enough leave balance
            if ($totalDays > $availableBalance) {
                return response()->json([
                    'status' => 0,
                    'message' => "Insufficient {$leaveManagement->leave_name} balance. Available: {$availableBalance} days, Requested: {$totalDays} days.",
                    'error_code' => 'INSUFFICIENT_BALANCE',
                    'data' => [
                        'available' => (float) $availableBalance,
                        'requested' => (float) $totalDays,
                    ]
                ], 422);
            }
            
            // Check for overlapping leave requests
            $overlapping = Leave::where('employee_id', $employee->id)
                ->where('workspace', $employee->workspace_id)
                ->whereIn('status', ['Pending', 'Approved'])
                ->where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate)
                              ->where('end_date', '>=', $endDate);
                        });
                })
                ->exists();
            
            if ($overlapping) {
                return response()->json([
                    'status' => 0,
                    'message' => 'You already have a leave request for these dates.',
                    'error_code' => 'OVERLAPPING_LEAVE'
                ], 422);
            }
            
            // Create leave request
            $leave = new Leave();
            $leave->employee_id = $employee->id;
            $leave->user_id = $employee->user_id ?? null;
            $leave->leave_management_id = $leaveManagement->id;
            $leave->applied_on = Carbon::now()->format('Y-m-d');
            $leave->start_date = $request->start_date;
            $leave->end_date = $request->end_date;
            $leave->total_leave_days = $totalDays;
            $leave->leave_reason = $request->leave_reason;
            $leave->remark = null;
            $leave->status = 'Pending';
            $leave->workspace = $employee->workspace_id;
            $leave->created_by = $employee->user_id ?? 0;
            $leave->source = 'mobile_app';
            $leave->save();
            
            return response()->json([
                'status' => 1,
                'message' => 'Leave request submitted successfully. It will be reviewed by HR.',
                'data' => [
                    'id' => $leave->id,
                    'leave_type' => $leaveManagement->leave_name,
                    'start_date' => $leave->start_date,
                    'end_date' => $leave->end_date,
                    'total_days' => $totalDays,
                    'status' => $leave->status,
                    'applied_on' => $leave->applied_on,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to submit leave request',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get single leave request details
     */
    public function show(Request $request, $id)
    {
        try {
            $employee = $request->ess_employee;
            
            $leave = Leave::where('id', $id)
                ->where('employee_id', $employee->id)
                ->where('workspace', $employee->workspace_id)
                ->first();
            
            if (!$leave) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Leave request not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }
            
            $leaveType = LeaveManagement::find($leave->leave_management_id);
            
            return response()->json([
                'status' => 1,
                'message' => 'Leave request retrieved successfully',
                'data' => [
                    'id' => $leave->id,
                    'leave_type_id' => $leave->leave_management_id,
                    'leave_type' => $leaveType ? $leaveType->leave_name : 'Unknown',
                    'is_unpaid' => $leaveType ? (bool) $leaveType->unpaid_leave : false,
                    'start_date' => $leave->start_date,
                    'end_date' => $leave->end_date,
                    'total_days' => (float) $leave->total_leave_days,
                    'reason' => $leave->leave_reason,
                    'remark' => $leave->remark,
                    'status' => $leave->status,
                    'applied_on' => $leave->applied_on,
                    'source' => $leave->source ?? 'web',
                    'can_cancel' => $this->canCancelLeave($leave),
                    'created_at' => $leave->created_at ? $leave->created_at->toIso8601String() : null,
                    'updated_at' => $leave->updated_at ? $leave->updated_at->toIso8601String() : null,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to retrieve leave request',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Cancel a leave request
     */
    public function cancel(Request $request, $id)
    {
        try {
            $employee = $request->ess_employee;
            
            $leave = Leave::where('id', $id)
                ->where('employee_id', $employee->id)
                ->where('workspace', $employee->workspace_id)
                ->first();
            
            if (!$leave) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Leave request not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }
            
            // Cannot cancel rejected requests
            if ($leave->status === 'Rejected') {
                return response()->json([
                    'status' => 0,
                    'message' => 'Rejected leave requests cannot be cancelled.',
                    'error_code' => 'CANNOT_CANCEL_REJECTED'
                ], 422);
            }
            
            // Cannot cancel if leave has already started
            if (Carbon::parse($leave->start_date)->startOfDay()->lte(Carbon::now()->startOfDay())) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Cannot cancel leave that has already started.',
                    'error_code' => 'LEAVE_ALREADY_STARTED'
                ], 422);
            }

            // Block cancellation if payslip for this leave's month has been finalized (payrun completed)
            $leaveMonth = Carbon::parse($leave->start_date)->format('Y-m');
            $finalizedPayslip = PaySlip::where('employee_id', $leave->employee_id)
                ->where('salary_month', 'LIKE', $leaveMonth . '%')
                ->where('status', 2)
                ->exists();
            if ($finalizedPayslip) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Cannot cancel leave. Payrun has already been completed for this period. Please contact HR for adjustments.',
                    'error_code' => 'PAYRUN_FINALIZED'
                ], 422);
            }
            
            // If it was approved, delete the corresponding LeaveRecord
            if ($leave->status === 'Approved' && $leave->leave_management_id) {
                LeaveRecord::where('employee_id', $leave->employee_id)
                    ->where('leave_type_id', $leave->leave_management_id)
                    ->where('start_date', $leave->start_date)
                    ->where('end_date', $leave->end_date)
                    ->delete();
            }
            
            $leave->delete();
            
            return response()->json([
                'status' => 1,
                'message' => 'Leave request cancelled successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to cancel leave request',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Check if a leave request can be cancelled
     */
    private function canCancelLeave(Leave $leave): bool
    {
        // Cannot cancel rejected requests
        if ($leave->status === 'Rejected') {
            return false;
        }
        
        // Cannot cancel if leave has already started
        if (Carbon::parse($leave->start_date)->startOfDay()->lte(Carbon::now()->startOfDay())) {
            return false;
        }
        
        return true;
    }

    /**
     * Get leave balances for the employee
     */
    private function getLeaveBalances($employee): array
    {
        $balances = [];
        
        $entitlementPolicies = EmployeeEntitlementPolicy::with(['leaveManagement', 'entitlementPolicy'])
            ->where('workspace', $employee->workspace_id)
            ->where('employee_id', $employee->id)
            ->get();
        
        foreach ($entitlementPolicies as $policy) {
            $leaveManagement = $policy->leaveManagement;
            
            if (!$leaveManagement) {
                continue;
            }

            // Skip leave types hidden from self-service
            if ($leaveManagement->hide_balances) {
                continue;
            }

            $snapshot = app(LeaveAccrualService::class)->getBalanceSnapshot($policy, $employee, Carbon::now(), true);
            if (!$snapshot['eligible']) {
                continue;
            }

            $totalEntitlement = $snapshot['accrued'];
            $used = $snapshot['used'];
            $pending = $snapshot['pending'];
            $available = $snapshot['available'];
            
            $balances[] = [
                'id' => $leaveManagement->id,
                'name' => $leaveManagement->leave_name,
                'total' => (float) $totalEntitlement,
                'used' => (float) $used,
                'pending' => (float) $pending,
                'available' => (float) max(0, $available),
                'is_unpaid' => (bool) $leaveManagement->unpaid_leave,
                'cycle_start' => $snapshot['cycle_start']->format('Y-m-d'),
                'cycle_end' => $snapshot['cycle_end']->format('Y-m-d'),
            ];
        }
        
        return $balances;
    }

    /**
     * Get cycle start date based on leave management settings
     */
    private function getCycleStartDate(LeaveManagement $leaveManagement, $employee)
    {
        $now = Carbon::now();
        
        switch ($leaveManagement->cycle_start_type) {
            case 'appointment':
                $appointmentDate = Carbon::parse($employee->date_of_appointment ?? $employee->created_at);
                $cycleLength = $leaveManagement->cycle_length;
                
                $monthsSinceAppointment = $appointmentDate->diffInMonths($now);
                $completedCycles = intval($monthsSinceAppointment / $cycleLength);
                
                return $appointmentDate->copy()->addMonths($completedCycles * $cycleLength);
                
            case 'january':
            default:
                $currentYear = $now->year;
                return Carbon::create($currentYear, 1, 1);
        }
    }
}
