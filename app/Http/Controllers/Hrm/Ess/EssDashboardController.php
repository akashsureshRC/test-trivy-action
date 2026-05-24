<?php

namespace App\Http\Controllers\Hrm\Ess;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Hrm\Announcement;
use App\Models\Hrm\Leave;
use App\Models\Hrm\PaySlip;
use App\Models\Hrm\EmployeeEntitlementPolicy;
use App\Models\Hrm\LeaveRecord;
use App\Services\LeaveAccrualService;
use Carbon\Carbon;

class EssDashboardController extends Controller
{
    /**
     * Show the ESS dashboard.
     */
    public function index()
    {
        $employee = Auth::guard('employee')->user();

        // Get leave balances
        $leaveBalances = $this->getLeaveBalances($employee);

        // Get recent payslips (last 3) - only finalized ones (status = 2)
        $recentPayslips = PaySlip::where('employee_id', $employee->id)
            ->where('status', 2)
            ->orderBy('salary_month', 'desc')
            ->limit(3)
            ->get();

        // Get pending leave requests
        $pendingLeaves = Leave::where('employee_id', $employee->id)
            ->where('workspace', $employee->workspace_id)
            ->where('status', 'Pending')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get announcements
        $announcements = Announcement::where('workspace', $employee->workspace_id)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Calculate next payday based on pay frequency
        $nextPayday = $this->calculateNextPayday($employee);

        return view('hrm.ess.dashboard', compact(
            'employee',
            'leaveBalances',
            'recentPayslips',
            'pendingLeaves',
            'announcements',
            'nextPayday'
        ));
    }

    /**
     * Get leave balances for the employee.
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

                $snapshot = app(LeaveAccrualService::class)->getBalanceSnapshot($entitlement, $employee, Carbon::now());
                if (!$snapshot['eligible']) {
                    continue;
                }

                $used = $snapshot['used'];
                $total = $snapshot['accrued'];

                $balances[] = [
                    'name' => $leaveManagement->leave_name,
                    'total' => $total,
                    'used' => $used,
                    'remaining' => $snapshot['available'],
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
        // This is a simplified calculation
        // You may want to adjust based on your actual pay frequency logic
        $payFrequency = $employee->pay_frequency;

        if (!$payFrequency) {
            return null;
        }

        $today = now();
        
        // Assuming monthly pay on the last day of month
        // Adjust this logic based on your actual pay schedule
        $nextPayday = $today->copy()->endOfMonth();
        
        if ($today->day > 25) {
            $nextPayday = $today->copy()->addMonth()->endOfMonth();
        }

        return $nextPayday->format('d M Y');
    }
}
