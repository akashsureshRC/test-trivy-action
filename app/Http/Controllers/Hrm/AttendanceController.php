<?php

namespace App\Http\Controllers\Hrm;

use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Hrm\Attendance;
use App\Models\Hrm\Branch;
use App\Models\Hrm\Department;
use App\Models\Hrm\Employee;
use App\Events\Hrm\CreateMarkAttendance;
use App\Events\Hrm\DestroyMarkAttendance;
use App\Events\Hrm\UpdateBulkAttendance;
use App\Events\Hrm\UpdateMarkAttendance;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        if (Auth::user()->isAbleTo('attendance manage')) {
            $perPage = $request->get('per_page', 10);
            $currentWorkspace = getActiveWorkspace();
            $branch = Branch::where('created_by', '=', creatorId())->where('workspace', getActiveWorkspace())->get()->pluck('name', 'id');
            $branch->prepend('All', '');

            $department = Department::where('created_by', '=', creatorId())->where('workspace', getActiveWorkspace())->get()->pluck('name', 'id');
            $department->prepend('All', '');

            // Get employees for filter dropdown
            $employees = Employee::where('workspace_id', $currentWorkspace)
                ->whereNull('deleted_at')
                ->orderBy('first_name')
                ->get()
                ->mapWithKeys(function ($employee) {
                    return [$employee->id => $employee->first_name . ' ' . $employee->last_name];
                });
            $employees = collect(['' => 'All Employees'])->union($employees);

            if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
                // For employees - get their own profile
                $profile = Employee::where('email', Auth::user()->email)->first();
                $attendances = Attendance::where('employee_id', $profile ? $profile->id : 0)->where('workspace', getActiveWorkspace());
                
                if ($request->type == 'monthly' && !empty($request->month)) {
                    $month = date('m', strtotime($request->month));
                    $year  = date('Y', strtotime($request->month));

                    $start_date = date($year . '-' . $month . '-01');
                    $end_date   = date($year . '-' . $month . '-t');

                    $attendances->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                } elseif ($request->type == 'daily' && !empty($request->date)) {
                    $attendances->where('date', $request->date);
                } else {
                    $month      = date('m');
                    $year       = date('Y');
                    $start_date = date($year . '-' . $month . '-01');
                    $end_date   = date($year . '-' . $month . '-t');

                    $attendances->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                }
                $attendances = $attendances->orderBy('date', 'desc')->orderBy('clock_in', 'desc')->paginate($perPage)->appends($request->query());
            } else {
                // Build base attendance query for the workspace
                $attendances = Attendance::where('workspace', getActiveWorkspace())->with('employee');
                
                // Filter by specific employee
                if (!empty($request->employee_id)) {
                    $attendances->where('employee_id', $request->employee_id);
                }
                
                // Apply employee filters if branch or department is selected
                if (!empty($request->branch) || !empty($request->department)) {
                    $empQuery = Employee::where('workspace_id', $currentWorkspace);
                    
                    if (!empty($request->branch)) {
                        $empQuery->where('branch_id', $request->branch);
                    }
                    if (!empty($request->department)) {
                        $empQuery->where('department_id', $request->department);
                    }
                    
                    $employeeIds = $empQuery->pluck('id');
                    $attendances->whereIn('employee_id', $employeeIds);
                }

                if ($request->type == 'monthly' && !empty($request->month)) {
                    $month = date('m', strtotime($request->month));
                    $year  = date('Y', strtotime($request->month));

                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                    $attendances->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                } elseif ($request->type == 'daily' && !empty($request->date)) {
                    $attendances->where('date', $request->date);
                } else {

                    $month      = date('m');
                    $year       = date('Y');
                    $start_date = date($year . '-' . $month . '-01');
                    $end_date   = date($year . '-' . $month . '-t');

                    $attendances->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                }

                $attendances = $attendances->orderBy('date', 'desc')->orderBy('clock_in', 'desc')->paginate($perPage)->appends($request->query());
            }

            return view('hrm.attendance.index', compact('attendances', 'branch', 'department', 'employees'));
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
        if (Auth::user()->isAbleTo('attendance create')) {
            $currentWorkspace = Auth::user()->active_workspace;

            $employees = Employee::where('workspace_id', $currentWorkspace)
                ->orderBy('first_name')
                ->get()
                ->mapWithKeys(function ($employee) {
                    return [$employee->id => $employee->first_name . ' ' . $employee->last_name];
                });
            $employees = ['' => 'Select Employee'] + $employees->toArray();

            return view('hrm.attendance.create', compact('employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('attendance create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'date' => 'required',
                    'clock_in' => 'required',
                    'clock_out' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => $messages->first()], 422);
                }
                return redirect()->back()->with('error', $messages->first());
            }

            $attendance = Attendance::where('employee_id', '=', $request->employee_id)->where('workspace', getActiveWorkspace())->where('date', '=', $request->date)->where('clock_out', '=', '00:00:00')->get()->toArray();
            if ($attendance) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => __('Employee Attendance Already Created.')], 422);
                }
                return redirect()->route('attendance.index')->with('error', __('Employee Attendance Already Created.'));
            } else {
                $employeeAttendance                = new Attendance();
                $employeeAttendance->employee_id   = $request->employee_id;
                $employeeAttendance->date          = $request->date;
                $employeeAttendance->status        = 'Present';
                $employeeAttendance->clock_in      = $request->clock_in . ':00';
                $employeeAttendance->clock_out     = $request->clock_out . ':00';
                $employeeAttendance->marked_by     = Attendance::MARKED_BY_HR;
                $employeeAttendance->workspace     = getActiveWorkspace();
                $employeeAttendance->created_by    = creatorId();
                $employeeAttendance->save();

                event(new CreateMarkAttendance($request, $employeeAttendance));

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => __('Employee attendance successfully created.')]);
                }
                return redirect()->route('attendance.index')->with('success', __('Employee attendance successfully created.'));
            }
        } else {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => __('Permission denied.')], 403);
            }
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
    public function edit($id)
    {
        if (Auth::user()->isAbleTo('attendance edit')) {
            $currentWorkspace = getActiveWorkspace();
            $attendance = Attendance::where('id', $id)->first();
            $employees = Employee::where('workspace_id', $currentWorkspace)->get()
                ->mapWithKeys(function ($employee) {
                    return [$employee->id => $employee->full_name];
                });
            return view('hrm.attendance.edit', compact('attendance', 'employees'));
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
    public function update(Request $request, $id)
    {
        if (!empty($request->employee_id)) {
            $employeeId = $request->employee_id;
        } else {
            $employeeId      = Auth::user()->id;
        }
        $todayAttendance = Attendance::where('employee_id', '=', $employeeId)->where('workspace', getActiveWorkspace())->where('date', '=', date('Y-m-d'))->first();
        $company_settings = getCompanyAllSetting();
        
        if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
            if (!empty($company_settings['defult_timezone'])) {
                date_default_timezone_set($company_settings['defult_timezone']);
            }
            $time = date("H:i");

            $attendance            = Attendance::find($id);
            $attendance->clock_out = $time;
            $attendance->save();

            event(new UpdateMarkAttendance($request, $attendance));

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => __('Employee Successfully Clock Out.')]);
            }
            return redirect()->back()->with('success', __('Employee Successfully Clock Out.'));
        } else {
            $attendance              = Attendance::find($id);
            $attendance->employee_id = $request->employee_id;
            $attendance->date        = $request->date;
            $attendance->clock_in    = $request->clock_in;
            $attendance->clock_out   = $request->clock_out;
            $attendance->save();

            event(new UpdateMarkAttendance($request, $attendance));

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => __('Employee attendance successfully updated.')]);
            }
            return redirect()->route('attendance.index')->with('success', __('Employee attendance successfully updated.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Attendance $attendance)
    {
        if (Auth::user()->isAbleTo('attendance delete')) {
            if ($attendance->workspace  == getActiveWorkspace()) {

                event(new DestroyMarkAttendance($attendance));

                $attendance->delete();
                return redirect()->route('attendance.index')->with('success', __('Attendance successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function BulkAttendance(Request $request)
    {
        if (Auth::user()->isAbleTo('bulk attendance manage')) {
            $perPage = $request->get('per_page', 10);
            $branch = Branch::where('created_by', '=', creatorId())->where('workspace', getActiveWorkspace())->get()->pluck('name', 'id');

            $department = Department::where('created_by', '=', creatorId())->where('workspace', getActiveWorkspace())->get()->pluck('name', 'id');
            $employees = [];
            if (!empty($request->branch) && !empty($request->department)) {
                $employees = Employee::where('workspace_id', getActiveWorkspace())
                    ->join('departments', 'employees.department_id', '=', 'departments.id')
                    ->where('departments.branch_id', $request->branch)
                    ->where('employees.department_id', $request->department)
                    ->with(['department.branch'])
                    ->select('employees.*');
                if ($request->date) {
                    $employees->where('employees.date_of_birth', '<=', $request->date);
                }
                $employees = $employees->paginate($perPage)->appends($request->query());
            } elseif (!empty($request->branch)) {
                $employees = Employee::where('workspace_id', getActiveWorkspace())
                    ->join('departments', 'employees.department_id', '=', 'departments.id')
                    ->where('departments.branch_id', $request->branch)
                    ->with(['department.branch'])
                    ->select('employees.*');
                if ($request->date) {
                    $employees->where('employees.date_of_birth', '<=', $request->date);
                }
                $employees = $employees->paginate($perPage)->appends($request->query());
            } elseif (!empty($request->department)) {
                $employees = Employee::where('workspace_id', getActiveWorkspace())
                    ->where('department_id', $request->department)
                    ->with(['department.branch']);
                if ($request->date) {
                    $employees->where('date_of_birth', '<=', $request->date);
                }
                $employees = $employees->paginate($perPage)->appends($request->query());
            }
            return view('hrm.attendance.bulk', compact('employees', 'branch', 'department'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function BulkAttendanceData(Request $request)
    {
        if (Auth::user()->isAbleTo('bulk attendance manage')) {
            if (!empty($request->employee_id)) {
                $employees = $request->employee_id;
                $atte      = [];

                foreach ($employees as $employee) {
                    $present = 'present-' . $employee;
                    $in      = 'in-' . $employee;
                    $out     = 'out-' . $employee;
                    $atte[]  = $present;
                    if ($request->$present == 'on') {
                        $in  = date("H:i:s", strtotime($request->$in));
                        $out = date("H:i:s", strtotime($request->$out));

                        $attendance = Attendance::where('employee_id', '=', $employee)->where('workspace', getActiveWorkspace())->where('date', '=', $request->date)->first();

                        // Get employee's branch_id from their department
                        $employeeProfile = \App\Models\Hrm\Employee::with('department')->find($employee);
                        $branchId = $employeeProfile && $employeeProfile->department ? $employeeProfile->department->branch_id : null;

                        if (!empty($attendance)) {
                            $employeeAttendance = $attendance;
                        } else {
                            $employeeAttendance              = new Attendance();
                            $employeeAttendance->employee_id = $employee;
                            $employeeAttendance->created_by  = creatorId();
                            $employeeAttendance->workspace   = getActiveWorkspace();
                        }
                        $employeeAttendance->date          = $request->date;
                        $employeeAttendance->status        = 'Present';
                        $employeeAttendance->clock_in      = $in;
                        $employeeAttendance->clock_out     = $out;
                        $employeeAttendance->branch_id     = $branchId;
                        $employeeAttendance->marked_by     = Attendance::MARKED_BY_HR;
                        $employeeAttendance->save();

                        event(new UpdateBulkAttendance($request, $employeeAttendance));
                    } else {
                        // If unchecked, delete existing attendance record if it exists
                        $attendance = Attendance::where('employee_id', '=', $employee)
                            ->where('workspace', getActiveWorkspace())
                            ->where('date', '=', $request->date)
                            ->first();

                        if (!empty($attendance)) {
                            $attendance->delete();
                        }
                    }
                }

                return redirect()->back()->with('success', __('Employee attendance successfully created.'));
            } else {
                return redirect()->back()->with('error', __('No employees selected.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function attendance(Request $request)
    {
        $employeeId      = \Auth::user()->id;
        $todayAttendance = Attendance::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d'))->first();

        // Check if already clocked in TODAY
        $existingTodaySession = Attendance::where('employee_id', '=', $employeeId)
            ->where('date', date('Y-m-d'))
            ->whereNotNull('clock_in')
            ->where(function ($q) {
                $q->whereNull('clock_out')->orWhere('clock_out', '00:00:00');
            })
            ->first();

        if ($existingTodaySession) {
            return redirect()->back()->with('error', __('You are already clocked in. Please clock out first.'));
        }

        // Flag any incomplete records from PREVIOUS days for HR review
        // This allows the employee to clock in today while flagging missed clock-outs
        $incompleteRecords = Attendance::where('employee_id', '=', $employeeId)
            ->where('date', '<', date('Y-m-d'))
            ->whereNotNull('clock_in')
            ->where(function ($q) {
                $q->whereNull('clock_out')->orWhere('clock_out', '00:00:00');
            })
            ->where('requires_hr_review', false)
            ->get();

        if ($incompleteRecords->count() > 0) {
            foreach ($incompleteRecords as $record) {
                $record->update(['requires_hr_review' => true]);
            }
        }

        if (!empty($company_settings['defult_timezone'])) {
            date_default_timezone_set($company_settings['defult_timezone']);
        }
        $date = date("Y-m-d");
        $time = date("H:i");

        $employeeAttendance              = new Attendance();
        $employeeAttendance->employee_id = $employeeId;
        $employeeAttendance->date        = $date;
        $employeeAttendance->status      = 'Present';
        $employeeAttendance->clock_in    = $time;
        $employeeAttendance->clock_out   = '00:00:00';
        $employeeAttendance->marked_by   = Attendance::MARKED_BY_HR;
        $employeeAttendance->created_by  = creatorId();
        $employeeAttendance->workspace   = getActiveWorkspace();
        $employeeAttendance->save();
        return redirect()->back()->with('success', __('Employee Successfully Clock In.'));
    }

    public function fileImportExport()
    {
        if (Auth::user()->isAbleTo('attendance import')) {
            return view('hrm.attendance.import');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function fileImport(Request $request)
    {
        if (Auth::user()->isAbleTo('attendance import')) {
            session_start();

            $error = '';
            $html = '';
            $columnMapping = [];

            // Define expected column mappings (header name => field name)
            $expectedColumns = [
                'employee_id' => 'employee_id',
                'employeeid' => 'employee_id',
                'emp_id' => 'employee_id',
                'date' => 'date',
                'attendance_date' => 'date',
                'clock_in' => 'clock_in',
                'clockin' => 'clock_in',
                'in' => 'clock_in',
                'time_in' => 'clock_in',
                'clock_out' => 'clock_out',
                'clockout' => 'clock_out',
                'out' => 'clock_out',
                'time_out' => 'clock_out',
            ];

            if ($request->hasFile('file')) {
                $file_array = explode(".", $request->file->getClientOriginalName());
                $extension = end($file_array);

                if ($extension == 'csv') {
                    $file_data = fopen($request->file->getRealPath(), 'r');
                    $file_header = fgetcsv($file_data);

                    $html .= '<table class="table table-bordered"><tr>';

                    for ($count = 0; $count < count($file_header); $count++) {
                        $headerName = strtolower(trim(str_replace([' ', '-'], '_', $file_header[$count])));
                        $selectedField = '';

                        // Check if header matches any expected column
                        if (isset($expectedColumns[$headerName])) {
                            $selectedField = $expectedColumns[$headerName];
                            $columnMapping[$selectedField] = $count;
                        }

                        $html .= '
                                <th>
                                        <select name="set_column_data" class="form-control set_column_data" data-column_number="' . $count . '">
                                            <option value="">Select Column</option>
                                            <option value="employee_id"' . ($selectedField == 'employee_id' ? ' selected' : '') . '>Employee ID</option>
                                            <option value="date"' . ($selectedField == 'date' ? ' selected' : '') . '>Date</option>
                                            <option value="clock_in"' . ($selectedField == 'clock_in' ? ' selected' : '') . '>Clock In</option>
                                            <option value="clock_out"' . ($selectedField == 'clock_out' ? ' selected' : '') . '>Clock Out</option>
                                        </select>
                                </th>
                                ';
                    }
                    $html .= '</tr>';
                    $limit = 0;
                    $temp_data = [];
                    while (($row = fgetcsv($file_data)) !== false) {
                        $limit++;
                        $html .= '<tr>';
                        for ($count = 0; $count < count($row); $count++) {
                            $html .= '<td>' . htmlspecialchars((string) $row[$count], ENT_QUOTES, 'UTF-8') . '</td>';
                        }
                        $html .= '</tr>';
                        $temp_data[] = $row;
                    }
                    $_SESSION['file_data'] = $temp_data;
                } else {
                    $error = 'Only <b>.csv</b> file allowed';
                }
            } else {
                $error = 'Please Select File';
            }

            $output = array(
                'error' => $error,
                'output' => $html,
                'columnMapping' => $columnMapping,
            );

            return response()->json($output);
        } else {
            return response()->json([
                'error' => 'Permission denied.',
                'output' => '',
                'columnMapping' => [],
            ]);
        }
    }

    public function fileImportModal()
    {
        if (Auth::user()->isAbleTo('attendance import')) {
            return view('hrm.attendance.import_modal');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function AttendanceImportdata(Request $request)
    {
        if (Auth::user()->isAbleTo('attendance import')) {
            session_start();
            $html = '<h5 class="text-danger text-center">Below data was not imported</h5></br>';
            $flag = 0;
            $html .= '<table class="table table-bordered"><tr><th>Employee ID</th><th>Date</th><th>Clock In</th><th>Clock Out</th><th>Reason</th></tr>';
            $file_data = $_SESSION['file_data'];
            $successCount = 0;

            foreach ($file_data as $key => $row) {
                $employeeIdValue = trim($row[$request->employee_id]);
                $dateValue = trim($row[$request->date]);
                $clockInValue = trim($row[$request->clock_in]);
                $clockOutValue = trim($row[$request->clock_out]);

                // Find employee by employee_id field in employees
                $employee = Employee::where('workspace_id', getActiveWorkspace())
                    ->where('employee_id', $employeeIdValue)
                    ->with('department')
                    ->first();

                $errorReason = '';

                if (empty($employee)) {
                    $errorReason = 'Employee not found';
                } else {
                    // Check if attendance already exists for this date
                    $existingAttendance = Attendance::where('employee_id', '=', $employee->id)
                        ->where('workspace', getActiveWorkspace())
                        ->where('date', '=', $dateValue)
                        ->first();

                    if (!empty($existingAttendance)) {
                        $errorReason = 'Attendance already exists for this date';
                    }
                }

                if (empty($errorReason)) {
                    try {
                        $employeeAttendance                = new Attendance();
                        $employeeAttendance->employee_id   = $employee->id;
                        $employeeAttendance->branch_id     = $employee->department ? $employee->department->branch_id : null;
                        $employeeAttendance->date          = $dateValue;
                        $employeeAttendance->status        = 'Present';
                        $employeeAttendance->clock_in      = $clockInValue . ':00';
                        $employeeAttendance->clock_out     = $clockOutValue . ':00';
                        $employeeAttendance->marked_by     = Attendance::MARKED_BY_HR;
                        $employeeAttendance->workspace     = getActiveWorkspace();
                        $employeeAttendance->created_by    = creatorId();
                        $employeeAttendance->save();
                        $successCount++;
                    } catch (\Exception $e) {
                        $flag = 1;
                        $html .= '<tr>';
                        $html .= '<td>' . htmlspecialchars((string) $employeeIdValue, ENT_QUOTES, 'UTF-8') . '</td>';
                        $html .= '<td>' . htmlspecialchars((string) $dateValue, ENT_QUOTES, 'UTF-8') . '</td>';
                        $html .= '<td>' . htmlspecialchars((string) $clockInValue, ENT_QUOTES, 'UTF-8') . '</td>';
                        $html .= '<td>' . htmlspecialchars((string) $clockOutValue, ENT_QUOTES, 'UTF-8') . '</td>';
                        $html .= '<td class="text-danger">' . htmlspecialchars((string) $e->getMessage(), ENT_QUOTES, 'UTF-8') . '</td>';
                        $html .= '</tr>';
                    }
                } else {
                    $flag = 1;
                    $html .= '<tr>';
                    $html .= '<td>' . htmlspecialchars((string) $employeeIdValue, ENT_QUOTES, 'UTF-8') . '</td>';
                    $html .= '<td>' . htmlspecialchars((string) $dateValue, ENT_QUOTES, 'UTF-8') . '</td>';
                    $html .= '<td>' . htmlspecialchars((string) $clockInValue, ENT_QUOTES, 'UTF-8') . '</td>';
                    $html .= '<td>' . htmlspecialchars((string) $clockOutValue, ENT_QUOTES, 'UTF-8') . '</td>';
                    $html .= '<td class="text-danger">' . htmlspecialchars((string) $errorReason, ENT_QUOTES, 'UTF-8') . '</td>';
                    $html .= '</tr>';
                }
            }

            $html .= '</table><br />';

            if ($flag == 1) {
                if ($successCount > 0) {
                    $html = '<div class="alert alert-success mb-3">' . $successCount . ' record(s) imported successfully.</div>' . $html;
                }
                return response()->json([
                    'html' => true,
                    'response' => $html,
                ]);
            } else {
                return response()->json([
                    'html' => false,
                    'response' => $successCount . ' record(s) imported successfully',
                ]);
            }
        } else {
            return response()->json([
                'html' => false,
                'response' => 'Permission denied.',
            ]);
        }
    }
}
