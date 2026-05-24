<?php

namespace App\Http\Controllers\Hrm;

use App\Models\EmailTemplate;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Hrm\Employee;
use App\Models\Hrm\Leave;
use App\Models\Hrm\LeaveManagement;
use App\Models\Hrm\EmployeeEntitlementPolicy;
use App\Models\Hrm\LeaveRecord;
use App\Events\Hrm\CreateLeave;
use App\Events\Hrm\DestroyLeave;
use App\Events\Hrm\LeaveStatus;
use App\Events\Hrm\UpdateLeave;
use App\Services\EssPushNotificationService;
use App\Services\LeaveAccrualService;
use App\Models\Hrm\PaySlip;

class LeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        if (Auth::user()->isAbleTo('leave manage')) {
            $perPage = $request->get('per_page', 10);
            if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
                // For regular employees, show only their own leave requests
                $employee = Employee::where('email', Auth::user()->email)
                    ->where('workspace_id', getActiveWorkspace())
                    ->first();
                
                if ($employee) {
                    $leaves = Leave::where('employee_id', $employee->id)
                        ->where('workspace', getActiveWorkspace())
                        ->with(['leaveManagement', 'employee'])
                        ->orderBy('id', 'desc')
                        ->paginate($perPage)->appends($request->query());
                } else {
                    $leaves = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
                }
            } else {
                // For admins/managers: Show ALL leaves for this workspace (including ESS/mobile requests)
                $leaves = Leave::where('workspace', getActiveWorkspace())
                    ->with(['leaveManagement', 'employee'])
                    ->orderBy('id', 'desc')
                    ->paginate($perPage)->appends($request->query());
                
                // Add employee name to each leave
                foreach ($leaves as $leave) {
                    if ($leave->employee) {
                        $leave->name = $leave->employee->first_name . ' ' . $leave->employee->last_name;
                        $leave->first_name = $leave->employee->first_name;
                        $leave->last_name = $leave->employee->last_name;
                    } else {
                        $leave->name = 'Unknown';
                        $leave->first_name = 'Unknown';
                        $leave->last_name = '';
                    }
                }
            }
            return view('hrm.leave.index', compact('leaves'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('leave create')) {
            if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
                $employees = Employee::where('workspace_id', getActiveWorkspace())->where('email', Auth::user()->email)->first();
            } else {
                //$employees = Employee::where('workspace', getActiveWorkspace())->where('created_by', '=', creatorId())->get()->pluck('name', 'id');
                $employees = Employee::where('workspace_id', getActiveWorkspace())->get()->pluck('first_name','id');
            }
            // Use LeaveManagement for leave types (consistent with ESS and entitlements)
            $leavetypes = LeaveManagement::where('workspace_id', getActiveWorkspace())->get();

            return view('hrm.leave.create', compact('employees', 'leavetypes'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        // dd($request->all());
        if (Auth::user()->isAbleTo('leave create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'leave_type_id' => 'required',
                    'start_date' => 'required|after:yesterday',
                    'end_date' => 'required',
                    'leave_reason' => 'required',
                    'remark' => 'nullable',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            // Use LeaveManagement instead of LeaveType
            $leave_type = LeaveManagement::find($request->leave_type_id);
            if (!$leave_type) {
                return redirect()->back()->with('error', __('Invalid leave type selected.'));
            }
            $startDate = new \DateTime($request->start_date);
            $endDate = new \DateTime($request->end_date);
            $endDate->add(new \DateInterval('P1D'));

            $leave    = new Leave();
            if (in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
                $employee = Employee::where('id', '=', $request->employee_id)->first();
                $leave->employee_id = $request->employee_id;
                $leave->user_id = $employee->user_id;
            } else {
                $employee = Employee::where('user_id', '=', Auth::user()->id)->first();
                if (!empty($employee)) {
                    $leave->user_id = Auth::user()->id;
                    $leave->employee_id = $employee->id;
                } else {
                    return redirect()->back()->with('error', __('Apologies, the employee data is currently unavailable. Please provide the necessary employee details.'));
                }
            }

            // Get employee entitlement for this leave type
            $entitlement = EmployeeEntitlementPolicy::where('employee_id', $leave->employee_id)
                ->where('leave_management_id', $leave_type->id)
                ->where('workspace', getActiveWorkspace())
                ->first();

            if (!$entitlement) {
                return redirect()->back()->with('error', __('You are not entitled to this leave type.'));
            }

            $accrualService = app(LeaveAccrualService::class);
            $asOfDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();
            $snapshot = $accrualService->getBalanceSnapshot($entitlement, $employee, $asOfDate, true);
            $totalEntitledDays = $snapshot['accrued'];
            $leaves_used = $snapshot['used'];
            $leaves_pending = $snapshot['pending'];

            $total_leave_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;

            $remaining = $totalEntitledDays - $leaves_used;
            if ($total_leave_days > $remaining) {
                return redirect()->back()->with('error', __('You are not eligible for leave. Remaining balance: ') . $remaining . __(' days'));
            }
            if (!empty($leaves_pending) && $leaves_pending + $total_leave_days > $remaining) {
                return redirect()->back()->with('error', __('Multiple leave entry is pending.'));
            }

            if ($totalEntitledDays >= $total_leave_days) {

                $leave->leave_management_id = $request->leave_type_id;
                $leave->leave_type_id    = null; // Keep for backward compatibility
                $leave->applied_on       = date('Y-m-d');
                $leave->start_date       = $request->start_date;
                $leave->end_date         = $request->end_date;
                $leave->total_leave_days = $total_leave_days;
                $leave->leave_reason     = $request->leave_reason;
                $leave->remark           = $request->remark;
                $leave->status           = 'Pending';
                $leave->workspace        = getActiveWorkspace();
                $leave->created_by       = creatorId();
                $leave->save();

                event(new CreateLeave($request, $leave));

                // Send "Employee Leave Received" notification to company admin
                $company_settings = getCompanyAllSetting();
                if (!empty($company_settings['Employee Leave Received']) && $company_settings['Employee Leave Received'] == true) {
                    $leaveUser = User::where('id', $leave->user_id)->where('workspace_id', '=', getActiveWorkspace())->first();
                    $companyAdmin = $leaveUser ? User::where('id', $leaveUser->created_by)->first() : null;

                    // Only notify the company admin if someone else created the leave (not the admin themselves)
                    if ($companyAdmin && $companyAdmin->id != Auth::user()->id) {
                        $uArr = [
                            'employee_name' => $leaveUser->name ?? ($employee->first_name . ' ' . $employee->last_name),
                            'company_name' => $companyAdmin->name,
                            'leave_start_date' => $leave->start_date,
                            'leave_end_date' => $leave->end_date,
                        ];
                        try {
                            $resp = EmailTemplate::sendEmailTemplate('Employee Leave Received', [$companyAdmin->email], $uArr, creatorId(), getActiveWorkspace());
                        } catch (\Exception $e) {
                            Log::error('Failed to send Employee Leave Received email: ' . $e->getMessage());
                        }
                    }
                }

                return redirect()->route('leave.index')->with('success', __('Leave  successfully created.'));
            } else {
                return redirect()->back()->with('error', __('Leave type ' . $leave_type->name . ' is provide maximum ' . $leave_type->days . "  days please make sure your selected days is under " . $leave_type->days . ' days.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return redirect()->back();
        return view('hrm.show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Leave $leave)
    {
        if (Auth::user()->isAbleTo('leave edit')) {
            if ($leave->workspace  == getActiveWorkspace()) {
                if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
                    // For regular employees, find their profile by employee_id from the leave
                    $employees = Employee::where('id', '=', $leave->employee_id)
                        ->where('workspace_id', getActiveWorkspace())
                        ->first();
                } else {
                    // Get employees as id => name for dropdown
                    $employees = Employee::where('workspace_id', getActiveWorkspace())
                        ->get()
                        ->mapWithKeys(function ($emp) {
                            return [$emp->id => $emp->first_name . ' ' . $emp->last_name];
                        });
                }
                // Use LeaveManagement instead of LeaveType
                $leavetypes = LeaveManagement::where('workspace_id', getActiveWorkspace())
                    ->orWhereNull('workspace_id')
                    ->get();
                
                // Fallback: if no leave types found, get all
                if ($leavetypes->isEmpty()) {
                    $leavetypes = LeaveManagement::all();
                }
                
                // Calculate balances for each leave type based on the employee (same logic as LeaveRecordController)
                $employeeId = $leave->employee_id;
                $leaveBalances = [];
                foreach ($leavetypes as $leaveType) {
                    // Get entitlement for this leave type
                    $entitlement = EmployeeEntitlementPolicy::where('employee_id', $employeeId)
                        ->where('leave_management_id', $leaveType->id)
                        ->where('workspace', getActiveWorkspace())
                        ->first();

                    $snapshot = $entitlement
                        ? app(LeaveAccrualService::class)->getBalanceSnapshot($entitlement, Employee::find($employeeId), now())
                        : ['available' => 0];

                    $balance = $snapshot['available'];
                    $leaveBalances[$leaveType->id] = $balance;
                }

                return view('hrm.leave.edit', compact('leave', 'employees', 'leavetypes', 'leaveBalances'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, Leave $leave)
    {
        if (Auth::user()->isAbleTo('leave edit')) {
            if ($leave->workspace  == getActiveWorkspace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'leave_type_id' => 'required',
                        'start_date' => 'required|date',
                        'end_date' => 'required',
                        'leave_reason' => 'required',
                        'remark' => 'nullable',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
                
                // Use LeaveManagement instead of LeaveType
                $leave_type = LeaveManagement::find($request->leave_type_id);
                if (!$leave_type) {
                    return redirect()->back()->with('error', __('Invalid leave type selected.'));
                }
                
                $startDate = new \DateTime($request->start_date);
                $endDate = new \DateTime($request->end_date);
                $endDate->add(new \DateInterval('P1D'));

                $total_leave_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;
                
                // Get employee's entitlement for this leave type
                $entitlement = EmployeeEntitlementPolicy::where('employee_id', $leave->employee_id)
                    ->where('leave_management_id', $leave_type->id)
                    ->where('workspace', getActiveWorkspace())
                    ->first();

                $snapshot = $entitlement
                    ? app(LeaveAccrualService::class)->getBalanceSnapshot($entitlement, Employee::find($leave->employee_id), \Carbon\Carbon::parse($request->end_date)->endOfDay(), true)
                    : ['available' => 0];

                $remaining = $snapshot['available'];
                
                if ($total_leave_days > $remaining) {
                    return redirect()->back()->with('error', __('You are not eligible for this leave. Remaining balance: ') . $remaining . __(' days'));
                }

                // Update leave record
                $leave->leave_management_id = $request->leave_type_id;
                $leave->leave_type_id = null; // Keep for backward compatibility
                if (!empty($request->status)) {
                    $leave->status = $request->status;
                    // Save cancellation reason if status is Rejected
                    if ($request->status == 'Rejected' && $request->has('rejection_reason')) {
                        $leave->rejection_reason = $request->rejection_reason;
                    }
                }
                $leave->start_date = $request->start_date;
                $leave->end_date = $request->end_date;
                $leave->total_leave_days = $total_leave_days;
                $leave->leave_reason = $request->leave_reason;
                $leave->remark = $request->remark;

                $previousStatus = $leave->getOriginal('status');
                $leave->save();
                event(new UpdateLeave($request, $leave));

                // Create or remove LeaveRecord when status changes
                if (!empty($request->status) && $request->status !== $previousStatus) {
                    if ($request->status === 'Approved' && $leave->employee_id && $leave->leave_management_id) {
                        // Create a LeaveRecord so the balance "used" column reflects this leave
                        LeaveRecord::create([
                            'employee_id' => $leave->employee_id,
                            'leave_type_id' => $leave->leave_management_id,
                            'start_date' => $leave->start_date,
                            'end_date' => $leave->end_date,
                            'total_days' => $leave->total_leave_days,
                        ]);
                    } elseif ($previousStatus === 'Approved' && in_array($request->status, ['Pending', 'Rejected'])) {
                        // Reverted from Approved — remove the corresponding LeaveRecord
                        LeaveRecord::where('employee_id', $leave->employee_id)
                            ->where('leave_type_id', $leave->leave_management_id)
                            ->where('start_date', $leave->start_date)
                            ->where('end_date', $leave->end_date)
                            ->delete();
                    }
                }

                // Send email notification if status changed to Approved or Rejected
                if (!empty($request->status) && in_array($request->status, ['Approved', 'Rejected'])) {
                    $company_settings = getCompanyAllSetting();
                    
                    // Determine which notification template to use based on status
                    $notificationKey = null;
                    $templateName = null;
                    
                    if ($request->status == 'Approved') {
                        $notificationKey = 'Leave Request Approved';
                        $templateName = 'Leave Request Approved';
                    } elseif ($request->status == 'Rejected') {
                        $notificationKey = 'Leave Request Rejected';
                        $templateName = 'Leave Request Rejected';
                    }
                    
                    if ($notificationKey && !empty($company_settings[$notificationKey]) && $company_settings[$notificationKey] == true) {
                        // Determine if this is an ESS employee or regular user
                        $employeeEmail = null;
                        $employeeName = null;
                        
                        if ($leave->employee_id) {
                            // ESS employee - get from Employee
                            $employeeProfile = Employee::find($leave->employee_id);
                            if ($employeeProfile) {
                                $employeeEmail = $employeeProfile->email;
                                $employeeName = $employeeProfile->full_name;
                            }
                        } elseif ($leave->user_id) {
                            // Regular user - get from User table
                            $user = User::where('id', $leave->user_id)
                                ->where('workspace_id', '=', getActiveWorkspace())
                                ->first();
                            if ($user) {
                                $employeeEmail = $user->email;
                                $employeeName = $user->name;
                            }
                        }
                        
                        if ($employeeEmail && $employeeName) {
                            $uArr = [
                                'employee_name' => $employeeName,
                                'leave_reason' => $leave->leave_reason,
                                'leave_start_date' => $leave->start_date,
                                'leave_end_date' => $leave->end_date,
                                'total_leave_days' => $leave->total_leave_days,
                                'rejection_reason' => $leave->rejection_reason ?? '',
                            ];
                            
                            try {
                                $resp = EmailTemplate::sendEmailTemplate($templateName, [$employeeEmail], $uArr, creatorId(), getActiveWorkspace());
                            } catch (\Exception $e) {
                                Log::error('Failed to send leave status email: ' . $e->getMessage());
                            }
                        }
                    }
                }
                
                // Return JSON for AJAX requests
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => __('Leave successfully updated.')]);
                }
                return redirect()->route('leave.index')->with('success', __('Leave successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * Industry standard: Can delete pending OR approved leaves until start date.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Leave $leave)
    {
        if (Auth::user()->isAbleTo('leave delete')) {
            $isOwnerOrEss = ($leave->created_by == creatorId() || $leave->source == 'ess');
            $isCorrectWorkspace = $leave->workspace == getActiveWorkspace();
            $canDelete = in_array($leave->status, ['Pending', 'Approved']);
            $leaveNotStarted = \Carbon\Carbon::parse($leave->start_date)->startOfDay()->gt(\Carbon\Carbon::now()->startOfDay());
            
            if ($isOwnerOrEss && $isCorrectWorkspace && $canDelete && $leaveNotStarted) {
                // Block cancellation if payslip for this leave's month has been finalized (payrun completed)
                $leaveMonth = \Carbon\Carbon::parse($leave->start_date)->format('Y-m');
                $finalizedPayslip = $leave->employee_id
                    ? PaySlip::where('employee_id', $leave->employee_id)
                        ->where('salary_month', 'LIKE', $leaveMonth . '%')
                        ->where('status', 2)
                        ->exists()
                    : false;
                if ($finalizedPayslip) {
                    return redirect()->back()->with('error', __('Cannot cancel leave. Payrun has already been completed for this period. Please contact HR for adjustments.'));
                }

                // Store leave details before deletion for email notification
                $leaveStatus = $leave->status;
                $leaveStartDate = $leave->start_date;
                $leaveEndDate = $leave->end_date;
                $leaveEmployeeId = $leave->employee_id;
                $leaveUserId = $leave->user_id;

                // If it was approved ESS leave, delete the corresponding LeaveRecord
                if ($leave->status === 'Approved' && $leave->leave_management_id && $leave->employee_id) {
                    \App\Models\Hrm\LeaveRecord::where('employee_id', $leave->employee_id)
                        ->where('leave_type_id', $leave->leave_management_id)
                        ->where('start_date', $leave->start_date)
                        ->where('end_date', $leave->end_date)
                        ->delete();
                }
                
                event(new DestroyLeave($leave));
                $leave->delete();

                // Send "Employee Leave Cancelled" email notification
                $company_settings = getCompanyAllSetting();
                if (!empty($company_settings['Employee Leave Cancelled']) && $company_settings['Employee Leave Cancelled'] == true) {
                    $employeeName = 'Employee';
                    $companyName = '';
                    $adminEmail = null;

                    if ($leaveEmployeeId) {
                        $employeeProfile = Employee::find($leaveEmployeeId);
                        if ($employeeProfile) {
                            $employeeName = $employeeProfile->full_name;
                        }
                    } elseif ($leaveUserId) {
                        $leaveUser = User::find($leaveUserId);
                        if ($leaveUser) {
                            $employeeName = $leaveUser->name;
                        }
                    }

                    $companyAdmin = User::where('id', creatorId())->first();
                    if ($companyAdmin) {
                        $companyName = $companyAdmin->name;
                        $adminEmail = $companyAdmin->email;
                    }

                    if ($adminEmail) {
                        $uArr = [
                            'employee_name' => $employeeName,
                            'company_name' => $companyName,
                            'leave_start_date' => $leaveStartDate,
                            'leave_end_date' => $leaveEndDate,
                            'leave_status' => $leaveStatus,
                        ];
                        try {
                            EmailTemplate::sendEmailTemplate('Employee Leave Cancelled', [$adminEmail], $uArr, creatorId(), getActiveWorkspace());
                        } catch (\Exception $e) {
                            Log::error('Failed to send Employee Leave Cancelled email: ' . $e->getMessage());
                        }
                    }
                }

                return redirect()->route('leave.index')->with('success', __('Leave successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Cannot delete this leave. It may have already started or been rejected.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function jsoncount(Request $request)
    {
        $date = annualLeaveCycle();

        $leave_counts = LeaveManagement::select(\DB::raw('COALESCE(SUM(leaves.total_leave_days),0) AS total_leave, leave_managements.leave_name as title, leave_managements.days, leave_managements.id'))->leftjoin(
            'leaves',
            function ($join) use ($request, $date) {
                $join->on('leaves.leave_management_id', '=', 'leave_managements.id');
                $join->where('leaves.employee_id', '=', $request->employee_id);
                $join->where('leaves.status', '=', 'Approved');
                $join->whereBetween('leaves.created_at', [$date['start_date'], $date['end_date']]);
            }
        )->where('leave_managements.workspace_id', '=', getActiveWorkspace())->groupBy('leave_managements.id')->get();
        return $leave_counts;
    }
}
