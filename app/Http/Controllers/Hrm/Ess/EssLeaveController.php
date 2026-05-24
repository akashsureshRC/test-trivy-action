<?php

namespace App\Http\Controllers\Hrm\Ess;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Hrm\Leave;
use App\Models\Hrm\LeaveManagement;
use App\Models\Hrm\LeaveRecord;
use App\Models\Hrm\EmployeeEntitlementPolicy;
use App\Models\Hrm\EntitlementPolicy;
use App\Models\Hrm\Employee;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Models\Setting;
use App\Services\LeaveAccrualService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Hrm\PaySlip;

class EssLeaveController extends Controller
{
    /**
     * Display leave dashboard with balances and recent requests.
     */
    public function index()
    {
        $employee = Auth::guard('employee')->user();
        
        // Get leave balances from EmployeeEntitlementPolicy
        $leaveBalances = $this->getLeaveBalances($employee);
        
        // Get leave requests (from Leave table - includes pending/approved/rejected)
        $leaveRequests = Leave::where('employee_id', $employee->id)
            ->where('workspace', $employee->workspace_id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('hrm.ess.leave.index', compact('leaveBalances', 'leaveRequests'));
    }

    /**
     * Show the leave application form.
     */
    public function create()
    {
        $employee = Auth::guard('employee')->user();
        
        // Get leave types available to this employee from EmployeeEntitlementPolicy
        $leaveBalances = $this->getLeaveBalances($employee);
        
        return view('hrm.ess.leave.apply', compact('leaveBalances'));
    }

    /**
     * Store a new leave request (creates Leave record with Pending status).
     */
    public function store(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        
        $validator = Validator::make($request->all(), [
            'leave_type_id' => 'required|exists:leave_managements,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'leave_reason' => 'required|string|max:500',
        ], [
            'start_date.after_or_equal' => 'Leave start date must be today or a future date.',
            'end_date.after_or_equal' => 'Leave end date must be on or after the start date.',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Get the leave management type
        $leaveManagement = LeaveManagement::find($request->leave_type_id);
        
        if (!$leaveManagement) {
            return redirect()->back()
                ->with('error', 'Invalid leave type selected.')
                ->withInput();
        }
        
        // Get employee's entitlement for this leave type
        $entitlementPolicy = EmployeeEntitlementPolicy::where('employee_id', $employee->id)
            ->where('leave_management_id', $leaveManagement->id)
            ->where('workspace', $employee->workspace_id)
            ->first();
        
        if (!$entitlementPolicy) {
            return redirect()->back()
                ->with('error', 'You are not entitled to this leave type.')
                ->withInput();
        }
        
        // Calculate total leave days
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1; // Include both start and end dates
        
        $snapshot = app(LeaveAccrualService::class)->getBalanceSnapshot(
            $entitlementPolicy,
            $employee,
            $endDate->copy()->endOfDay(),
            true
        );

        $availableBalance = $snapshot['available'];
        
        // Check if employee has enough leave balance
        if ($totalDays > $availableBalance) {
            return redirect()->back()
                ->with('error', "You don't have enough {$leaveManagement->leave_name} balance. Available: {$availableBalance} days, Requested: {$totalDays} days.")
                ->withInput();
        }
        
        // Create leave request with Pending status
        $leave = new Leave();
        $leave->employee_id = $employee->id;
        $leave->user_id = $employee->user_id ?? null;
        $leave->leave_management_id = $leaveManagement->id;
        $leave->applied_on = Carbon::now()->format('Y-m-d');
        $leave->start_date = $request->start_date;
        $leave->end_date = $request->end_date;
        $leave->total_leave_days = $totalDays;
        $leave->leave_reason = $request->leave_reason;
        $leave->remark = null; // Remark is optional, leave blank by default
        $leave->status = 'Pending';
        $leave->workspace = $employee->workspace_id;
        $leave->created_by = $employee->user_id ?? 0;
        $leave->source = 'ess';
        $leave->save();
        
        // Send email notification to HR/Company Admin
        // Get company settings for this workspace
        $companyAdmin = User::where('type', 'company')
            ->where('workspace_id', $employee->workspace_id)
            ->first();
        
        if ($companyAdmin) {
            // Check if notification is enabled
            $notificationEnabled = Setting::where('created_by', $companyAdmin->id)
                ->where('workspace', $employee->workspace_id)
                ->where('key', 'Employee Leave Received')
                ->value('value');
            
            if ($notificationEnabled) {
                $uArr = [
                    'employee_name' => $employee->full_name,
                    'company_name' => $companyAdmin->name,
                    'leave_start_date' => $leave->start_date,
                    'leave_end_date' => $leave->end_date,
                ];
                try {
                    EmailTemplate::sendEmailTemplate(
                        'Employee Leave Received', 
                        [$companyAdmin->email], 
                        $uArr, 
                        $companyAdmin->id, 
                        $employee->workspace_id
                    );
                } catch (\Exception $e) {
                    // Log error but don't stop the process
                    Log::error('Failed to send leave notification email: ' . $e->getMessage());
                }
            }
        }
        
        return redirect()->route('ess.leave')
            ->with('success', 'Leave request submitted successfully. It will be reviewed by HR.');
    }

    /**
     * Display a specific leave request.
     */
    public function show($id)
    {
        $employee = Auth::guard('employee')->user();
        
        $leave = Leave::where('id', $id)
            ->where('employee_id', $employee->id)
            ->where('workspace', $employee->workspace_id)
            ->first();
        
        if (!$leave) {
            return redirect()->route('ess.leave')
                ->with('error', 'Leave request not found.');
        }
        
        $leaveType = LeaveManagement::find($leave->leave_management_id);
        
        return view('hrm.ess.leave.show', compact('leave', 'leaveType'));
    }

    /**
     * Cancel a leave request.
     * Industry standard: Can cancel pending OR approved requests until leave start date.
     */
    public function cancel($id)
    {
        $employee = Auth::guard('employee')->user();
        
        $leave = Leave::where('id', $id)
            ->where('employee_id', $employee->id)
            ->where('workspace', $employee->workspace_id)
            ->first();
        
        if (!$leave) {
            return redirect()->route('ess.leave')
                ->with('error', 'Leave request not found.');
        }
        
        // Cannot cancel rejected requests
        if ($leave->status === 'Rejected') {
            return redirect()->route('ess.leave')
                ->with('error', 'Rejected leave requests cannot be cancelled.');
        }
        
        // Cannot cancel if leave has already started
        if (Carbon::parse($leave->start_date)->startOfDay()->lte(Carbon::now()->startOfDay())) {
            return redirect()->route('ess.leave')
                ->with('error', 'Cannot cancel leave that has already started.');
        }

        // Block cancellation if payslip for this leave's month has been finalized (payrun completed)
        $leaveMonth = Carbon::parse($leave->start_date)->format('Y-m');
        $finalizedPayslip = PaySlip::where('employee_id', $leave->employee_id)
            ->where('salary_month', 'LIKE', $leaveMonth . '%')
            ->where('status', 2)
            ->exists();
        if ($finalizedPayslip) {
            return redirect()->route('ess.leave')
                ->with('error', 'Cannot cancel leave. Payrun has already been completed for this period. Please contact HR for adjustments.');
        }

        // Store leave status before deletion for email notification
        $leaveStatus = $leave->status;
        $leaveStartDate = $leave->start_date;
        $leaveEndDate = $leave->end_date;
        
        // If it was approved, we need to delete the corresponding LeaveRecord
        if ($leave->status === 'Approved' && $leave->leave_management_id) {
            LeaveRecord::where('employee_id', $leave->employee_id)
                ->where('leave_type_id', $leave->leave_management_id)
                ->where('start_date', $leave->start_date)
                ->where('end_date', $leave->end_date)
                ->delete();
        }
        
        $leave->delete();
        
        // Send email notification to HR/Company Admin
        $companyAdmin = User::where('type', 'company')
            ->where('workspace_id', $employee->workspace_id)
            ->first();
        
        if ($companyAdmin) {
            // Check if notification is enabled
            $notificationEnabled = Setting::where('created_by', $companyAdmin->id)
                ->where('workspace', $employee->workspace_id)
                ->where('key', 'Employee Leave Cancelled')
                ->value('value');
            
            if ($notificationEnabled) {
                $uArr = [
                    'employee_name' => $employee->full_name,
                    'company_name' => $companyAdmin->name,
                    'leave_start_date' => $leaveStartDate,
                    'leave_end_date' => $leaveEndDate,
                    'leave_status' => $leaveStatus,
                ];
                try {
                    EmailTemplate::sendEmailTemplate(
                        'Employee Leave Cancelled', 
                        [$companyAdmin->email], 
                        $uArr, 
                        $companyAdmin->id, 
                        $employee->workspace_id
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to send leave cancellation email: ' . $e->getMessage());
                }
            }
        }
        
        return redirect()->route('ess.leave')
            ->with('success', 'Leave request cancelled successfully.');
    }

    /**
     * Get leave balances for the employee using EmployeeEntitlementPolicy.
     */
    private function getLeaveBalances($employee): array
    {
        $balances = [];
        
        // Get all entitlement policies for this employee
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
                'total' => $totalEntitlement,
                'used' => $used,
                'pending' => $pending,
                'available' => max(0, $available),
                'is_unpaid' => $leaveManagement->unpaid_leave,
                'cycle_start' => $snapshot['cycle_start']->format('d M Y'),
                'cycle_end' => $snapshot['cycle_end']->format('d M Y'),
            ];
        }
        
        return $balances;
    }

    /**
     * Get cycle start date based on leave management settings.
     */
    private function getCycleStartDate(LeaveManagement $leaveManagement, $employee)
    {
        $now = Carbon::now();
        
        switch ($leaveManagement->cycle_start_type) {
            case 'appointment':
                $appointmentDate = Carbon::parse($employee->date_of_appointment);
                $cycleLength = $leaveManagement->cycle_length;
                
                $monthsSinceAppointment = $appointmentDate->diffInMonths($now);
                $completedCycles = intval($monthsSinceAppointment / $cycleLength);
                
                return $appointmentDate->copy()->addMonths($completedCycles * $cycleLength);
                
            case 'january':
                $currentYear = $now->year;
                $januaryStart = Carbon::create($currentYear, 1, 1);
                
                if ($now->lt($januaryStart)) {
                    return Carbon::create($currentYear - 1, 1, 1);
                }
                return $januaryStart;
                
            case 'custom':
                if ($leaveManagement->custom_cycle_date) {
                    $customDate = Carbon::parse($leaveManagement->custom_cycle_date);
                    $cycleLength = $leaveManagement->cycle_length;
                    
                    $monthsSinceCustom = $customDate->diffInMonths($now);
                    $completedCycles = intval($monthsSinceCustom / $cycleLength);
                    
                    return $customDate->copy()->addMonths($completedCycles * $cycleLength);
                }
                // Fall through to default if no custom date
                
            default:
                // Default to January start
                $currentYear = $now->year;
                return Carbon::create($currentYear, 1, 1);
        }
    }
}
