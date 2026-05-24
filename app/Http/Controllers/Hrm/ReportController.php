<?php

namespace App\Http\Controllers\Hrm;

use App\Models\User;
use DateTime;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Hrm\Attendance;
use App\Models\Hrm\Branch;
use App\Models\Hrm\Department;
use App\Models\Hrm\Employee;
use App\Models\Hrm\Leave;
use App\Models\Hrm\LeaveManagement;
use App\Models\Hrm\PaySlip;
use App\Services\WorkingHoursService;
use App\Services\AttendanceCalculationService;
use App\Services\PayrollHelperService;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function monthlyAttendance(Request $request)
    {
        if (Auth::user()->isAbleTo('attendance report manage')) {
            $branch = Branch::where('created_by', '=', creatorId())->where('workspace', getActiveWorkspace())->get()->pluck('name', 'id');
            $branch->prepend('All', '');

            $department = Department::where('created_by', '=', creatorId())->where('workspace', getActiveWorkspace())->get()->pluck('name', 'id');
            $department->prepend('All', '');


            $data['branch']     = __('All');
            $data['department'] = __('All');

            $employees = User::where('workspace_id', getActiveWorkspace())
                ->leftjoin('employees', 'users.id', '=', 'employees.user_id')
                ->where('users.created_by', creatorId())->emp()
                ->select('users.id', 'users.name');
            if (!empty($request->branch)) {
                $employees->where('branch_id', $request->branch);
            }

            if (!empty($request->department)) {
                $employees->where('department_id', $request->department);
            }
            if (!empty($request->employee_id && !in_array('0', $request->employee_id))) {
                $employees->whereIn('employees.id', $request->employee_id);
            }
            $employees = $employees->get()->pluck('name', 'id');

            if ($request->has('week') && $request->type == 'weekly') {
                $week = $request->input('week');
                $year = substr($week, 0, 4);
                $week_number = substr($week, -2);

                $start_date = date("Y-m-d", strtotime($year . "W" . $week_number));
                $week_dates = [];

                $date = new DateTime($start_date);
                for ($i = 0; $i < 7; $i++) {
                    $week_dates[] = $date->format('d-m-Y');
                    $dates[] = date('d', strtotime($date->format('Y-m-d')));
                    $date->modify('+1 day');
                }
                $start_date = reset($week_dates);
                $end_date = end($week_dates);
                $curMonth    = $start_date . __(' To ') . $end_date;
            } elseif ($request->has('month') && $request->type == 'monthly') {
                $currentdate = strtotime($request->month);
                $month       = date('m', $currentdate);
                $year        = date('Y', $currentdate);

                $curMonth    = date('M-Y', strtotime($request->month));

                $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
                for ($i = 1; $i <= $num_of_days; $i++) {
                    $dates[] = str_pad($i, 2, '0', STR_PAD_LEFT);
                }
            } else {
                $month    = date('m');
                $year     = date('Y');
                $curMonth = date('M-Y', strtotime($year . '-' . $month));

                $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
                for ($i = 1; $i <= $num_of_days; $i++) {
                    $dates[] = str_pad($i, 2, '0', STR_PAD_LEFT);
                }
            }

            $employeesAttendance = [];
            $totalPresent        = $totalLeave = $totalEarlyLeave = 0;
            $ovetimeHours        = $overtimeMins = $earlyleaveHours = $earlyleaveMins = $lateHours = $lateMins = 0;
            foreach ($employees as $id => $employee) {
                $attendances['name'] = $employee;
                if ($request->type == 'weekly') {
                    foreach ($week_dates as $date) {
                        $employeeAttendance = Attendance::where('employee_id', $id)
                            ->whereDate('date', '=', date('Y-m-d', strtotime($date)))
                            ->where('workspace', getActiveWorkspace())
                            ->first();

                        if (!empty($employeeAttendance) && $employeeAttendance->status == 'Present') {
                            $attendanceStatus[$date] = 'P';
                            $totalPresent            += 1;

                            if ($employeeAttendance->overtime > 0) {
                                $ovetimeHours += date('h', strtotime($employeeAttendance->overtime));
                                $overtimeMins += date('i', strtotime($employeeAttendance->overtime));
                            }

                            if ($employeeAttendance->early_leaving > 0) {
                                $earlyleaveHours += date('h', strtotime($employeeAttendance->early_leaving));
                                $earlyleaveMins  += date('i', strtotime($employeeAttendance->early_leaving));
                            }

                            if ($employeeAttendance->late > 0) {
                                $lateHours += date('h', strtotime($employeeAttendance->late));
                                $lateMins  += date('i', strtotime($employeeAttendance->late));
                            }
                        } elseif (!empty($employeeAttendance) && $employeeAttendance->status == 'Leave') {
                            $attendanceStatus[$date] = 'A';
                            $totalLeave              += 1;
                        } else {
                            $attendanceStatus[$date] = '';
                        }
                    }
                } else {
                    foreach ($dates as $date) {
                        $dateFormat = $year . '-' . $month . '-' . $date;
                        if ($dateFormat <= date('Y-m-d')) {
                            $employeeAttendance = Attendance::where('employee_id', $id)->where('date', $dateFormat)->where('workspace', getActiveWorkspace())->first();

                            if (!empty($employeeAttendance) && $employeeAttendance->status == 'Present') {
                                $attendanceStatus[$date] = 'P';
                                $totalPresent            += 1;

                                if ($employeeAttendance->overtime > 0) {
                                    $ovetimeHours += date('h', strtotime($employeeAttendance->overtime));
                                    $overtimeMins += date('i', strtotime($employeeAttendance->overtime));
                                }

                                if ($employeeAttendance->early_leaving > 0) {
                                    $earlyleaveHours += date('h', strtotime($employeeAttendance->early_leaving));
                                    $earlyleaveMins  += date('i', strtotime($employeeAttendance->early_leaving));
                                }

                                if ($employeeAttendance->late > 0) {
                                    $lateHours += date('h', strtotime($employeeAttendance->late));
                                    $lateMins  += date('i', strtotime($employeeAttendance->late));
                                }
                            } elseif (!empty($employeeAttendance) && $employeeAttendance->status == 'Leave') {
                                $attendanceStatus[$date] = 'A';
                                $totalLeave              += 1;
                            } else {
                                $attendanceStatus[$date] = '';
                            }
                        } else {
                            $attendanceStatus[$date] = '';
                        }
                    }
                }

                $attendances['status'] = $attendanceStatus;
                $employeesAttendance[] = $attendances;
            }

            $totalOverTime   = $ovetimeHours + ($overtimeMins / 60);
            $totalEarlyleave = $earlyleaveHours + ($earlyleaveMins / 60);
            $totalLate       = $lateHours + ($lateMins / 60);

            $data['totalOvertime']   = $totalOverTime;
            $data['totalEarlyLeave'] = $totalEarlyleave;
            $data['totalLate']       = $totalLate;
            $data['totalPresent']    = $totalPresent;
            $data['totalLeave']      = $totalLeave;
            $data['curMonth']        = $curMonth;

            return view('hrm.report.monthlyAttendance', compact('employeesAttendance', 'branch', 'department', 'dates', 'data'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display detailed attendance report with geofencing data and time calculations.
     * @return Renderable
     */
    public function detailedAttendance(Request $request)
    {
        if (Auth::user()->isAbleTo('attendance report manage')) {
            $branch = Branch::where('created_by', '=', creatorId())
                ->where('workspace', getActiveWorkspace())
                ->get()
                ->pluck('name', 'id');
            $branch->prepend('All', '');

            $department = Department::where('created_by', '=', creatorId())
                ->where('workspace', getActiveWorkspace())
                ->get()
                ->pluck('name', 'id');
            $department->prepend('All', '');

            // Default date range - current month
            $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
            $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

            // Get employees based on filters - use employees directly
            $employees = Employee::where('workspace_id', getActiveWorkspace())
                ->whereNull('deleted_at');

            if (!empty($request->branch_id)) {
                $employees->where('branch_id', $request->branch_id);
            }

            if (!empty($request->department_id)) {
                $employees->where('department_id', $request->department_id);
            }

            if (!empty($request->employee_id) && $request->employee_id != '0') {
                $employees->where('id', $request->employee_id);
            }

            $employees = $employees->get();

            // Build summary data
            $employeeReports = [];
            $totals = [
                'total_days' => 0,
                'present_days' => 0,
                'absent_days' => 0,
                'expected_minutes' => 0,
                'worked_minutes' => 0,
                'late_minutes' => 0,
                'early_leaving_minutes' => 0,
                'overtime_minutes' => 0,
                'rest_minutes' => 0,
            ];

            foreach ($employees as $employee) {
                // employee IS the profile directly from our query
                $profile = $employee;

                // Calculate period summary using static service method
                $summary = AttendanceCalculationService::calculatePeriodSummary($profile, $startDate, $endDate);

                // Get daily attendance records for detail view
                $dailyRecords = Attendance::where('employee_id', $profile->id)
                    ->where('workspace', getActiveWorkspace())
                    ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->orderBy('date')
                    ->get()
                    ->groupBy(function($att) {
                        return $att->date->format('Y-m-d');
                    });

                $processedDaily = [];
                $currentDate = $startDate->copy();
                
                while ($currentDate <= $endDate) {
                    $dateStr = $currentDate->format('Y-m-d');
                    $workingHours = WorkingHoursService::getWorkingHoursForDate($profile, $currentDate);
                    $isWorkingDay = $workingHours && $workingHours->is_working_day;
                    
                    $dayData = [
                        'date' => $dateStr,
                        'day_name' => $currentDate->format('D'),
                        'is_working_day' => $isWorkingDay,
                        'expected_start' => $workingHours ? $workingHours->start_time : null,
                        'expected_end' => $workingHours ? $workingHours->end_time : null,
                        'status' => 'N/A',
                        'clock_in' => null,
                        'clock_out' => null,
                        'worked_minutes' => 0,
                        'late_minutes' => 0,
                        'early_leaving_minutes' => 0,
                        'overtime_minutes' => 0,
                        'rest_minutes' => 0,
                        'source' => null,
                    ];

                    if (isset($dailyRecords[$dateStr])) {
                        $dayAttendances = $dailyRecords[$dateStr];
                        $firstClockIn = $dayAttendances->first();
                        $lastClockOut = $dayAttendances->where('clock_out', '!=', null)->last();

                        $dayData['clock_in'] = $firstClockIn->clock_in;
                        $dayData['clock_out'] = $lastClockOut ? $lastClockOut->clock_out : null;
                        $dayData['status'] = $firstClockIn->status;
                        
                        // Check if any attendance record requires HR review
                        $pendingReview = $dayAttendances->filter(function($att) {
                            return $att->requires_hr_review && !$att->hr_reviewed_at;
                        })->count() > 0;
                        $dayData['requires_hr_review'] = $pendingReview;
                        $dayData['hr_reviewed'] = $dayAttendances->filter(function($att) {
                            return $att->hr_reviewed_at !== null;
                        })->count() > 0;

                        // Calculate totals for the day - use countable minutes
                        // Only count hours for complete records OR HR-reviewed records
                        $dayWorkedMinutes = 0;
                        foreach ($dayAttendances as $att) {
                            $dayWorkedMinutes += $att->getCountableWorkedMinutes();
                        }
                        $dayData['worked_minutes'] = $dayWorkedMinutes;

                        // Calculate rest/break minutes (gaps between clock_out and next clock_in)
                        if ($dayAttendances->count() > 1) {
                            $dayData['rest_minutes'] = AttendanceCalculationService::calculateRestMinutesForDate(
                                $profile->id,
                                $currentDate,
                                getActiveWorkspace()
                            );
                        }

                        // Calculate late using static method
                        if ($isWorkingDay && $firstClockIn->clock_in) {
                            $toleranceIn = $profile->branch ? ($profile->branch->clock_in_tolerance_minutes ?? 0) : 0;
                            $dayData['late_minutes'] = AttendanceCalculationService::calculateLateMinutes(
                                $profile,
                                $currentDate,
                                $firstClockIn->clock_in,
                                $toleranceIn
                            );
                        }

                        // Calculate early leaving using static method
                        if ($isWorkingDay && $lastClockOut && $lastClockOut->clock_out) {
                            $toleranceOut = $profile->branch ? ($profile->branch->clock_out_tolerance_minutes ?? 0) : 0;
                            $dayData['early_leaving_minutes'] = AttendanceCalculationService::calculateEarlyLeavingMinutes(
                                $profile,
                                $currentDate,
                                $lastClockOut->clock_out,
                                $toleranceOut
                            );
                        }

                        // Calculate overtime - only if worked more than expected hours
                        $dayData['overtime_minutes'] = AttendanceCalculationService::calculateOvertimeMinutes(
                            $profile,
                            $currentDate,
                            $dayWorkedMinutes
                        );
                    } elseif ($isWorkingDay && $currentDate < Carbon::now()) {
                        $dayData['status'] = 'Absent';
                    }

                    $processedDaily[] = $dayData;
                    $currentDate->addDay();
                }

                // Calculate total rest minutes for the period
                $totalRestMinutes = 0;
                foreach ($processedDaily as $day) {
                    $totalRestMinutes += $day['rest_minutes'] ?? 0;
                }

                // Map summary data from service format to view format
                $mappedSummary = [
                    'total_working_days' => $summary['expected']['working_days'],
                    'present_days' => $summary['actual']['days_present'],
                    'absent_days' => $summary['expected']['working_days'] - $summary['actual']['days_present'],
                    'expected_minutes' => $summary['expected']['total_minutes'],
                    'worked_minutes' => $summary['actual']['total_worked_minutes'],
                    'late_minutes' => $summary['actual']['total_late_minutes'],
                    'early_leaving_minutes' => $summary['actual']['total_early_leaving_minutes'],
                    'overtime_minutes' => $summary['actual']['total_overtime_minutes'],
                    'rest_minutes' => $totalRestMinutes,
                ];

                // Create employee object with name for view compatibility
                $employeeObj = (object) [
                    'id' => $profile->id,
                    'name' => $profile->first_name . ' ' . $profile->last_name,
                    'employee_id' => $profile->employee_id,
                ];

                $employeeReports[] = [
                    'employee' => $employeeObj,
                    'profile' => $profile,
                    'summary' => $mappedSummary,
                    'daily' => $processedDaily,
                ];

                // Accumulate totals
                $totals['total_days'] += $mappedSummary['total_working_days'];
                $totals['present_days'] += $mappedSummary['present_days'];
                $totals['absent_days'] += $mappedSummary['absent_days'];
                $totals['expected_minutes'] += $mappedSummary['expected_minutes'];
                $totals['worked_minutes'] += $mappedSummary['worked_minutes'];
                $totals['late_minutes'] += $mappedSummary['late_minutes'];
                $totals['early_leaving_minutes'] += $mappedSummary['early_leaving_minutes'];
                $totals['overtime_minutes'] += $mappedSummary['overtime_minutes'];
                $totals['rest_minutes'] += $mappedSummary['rest_minutes'];
            }

            // Filter data for the view
            $filterData = [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'branch_id' => $request->branch_id ?? '',
                'department_id' => $request->department_id ?? '',
                'employee_id' => $request->employee_id ?? '',
            ];

            return view('hrm.report.detailedAttendance', compact(
                'employeeReports',
                'totals',
                'branch',
                'department',
                'filterData'
            ));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Export detailed attendance report to CSV.
     */
    public function exportDetailedAttendance(Request $request)
    {
        if (Auth::user()->isAbleTo('attendance report manage')) {
            $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
            $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

            $employees = Employee::where('workspace_id', getActiveWorkspace())
                ->whereNull('deleted_at');

            if (!empty($request->branch_id)) {
                $employees->where('branch_id', $request->branch_id);
            }

            if (!empty($request->department_id)) {
                $employees->where('department_id', $request->department_id);
            }

            if (!empty($request->employee_id) && $request->employee_id != '0') {
                $employees->where('id', $request->employee_id);
            }

            $employees = $employees->get();

            $filename = 'attendance_report_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($employees, $startDate, $endDate) {
                $file = fopen('php://output', 'w');

                // Header row
                fputcsv($file, [
                    'Employee ID',
                    'Employee Name',
                    'Date',
                    'Day',
                    'Status',
                    'Clock In',
                    'Clock Out',
                    'Expected Hours',
                    'Worked Hours',
                    'Late (mins)',
                    'Early Leave (mins)',
                    'Overtime (mins)'
                ]);

                foreach ($employees as $employee) {
                    // employee IS the profile directly
                    $profile = $employee;

                    $currentDate = $startDate->copy();
                    while ($currentDate <= $endDate) {
                        $dateStr = $currentDate->format('Y-m-d');
                        $workingHours = WorkingHoursService::getWorkingHoursForDate($profile, $currentDate);
                        $isWorkingDay = $workingHours && $workingHours->is_working_day;

                        // Get ALL attendance records for this day (multiple clock in/out)
                        $dayAttendances = Attendance::where('employee_id', $profile->id)
                            ->where('workspace', getActiveWorkspace())
                            ->whereDate('date', $dateStr)
                            ->orderBy('clock_in', 'asc')
                            ->get();

                        $status = 'N/A';
                        $clockIn = '';
                        $clockOut = '';
                        $workedMinutes = 0;
                        $lateMinutes = 0;
                        $earlyMinutes = 0;
                        $overtimeMinutes = 0;
                        $source = 'N/A';
                        $expectedMinutes = WorkingHoursService::getExpectedMinutesForDate($profile, $currentDate);

                        if ($dayAttendances->isNotEmpty()) {
                            $firstAttendance = $dayAttendances->first();
                            $lastAttendance = $dayAttendances->where('clock_out', '!=', null)->last() ?? $firstAttendance;
                            
                            $status = $firstAttendance->status;
                            $clockIn = $firstAttendance->clock_in ?? '';
                            $clockOut = $lastAttendance->clock_out ?? '';
                            
                            // Sum worked minutes from all entries
                            foreach ($dayAttendances as $att) {
                                $workedMinutes += $att->getWorkedMinutes();
                            }

                            // Calculate late based on first clock in
                            if ($isWorkingDay && $firstAttendance->clock_in) {
                                $toleranceIn = $profile->branch ? ($profile->branch->clock_in_tolerance_minutes ?? 0) : 0;
                                $lateMinutes = AttendanceCalculationService::calculateLateMinutes(
                                    $profile,
                                    $currentDate,
                                    $firstAttendance->clock_in,
                                    $toleranceIn
                                );
                            }

                            // Calculate early leaving based on last clock out
                            if ($isWorkingDay && $lastAttendance->clock_out) {
                                $toleranceOut = $profile->branch ? ($profile->branch->clock_out_tolerance_minutes ?? 0) : 0;
                                $earlyMinutes = AttendanceCalculationService::calculateEarlyLeavingMinutes(
                                    $profile,
                                    $currentDate,
                                    $lastAttendance->clock_out,
                                    $toleranceOut
                                );
                            }

                            // Calculate overtime based on actual worked minutes exceeding expected
                            $overtimeMinutes = AttendanceCalculationService::calculateOvertimeMinutes(
                                $profile,
                                $currentDate,
                                $workedMinutes
                            );
                        } elseif ($isWorkingDay && $currentDate < Carbon::now()) {
                            $status = 'Absent';
                        }

                        fputcsv($file, [
                            $profile->employee_id ?? '',
                            $profile->first_name . ' ' . $profile->last_name,
                            $dateStr,
                            $currentDate->format('D'),
                            $status,
                            $clockIn,
                            $clockOut,
                            $this->formatMinutesToHours($expectedMinutes),
                            $this->formatMinutesToHours($workedMinutes),
                            $lateMinutes,
                            $earlyMinutes,
                            $overtimeMinutes
                        ]);

                        $currentDate->addDay();
                    }
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Format minutes to hours:minutes string.
     */
    private function formatMinutesToHours($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%d:%02d', $hours, $mins);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function leave(Request $request)
    {
        if (Auth::user()->isAbleTo('leave report manage')) {

            $branch = Branch::where('created_by', '=', creatorId())->where('workspace', getActiveWorkspace())->get()->pluck('name', 'id');
            $branch->prepend('All', '');

            $department = Department::where('created_by', '=', creatorId())->where('workspace', getActiveWorkspace())->get()->pluck('name', 'id');
            $department->prepend('All', '');

            $filterYear['branch']        = __('All');
            $filterYear['department']    = __('All');
            $filterYear['type']          = __('Monthly');
            $filterYear['dateYearRange'] = date('M-Y');

            $employees = User::where('workspace_id', getActiveWorkspace())
                ->leftjoin('employees', 'users.id', '=', 'employees.user_id')
                ->where('users.created_by', creatorId())->emp()
                ->select('users.id', 'users.name', 'employees.employee_id');

            if (!empty($request->branch)) {
                $employees->where('branch_id', $request->branch);
                $filterYear['branch'] = !empty(Branch::find($request->branch)) ? Branch::find($request->branch)->name : '';
            }
            if (!empty($request->department)) {
                $employees->where('department_id', $request->department);
                $filterYear['department'] = !empty(Department::find($request->department)) ? Department::find($request->department)->name : '';
            }


            $employees = $employees->get();

            $leaves        = [];
            $totalApproved = $totalReject = $totalPending = 0;
            foreach ($employees as $employee) {
                $employeeLeave['id']          = $employee->id;
                $employeeLeave['employee_id'] = $employee->employee_id;
                $employeeLeave['employee']    = $employee->name;

                $approved = Leave::where('user_id', $employee->id)->where('status', 'Approved');
                $reject   = Leave::where('user_id', $employee->id)->where('status', 'Reject');
                $pending  = Leave::where('user_id', $employee->id)->where('status', 'Pending');

                if ($request->type == 'monthly' && !empty($request->month)) {
                    $month = date('m', strtotime($request->month));
                    $year  = date('Y', strtotime($request->month));

                    $approved->whereMonth('applied_on', $month)->whereYear('applied_on', $year);
                    $reject->whereMonth('applied_on', $month)->whereYear('applied_on', $year);
                    $pending->whereMonth('applied_on', $month)->whereYear('applied_on', $year);

                    $filterYear['dateYearRange'] = date('M-Y', strtotime($request->month));
                    $filterYear['type']          = __('Monthly');
                } elseif (!isset($request->type)) {
                    $month     = date('m');
                    $year      = date('Y');
                    $monthYear = date('Y-m');

                    $approved->whereMonth('applied_on', $month)->whereYear('applied_on', $year);
                    $reject->whereMonth('applied_on', $month)->whereYear('applied_on', $year);
                    $pending->whereMonth('applied_on', $month)->whereYear('applied_on', $year);

                    $filterYear['dateYearRange'] = date('M-Y', strtotime($monthYear));
                    $filterYear['type']          = __('Monthly');
                }

                if ($request->type == 'yearly' && !empty($request->year)) {
                    $approved->whereYear('applied_on', $request->year);
                    $reject->whereYear('applied_on', $request->year);
                    $pending->whereYear('applied_on', $request->year);


                    $filterYear['dateYearRange'] = $request->year;
                    $filterYear['type']          = __('Yearly');
                }

                $approved = $approved->count();
                $reject   = $reject->count();
                $pending  = $pending->count();

                $totalApproved += $approved;
                $totalReject   += $reject;
                $totalPending  += $pending;

                $employeeLeave['approved'] = $approved;
                $employeeLeave['reject']   = $reject;
                $employeeLeave['pending']  = $pending;


                $leaves[] = $employeeLeave;
            }

            $starting_year = date('Y', strtotime('-5 year'));
            $ending_year   = date('Y', strtotime('+5 year'));

            $filterYear['starting_year'] = $starting_year;
            $filterYear['ending_year']   = $ending_year;

            $filter['totalApproved'] = $totalApproved;
            $filter['totalReject']   = $totalReject;
            $filter['totalPending']  = $totalPending;


            return view('hrm.report.leave', compact('department', 'branch', 'leaves', 'filterYear', 'filter'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function employeeLeave(Request $request, $employee_id, $status, $type, $month, $year)
    {
        if (Auth::user()->isAbleTo('leave report manage')) {
            $leaveTypes = LeaveManagement::where('workspace_id', getActiveWorkspace())->get();

            $leaves     = [];
            foreach ($leaveTypes as $leaveType) {
                $leave        = new Leave();
                $leave->title = $leaveType->leave_name;
                $totalLeave   = Leave::where('user_id', '=', $employee_id)->where('status', $status)->where('workspace', getActiveWorkspace())->where('leave_management_id', $leaveType->id);
                if ($type == 'yearly') {
                    $totalLeave->whereYear('applied_on', $year);
                } else {
                    $m = date('m', strtotime($month));
                    $y = date('Y', strtotime($month));

                    $totalLeave->whereMonth('applied_on', $m)->whereYear('applied_on', $y);
                }
                $totalLeave = $totalLeave->get()->count();
                $leave->total = $totalLeave;
                $leaves[]     = $leave;
            }
            $leaveData = Leave::where('user_id', '=', $employee_id)->where('status', $status)->where('workspace', getActiveWorkspace());
            if ($type == 'yearly') {
                $leaveData->whereYear('applied_on', $year);
            } else {
                $m = date('m', strtotime($month));
                $y = date('Y', strtotime($month));

                $leaveData->whereMonth('applied_on', $m)->whereYear('applied_on', $y);
            }
            $leaveData = $leaveData->get();

            return view('hrm.report.leaveShow', compact('leaves', 'leaveData'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function Payroll(Request $request)
    {
        if (Auth::user()->isAbleTo('payroll report manage')) {
            $count_key = 0;
            $data = [];
            if (!empty($request->all()) && !empty($request->start_month) && !empty($request->end_month) && !empty($request->report_type) && !empty($request->employees)) {
                $selected_month = [];

                $start    = new \DateTime($request->start_month);
                $start->modify('first day of this month');
                $end      = new \DateTime($request->end_month);
                $end->modify('first day of next month');
                $interval = \DateInterval::createFromDateString('1 month');
                $period   = new \DatePeriod($start, $interval, $end);

                // Selected Months Get and set header
                $report_type = !empty($request->report_type) ? $request->report_type : 'allowance';
                $header_args = [];
                $header_args[] = 'Name';

                foreach ($period as $dt) {
                    $selected_month[] =  $dt->format("Y-m");
                    $header_args[] =  $dt->format("M-Y");
                }
                $header_args[] = 'Total';

                // Get  selected Employees
                $employees = Employee::where('workspace_id', getActiveWorkspace());
                if (isset($request->employees) && !in_array('0', $request->employees)) {
                    $employees = $employees->whereIn('id', $request->employees);
                }
                $employees = $employees->get();

                // calculation
                foreach ($employees as $index => $employee) {
                    $temp_data = [];
                    $temp_data[] = $employee->first_name . ' ' . $employee->last_name;

                    $month_calculation = PayrollHelperService::PayrollCalculation($employee->id, $selected_month, $report_type);

                    $temp_data =  array_merge($temp_data, $month_calculation);

                    array_push($data, $temp_data);

                    $count_key = count($month_calculation);
                }
            }

            if (empty($request->all()) || $request->is_export == 'no' || !empty($request->all())) {
                $employees_box = [];
                $report_type = [
                    '' => 'Please Select',
                    'allowance' => 'Allowance',
                    'commission' => 'Commission',
                    'saturation_deduction' => 'Saturation Deduction',
                    'other_payment' => 'Other Payment',
                    'overtime' => 'Overtime',
                ];

                if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
                    $employees = Employee::where('user_id', Auth::user()->id)->where('workspace', getActiveWorkspace())->get();
                } else {
                    if (!empty($request->all())) {
                        $employees = Employee::select(
        'employees.id',
        'employees.employee_id',
        'employees.first_name',
        //'employees.user_id',
        'pay_slips.salary_month'
    )
    ->leftJoin('pay_slips', 'employees.id', '=', 'pay_slips.employee_id')
    ->where('pay_slips.created_by', creatorId())
    ->where('pay_slips.salary_month', '>=', $request->start_month)
    ->where('pay_slips.salary_month', '<=', $request->end_month);
                    } else {
                        $employees = Employee::where('workspace_id', getActiveWorkspace());
                    }
                    $employees_box = $employees->pluck('first_name','last_name' ,'employees.id');

                    if (isset($request->employees) && !in_array('0', $request->employees)) {
                        $employees = $employees->whereIn('employees.id', $request->employees);
                    }
                    $employees = $employees->get();
                }

                return view('hrm.report.payroll', compact('employees', 'employees_box', 'report_type', 'data'));
            }
            if (!empty($request->all()) && $request->is_export == 'yes') {
                // For Final Total
                $final_total = [];
                $final_total[] = 'Total';
                for ($i = 1; $i <= $count_key; $i++) {
                    $final_total[] = array_sum(array_map(fn ($item) => $item[$i], $data));
                }
                array_push($data, $final_total);

                $filename = $report_type . "-" . date('Ymd') . ".csv";
                header('Content-Type: text/csv; charset=utf-8');
                header("Content-Disposition: attachment; filename=\"$filename\"");


                $output = fopen('php://output', 'w');
                ob_end_clean();
                fputcsv($output, $header_args);
                foreach ($data as $data_item) {
                    fputcsv($output, $data_item);
                }
                exit;
                return redirect()->route('report.payroll');
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function getdepartment(Request $request)
    {

        if ($request->branch_id == 0) {
            $departments = Department::where('created_by', '=', creatorId())->where('workspace', getActiveWorkspace())->get()->pluck('name', 'id')->toArray();
        } else {
            $departments = Department::where('created_by', '=', creatorId())->where('workspace', getActiveWorkspace())->where('branch_id', $request->branch_id)->get()->pluck('name', 'id')->toArray();
        }

        return response()->json($departments);
    }

    /**
     * Get employees for dropdown filtering
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getemployee(Request $request)
    {
        $query = Employee::where('workspace_id', getActiveWorkspace())
            ->whereNull('deleted_at');

        if (!empty($request->department_id)) {
            $query->where('department_id', $request->department_id);
        }

        $employees = $query->get()->mapWithKeys(function ($employee) {
            return [$employee->id => $employee->first_name . ' ' . $employee->last_name];
        })->toArray();

        return response()->json($employees);
    }
}
