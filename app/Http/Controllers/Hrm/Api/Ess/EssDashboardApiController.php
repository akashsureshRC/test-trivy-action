<?php

namespace App\Http\Controllers\Hrm\Api\Ess;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Http\Resources\Ess\EmployeeBasicResource;
use App\Models\Hrm\Employee;
use App\Models\Hrm\Leave;
use App\Models\Hrm\LeaveRecord;
use App\Models\Hrm\LeaveManagement;
use App\Models\Hrm\PaySlip;
use App\Models\Hrm\EmployeeEntitlementPolicy;
use App\Services\LeaveAccrualService;
use Carbon\Carbon;

class EssDashboardApiController extends Controller
{
    /**
     * Get dashboard data for the authenticated employee
     * 
     * @response 200 {
     *   "status": 1,
     *   "message": "Dashboard data retrieved successfully",
     *   "data": {
     *     "employee": {...},
     *     "leave_balances": [...],
     *     "recent_payslips": [...],
     *     "pending_leaves": [...],
     *     "next_payday": "25 Dec 2025"
     *   }
     * }
     */
    public function index(Request $request)
    {
        try {
            $employee = $request->ess_employee;

            // Get leave balances
            $leaveBalances = $this->getLeaveBalances($employee);

            // Get recent payslips (last 3) - only finalized ones (status = 2)
            $recentPayslips = PaySlip::where('employee_id', $employee->id)
                ->where('status', 2)
                ->orderBy('salary_month', 'desc')
                ->limit(3)
                ->get()
                ->map(function ($payslip) {
                    return [
                        'id' => $payslip->id,
                        'month' => Carbon::parse($payslip->salary_month)->format('F Y'),
                        'salary_month' => $payslip->salary_month,
                    ];
                });

            // Get pending leave requests
            $pendingLeaves = Leave::where('employee_id', $employee->id)
                ->where('workspace', $employee->workspace_id)
                ->where('status', 'Pending')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($leave) {
                    $leaveType = LeaveManagement::find($leave->leave_management_id);
                    return [
                        'id' => $leave->id,
                        'leave_type' => $leaveType ? $leaveType->leave_name : 'Unknown',
                        'start_date' => $leave->start_date,
                        'end_date' => $leave->end_date,
                        'total_days' => $leave->total_leave_days,
                        'status' => $leave->status,
                        'applied_on' => $leave->applied_on,
                    ];
                });

            // Quick stats
            $stats = [
                'total_payslips' => PaySlip::where('employee_id', $employee->id)->where('status', 2)->count(),
                'pending_leave_requests' => $pendingLeaves->count(),
                'years_of_service' => $employee->date_of_appointment 
                    ? Carbon::parse($employee->date_of_appointment)->diffInYears(now()) 
                    : 0,
            ];

            return response()->json([
                'status' => 1,
                'message' => 'Dashboard data retrieved successfully',
                'data' => [
                    'employee' => $this->formatEmployeeData($employee),
                    'stats' => $stats,
                    'leave_balances' => $leaveBalances,
                    'recent_payslips' => $recentPayslips,
                    'pending_leaves' => $pendingLeaves,
                    'current_date' => now()->format('l, d M Y'),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to retrieve dashboard data',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get leave balances for the employee
     */
    private function getLeaveBalances($employee): array
    {
        $balances = [];

        $entitlements = EmployeeEntitlementPolicy::with(['leaveManagement', 'entitlementPolicy'])
            ->where('employee_id', $employee->id)
            ->where('workspace', $employee->workspace_id)
            ->get();

        foreach ($entitlements as $entitlement) {
            if ($entitlement->leaveManagement) {
                $leaveManagement = $entitlement->leaveManagement;
                
                // Skip leave types hidden from self-service
                if ($leaveManagement->hide_balances) {
                    continue;
                }

                $snapshot = app(LeaveAccrualService::class)->getBalanceSnapshot($entitlement, $employee, Carbon::now(), true);
                if (!$snapshot['eligible']) {
                    continue;
                }

                $used = $snapshot['used'];
                $pending = $snapshot['pending'];
                $total = $snapshot['accrued'];
                $available = $snapshot['available'];

                $balances[] = [
                    'id' => $leaveManagement->id,
                    'name' => $leaveManagement->leave_name,
                    'total' => (float) $total,
                    'used' => (float) $used,
                    'pending' => (float) $pending,
                    'remaining' => (float) $available,
                    'is_unpaid' => (bool) $leaveManagement->unpaid_leave,
                ];
            }
        }

        return $balances;
    }

    /**
     * Get cycle start date based on leave management settings.
     */
    private function getCycleStartDate($leaveManagement, $employee)
    {
        $now = Carbon::now();
        
        switch ($leaveManagement->cycle_start_type ?? 'january') {
            case 'appointment':
                $appointmentDate = Carbon::parse($employee->date_of_appointment ?? $employee->created_at);
                $cycleLength = $leaveManagement->cycle_length ?? 12;
                
                $monthsSinceAppointment = $appointmentDate->diffInMonths($now);
                $completedCycles = intval($monthsSinceAppointment / $cycleLength);
                
                return $appointmentDate->copy()->addMonths($completedCycles * $cycleLength);
                
            case 'custom':
                if ($leaveManagement->custom_cycle_date) {
                    $customDate = Carbon::parse($leaveManagement->custom_cycle_date);
                    $cycleLength = $leaveManagement->cycle_length ?? 12;
                    
                    $monthsSinceCustom = $customDate->diffInMonths($now);
                    $completedCycles = intval($monthsSinceCustom / $cycleLength);
                    
                    return $customDate->copy()->addMonths($completedCycles * $cycleLength);
                }
                // Fall through to default
                
            case 'january':
            default:
                $currentYear = $now->year;
                return Carbon::create($currentYear, 1, 1);
        }
    }

    /**
     * Calculate the next payday based on pay frequency.
     */
    private function calculateNextPayday($employee): ?string
    {
        $payFrequency = $employee->pay_frequency;

        if (!$payFrequency) {
            return null;
        }

        $today = now();
        
        // Assuming monthly pay on the last day of month
        $nextPayday = $today->copy()->endOfMonth();
        
        if ($today->day > 25) {
            $nextPayday = $today->copy()->addMonth()->endOfMonth();
        }

        return $nextPayday->format('d M Y');
    }

    /**
     * Format employee data for API response
     */
    private function formatEmployeeData($employee): array
    {
        return (new EmployeeBasicResource($employee))->resolve();
    }
}
