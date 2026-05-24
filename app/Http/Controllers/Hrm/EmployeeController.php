<?php

namespace App\Http\Controllers\Hrm;

use App\Models\WorkSpace;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Hrm\Employee;
use App\Models\Hrm\Department;
use App\Models\Hrm\Designation;
use App\Models\Hrm\Branch;
use App\Models\Hrm\Country;
use App\Models\Hrm\Province;
use App\Models\Hrm\EmployeeWorkingHour;
use Carbon\Carbon;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Hrm\PaySlip;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use App\Models\Hrm\EntitlementPolicy;
use App\Models\Hrm\EmployeeEntitlementPolicy;
use App\Models\Hrm\PayFrequency;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!Auth::user()->isAbleTo('employee manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $workspace = WorkSpace::find(Auth::user()->active_workspace);
        
        if (!$workspace) {
            return redirect()->back()->with('error', __('No active workspace found. Please select a workspace.'));
        }
        
        $perPage = $request->get('per_page', 10);
        $search  = $request->get('search');
        $status  = $request->get('status');

        $query = Employee::with(['designation', 'department'])
            ->where('workspace_id', $workspace->id);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $employees = $query->orderBy('id', 'desc')->paginate($perPage)->appends($request->query());

        return view('hrm.employees.index', compact('employees'));
    }

    /**
     * Get departments for a branch (AJAX)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDepartments(Request $request)
    {
        if (!Auth::user()->isAbleTo('employee manage')) {
            return response()->json([], 403);
        }

        $departments = Department::where('branch_id', $request->branch_id)
            ->where('workspace', getActiveWorkspace())
            ->get()
            ->pluck('name', 'id')
            ->toArray();

        return response()->json($departments);
    }

    /**
     * Get designations for a department (AJAX)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDesignations(Request $request)
    {
        if (!Auth::user()->isAbleTo('employee manage')) {
            return response()->json([], 403);
        }

        $designations = Designation::where('department_id', $request->department_id)
            ->where('workspace', getActiveWorkspace())
            ->get()
            ->pluck('name', 'id')
            ->toArray();

        return response()->json($designations);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->isAbleTo('employee create')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $employeeId = "EMP" . time();
        $today = Carbon::now()->format("Y-m-d");

        $departments = Department::where('created_by', creatorId())
            ->where('workspace', getActiveWorkspace())
            ->select('id', 'name')
            ->get();

        $designations = Designation::where('created_by', creatorId())
            ->where('workspace', getActiveWorkspace())
            ->select('id', 'name')
            ->get();

        $branches = Branch::where('created_by', creatorId())
            ->where('workspace', getActiveWorkspace())
            ->select('id', 'name')
            ->get();


        $countries = Country::where('status', 'Active')
            ->selectRaw('MIN(id) as id, name')
            ->groupBy('name')
            ->orderBy('name')
            ->get();

        $allCountries = Country::select('name')
            ->distinct()
            ->orderBy('name')
            ->get();
        $provinces = Province::where('status', 'Active')->get();

        return view('hrm.employees.create', compact('employeeId', 'departments', 'designations', 'today', 'provinces', 'countries', 'branches', 'allCountries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isAbleTo('employee create')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        try {
            // Check for active workspace first
            $workspace = WorkSpace::find(Auth::user()->active_workspace);
            if (!$workspace) {
                return redirect()->back()->with('error', __('No active workspace found. Please select a workspace.'))->withInput();
            }
            
            DB::beginTransaction();


            $validatedData = $request->validate([
                'profile_pic_path' => 'nullable|image',
                'employee_id' => 'required|unique:employees,employee_id',
                'salutation' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'phone_number' => [
                    'nullable',
                    Rule::unique('employees', 'phone_number')->where(function ($query) {
                        $query->where('workspace_id', getActiveWorkspace());
                    }),
                ],
                'department_id' => 'required|exists:departments,id',
                'designation_id' => 'required|exists:designations,id',
                //'email' => 'required|email|unique:employees,email',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('employees', 'email')
                        ->where(function ($query) use ($request) {
                            return $query->where('workspace_id', $request->workspace_id);
                        }),
                ],
                'date_of_birth' => ['required', 'date', 'before_or_equal:' . now()->subYears(18)->format('Y-m-d')],
                'flat_no' => 'nullable',
                'pincode' => 'nullable',
                'street' => 'nullable',
                'city' => 'nullable',
                'state' => 'nullable',
                'country' => 'nullable',
                // 'emergency_contact_name.*' => 'required',
                // 'emergency_contact_phone.*' => 'required',
                'bank' => 'required',
                //'account_number' => 'required',
                'account_number' => [
                    'required',
                    Rule::unique('employees', 'account_number')
                        ->where(function ($query) {
                            $query->where('workspace_id', getActiveWorkspace());
                        }),
                ],
                'branch_name' => 'required',
                'branch_code' => 'required',
                'account_type' => 'required',
                'holder_relationship' => 'required',
                'pay_frequency' => 'required|exists:add_pay_frequencies,id',
                'date_of_appointment' => 'required|date',
                'identification_type' => 'required',
                //'id_number' => 'required',
                'id_number' => [
                    'required',
                    Rule::unique('employees', 'id_number')
                        ->where(function ($query) {
                            $query->where('workspace_id', getActiveWorkspace());
                        }),

                ],
                'tax_reference_number' => 'nullable',
                'passport_country' => 'nullable',
                // Attendance fields
                'branch_id' => 'required|exists:branches,id',
                'attendance_enabled' => 'nullable|boolean',
                'use_custom_working_hours' => 'nullable|boolean',
            ]);

            $data = $request->all();


            if ($request->hasFile('profile_pic_path')) {
                $file = $request->file('profile_pic_path');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/users/profile-pics'), $filename);
                $data['profile_pic_path'] = 'users/profile-pics/' . $filename;
            }


            if ($request->emergency_contact_name) {
                $data['emergency_contact_name'] = implode(',', $request->emergency_contact_name);
                $data['emergency_contact_phone'] = implode(',', $request->emergency_contact_phone);
            }
            $data['workspace_id'] = $workspace->id;

            // Only allow safe fields to be mass assigned
            $safeFields = [
                'employee_id', 'salutation', 'first_name', 'last_name', 'phone_number',
                'department_id', 'designation_id', 'email', 'date_of_birth',
                'flat_no', 'pincode', 'street', 'city', 'state', 'country',
                'emergency_contact_name', 'emergency_contact_phone',
                'bank', 'account_number', 'branch_name', 'branch_code', 'account_type',
                'holder_relationship', 'pay_frequency', 'date_of_appointment',
                'identification_type', 'id_number', 'tax_reference_number', 'passport_country',
                'branch_id', 'attendance_enabled', 'use_custom_working_hours',
                'profile_pic_path', 'workspace_id',
            ];
            $employeeData = array_intersect_key($data, array_flip($safeFields));
            $employee = new Employee();
            $employee->fill($employeeData);
            $employee->workspace_id = $workspace->id;
            $employee->save();

            // Create custom working hours if enabled
            if ($request->use_custom_working_hours && $request->has('working_hours')) {
                foreach ($request->working_hours as $hourData) {
                    EmployeeWorkingHour::create([
                        'employee_id' => $employee->id,
                        'day' => $hourData['day'],
                        'is_working_day' => !empty($hourData['is_working_day']) ? 1 : 0,
                        'start_time' => $hourData['start_time'] ?? '08:00:00',
                        'end_time' => $hourData['end_time'] ?? '17:00:00',
                        'lunch_start_time' => !empty($hourData['lunch_start_time']) ? $hourData['lunch_start_time'] : null,
                        'lunch_end_time' => !empty($hourData['lunch_end_time']) ? $hourData['lunch_end_time'] : null,
                    ]);
                }
            }

            $payslip = $this->createPayslip($employee->id);
            if (!$payslip) {
                throw new Exception("Failed to create payslip for the employee.");
            }

            // Assign all existing entitlement policies to the new employee
            $this->assignEntitlementPoliciesToEmployee($employee);

            DB::commit();

            return redirect()->route('employees.list')->with('success', 'Employee profile created successfully!');
        } catch (Exception $e) {

            DB::rollBack();


            Log::error('Employee Profile Store Error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Error:' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        if (!Auth::user()->isAbleTo('employee edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $employee = Employee::where('id', $id)->where('workspace_id', getActiveWorkspace())->firstOrFail();
        $today = Carbon::now()->format("Y-m-d");
        //$departments = Department::select('id', 'name')->get();
        //$branches = Branch::all();
        //$designations = Designation::where('department_id', $employee->department_id)->get();
        $departments = Department::where('created_by', creatorId())
            ->where('workspace', getActiveWorkspace())
            ->select('id', 'name')
            ->get();

        $designations = Designation::where('created_by', creatorId())
            ->where('workspace', getActiveWorkspace())
            ->select('id', 'name')
            ->get();

        $branches = Branch::where('created_by', creatorId())
            ->where('workspace', getActiveWorkspace())
            ->select('id', 'name')
            ->get();
        $countries = Country::where('status', 'Active')
            ->selectRaw('MIN(id) as id, name')
            ->groupBy('name')
            ->orderBy('name')
            ->get();

        $allCountries = Country::select('name')
            ->distinct()
            ->orderBy('name')
            ->get();
        $country = Country::where('name', $employee->country)->first();
        if (!$country) {
            $provinces = [];
        } else {
            $provinces = Province::where('country_id', $country->id)->get();
        }

        $names = explode(',', $employee->emergency_contact_name ?? '');
        $numbers = explode(',', $employee->emergency_contact_phone);
        $emergencyContacts = [];

        foreach ($names as $index => $name) {
            $emergencyContacts[] = [
                'name' => $name,
                'number' => $numbers[$index] ?? '',
            ];
        }
        return view('hrm.employees.edit', compact("employee", "departments", "designations", "today", "emergencyContacts", 'provinces', 'countries', 'branches','allCountries'));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $employee = Employee::findOrFail($id);
        try {

            DB::beginTransaction();


            $validator = Validator::make($request->all(), [
                'profile_pic_path' => 'nullable|image',
                'employee_id' => 'required|unique:employees,employee_id,' . $id,
                'salutation' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                //'phone_number' => 'nullable',
                'phone_number' => [
                    'nullable',
                    Rule::unique('employees', 'phone_number')
                        ->where(fn($query) => $query->where('workspace_id', getActiveWorkspace()))
                        ->ignore($employee->id),
                ],
                'department_id' => 'required|exists:departments,id',
                'designation_id' => 'required|exists:designations,id',
                //'date_of_birth' => 'required|date|before_or_equal:today',
                //'email' => 'required|email|unique:employees,email',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('employees', 'email')
                        ->where(fn($query) => $query->where('workspace_id', getActiveWorkspace()))
                        ->ignore($employee->id),
                ],
                'date_of_birth' => ['required', 'date', 'before_or_equal:' . now()->subYears(18)->format('Y-m-d')],
                'flat_no' => 'nullable',
                'pincode' => 'nullable',
                'street' => 'nullable',
                'city' => 'nullable',
                'state' => 'nullable',
                'country' => 'nullable',
                'bank' => 'required',
                // 'account_number' => 'required',
                'account_number' => [
                    'required',
                    Rule::unique('employees', 'account_number')
                        ->where(function ($query) {
                            $query->where('workspace_id', getActiveWorkspace());
                        })
                        ->ignore($employee->id),
                ],
                'branch_name' => 'required',
                'branch_code' => 'required',
                'account_type' => 'required',
                'holder_relationship' => 'required',
                'pay_frequency' => 'required|exists:add_pay_frequencies,id',
                'date_of_appointment' => 'required|date',
                'identification_type' => 'required',
                'id_number' => [
                    'required',
                    Rule::unique('employees', 'id_number')
                        ->where(function ($query) {
                            $query->where('workspace_id', getActiveWorkspace());
                        })
                        ->ignore($employee->id),
                ],
                'tax_reference_number' => 'nullable',
                'passport_country' => 'nullable',
                // Attendance fields
                'branch_id' => 'required|exists:branches,id',
                'attendance_enabled' => 'nullable|boolean',
                'use_custom_working_hours' => 'nullable|boolean',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422); // This status code is **critical**
            }
            $data = $request->all();
            
            // Handle checkbox fields that might not be sent when unchecked
            $data['attendance_enabled'] = $request->has('attendance_enabled') ? 1 : 0;
            $data['use_custom_working_hours'] = $request->has('use_custom_working_hours') ? 1 : 0;
            if ($request->hasFile('profile_pic_path')) {
                $file = $request->file('profile_pic_path');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/users/profile-pics'), $filename);
                $data['profile_pic_path'] = 'users/profile-pics/' . $filename;
            }
            if ($request->emergency_contact_name) {
                $data['emergency_contact_name'] = implode(',', $request->emergency_contact_name);
                $data['emergency_contact_phone'] = implode(',', $request->emergency_contact_phone);
            }

            $employee->update($data);

            DB::commit();

            return redirect()->route('employees.list')->with('success', 'Employee profile updated successfully!');
        } catch (Exception $e) {

            DB::rollBack();


            Log::error('Employee Profile Store Error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Error:' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (!Auth::user()->isAbleTo('employee delete')) {
            return redirect()->route('employees.list')->with('error', __('Permission denied.'));
        }

        // Workspace-scoped lookup
        $employee = Employee::where('id', $id)->where('workspace_id', getActiveWorkspace())->first();

        if (!$employee) {
            return redirect()->route('employees.list')->with('error', 'Employee profile not found!');
        }


        $hasPayslips = \App\Models\Hrm\PaySlip::where('employee_id', $id)->exists();

        if ($hasPayslips) {
            return redirect()->route('employees.list')->with('error', 'Cannot delete employee because payslips exist for this employee.');
        }

        $employee->delete();

        return redirect()->route('employees.list')->with('success', 'Employee profile deleted successfully!');
    }

    public function updateStatus(Request $request, string $id)
    {
        if (!Auth::user()->isAbleTo('employee edit')) {
            return response()->json([
                'status' => false,
                'message' => __('Permission denied.'),
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:Active,Inactive',
        ]);

        // Workspace-scoped lookup
        $employee = Employee::where('id', $id)->where('workspace_id', getActiveWorkspace())->first();

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found!',
            ], 404);
        }

        $employee->status = $request->status;
        $employee->save();

        return response()->json([
            'status' => true,
            'newStatus' => $employee->status,
            'message' => 'Employee status updated successfully!',
        ]);
    }
    public function getProvinces($country)
    {
        $countryIds = collect();

        if (is_numeric($country)) {
            $countryId = (int) $country;
            $countryIds = Country::where('id', $countryId)->pluck('id');

            $hasDirectProvinces = Province::whereIn('country_id', $countryIds)
                ->where('status', 'Active')
                ->exists();

            if (!$hasDirectProvinces) {
                $countryName = Country::where('id', $countryId)->value('name');
                if (!empty($countryName)) {
                    $countryIds = Country::where('name', $countryName)->pluck('id');
                }
            }
        } else {
            $countryName = trim((string) $country);
            $countryIds = Country::where('name', $countryName)->pluck('id');
        }

        if ($countryIds->isEmpty()) {
            return response()->json([]);
        }

        $provinces = Province::whereIn('country_id', $countryIds)
            ->where('status', 'Active')
            ->selectRaw('MIN(id) as id, name')
            ->groupBy('name')
            ->orderBy('name')
            ->pluck('name', 'id');

        return response()->json($provinces);
    }
    public function createPayslip($id)
    {
        $employeeId = $id;

        if (!$employeeId) {
            return redirect()->back()->with('error', 'Employee ID is missing.');
        }

        $employee = Employee::find($employeeId);
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found.');
        }

        // Calculate first payslip date based on employee's pay frequency
        $payFrequency = $employee->pay_frequency ? PayFrequency::find($employee->pay_frequency) : null;
        $formate_month_year = $this->calculateFirstPayslipDate($payFrequency, $employee->date_of_appointment);
        
        $newPayslipEmployee = new PaySlip();
        $newPayslipEmployee->employee_id          = $employee->id;
        $newPayslipEmployee->net_payble           = 0;
        $newPayslipEmployee->salary_month         = $formate_month_year;
        $newPayslipEmployee->status               = 0;
        $newPayslipEmployee->basic_salary         = 0;
        $newPayslipEmployee->allowance            = 0;
        $newPayslipEmployee->commission           = 0;
        $newPayslipEmployee->loan                 = 0;
        $newPayslipEmployee->saturation_deduction = 0;
        $newPayslipEmployee->other_payment        = 0;
        $newPayslipEmployee->overtime             = 0;
        $newPayslipEmployee->company_contribution = 0;
        $newPayslipEmployee->workspace            = getActiveWorkspace();
        $newPayslipEmployee->created_by           = creatorId();
        $newPayslipEmployee->save();
        return $newPayslipEmployee;
    }

    /**
     * Calculate the first payslip date based on employee's pay frequency and appointment date
     *
     * @param PayFrequency|null $payFrequency
     * @param string $appointmentDate
     * @return string Date in Y-m-d format
     */
    private function calculateFirstPayslipDate($payFrequency, $appointmentDate)
    {
        $startDate = Carbon::parse($appointmentDate);

        // Default to monthly (end of month) if no pay frequency set
        if (!$payFrequency) {
            return $startDate->endOfMonth()->format('Y-m-d');
        }

        $frequencyType = strtolower($payFrequency->pay_frequency);

        // DAILY - first payslip is appointment date itself
        if (str_contains($frequencyType, 'daily')) {
            return $startDate->format('Y-m-d');
        }

        // WEEKLY - find the next week-ending day AFTER appointment (not including appointment day)
        if (str_contains($frequencyType, 'weekly') && !str_contains($frequencyType, 'fortnightly')) {
            $weekEndDay = $payFrequency->last_day_of_period ?? 'Sunday';
            $dayOfWeek = $this->getDayOfWeekNumber($weekEndDay);

            $nextDate = $startDate->copy();
            // Always move to NEXT week-end day (employee needs to work at least some days first)
            do {
                $nextDate->addDay();
            } while ($nextDate->dayOfWeek !== $dayOfWeek);
            return $nextDate->format('Y-m-d');
        }

        // FORTNIGHTLY - find next fortnight end date after appointment
        if (str_contains($frequencyType, 'fortnightly') || str_contains($frequencyType, 'two weeks')) {
            if ($payFrequency->biweekly_date) {
                $anchorDate = Carbon::parse($payFrequency->biweekly_date);
                $nextDate = $anchorDate->copy();
                
                // Move forward in 14-day increments until we're at or past appointment date
                while ($nextDate->lt($startDate)) {
                    $nextDate->addDays(14);
                }
                return $nextDate->format('Y-m-d');
            }
            
            // Fallback: use last_day_of_period
            $weekEndDay = $payFrequency->last_day_of_period ?? 'Friday';
            $dayOfWeek = $this->getDayOfWeekNumber($weekEndDay);
            $nextDate = $startDate->copy();
            while ($nextDate->dayOfWeek !== $dayOfWeek) {
                $nextDate->addDay();
            }
            return $nextDate->format('Y-m-d');
        }

        // MONTHLY - use specific day or end of month
        $payDay = $payFrequency->last_day_of_month ?? null;
        
        if ($payDay) {
            $maxDay = $startDate->daysInMonth;
            $actualPayDay = min($payDay, $maxDay);
            $payDateThisMonth = $startDate->copy()->setDay($actualPayDay);
            
            // If payday has passed in appointment month, use next month
            if ($payDateThisMonth->lt($startDate)) {
                $nextMonth = $startDate->copy()->addMonthsNoOverflow();
                return $nextMonth->setDay(min($payDay, $nextMonth->daysInMonth))->format('Y-m-d');
            }
            return $payDateThisMonth->format('Y-m-d');
        }
        
        return $startDate->endOfMonth()->format('Y-m-d');
    }

    /**
     * Convert day name to Carbon day of week number
     *
     * @param string $dayName
     * @return int (0=Sunday, 1=Monday, ... 6=Saturday)
     */
    private function getDayOfWeekNumber($dayName)
    {
        $days = [
            'sunday' => Carbon::SUNDAY,
            'monday' => Carbon::MONDAY,
            'tuesday' => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'thursday' => Carbon::THURSDAY,
            'friday' => Carbon::FRIDAY,
            'saturday' => Carbon::SATURDAY,
        ];
        return $days[strtolower($dayName)] ?? Carbon::SUNDAY;
    }
    public function ajaxValidate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_pic_path' => 'nullable|image',
            'employee_id' => 'required|unique:employees,employee_id',
            'salutation' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'date_of_birth' => 'required|date|before_or_equal:today',
            'email' => 'required|email|unique:employees,email',
            'branch_id' => 'required|exists:branches,id',
            'bank' => 'required',
            'account_number' => [
                'required',
                Rule::unique('employees', 'account_number')->where(function ($query) {
                    return $query->where('workspace_id', getActiveWorkspace());
                }),
            ],
            'branch_code' => 'required',
            'account_type' => 'required',
            'holder_relationship' => 'required',
            'pay_frequency' => 'required|exists:add_pay_frequencies,id',
            'date_of_appointment' => 'required|date',
            'identification_type' => 'required',
            'id_number' => [
                'required',
                Rule::unique('employees', 'id_number')->where(function ($query) {
                    return $query->where('workspace_id', getActiveWorkspace());
                }),
            ],
            'tax_reference_number' => 'nullable',
            'passport_country' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }
    public function grid(Request $request)
    {
        if (!Auth::user()->isAbleTo('employee manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        try {
            $search  = $request->get('search');
            $status  = $request->get('status');

            $query = Employee::where('workspace_id', getActiveWorkspace());

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone_number', 'like', "%{$search}%");
                });
            }

            if ($status) {
                $query->where('status', $status);
            }

            $employees = $query->orderBy('id', 'desc')->get();

            return view('hrm.employees.grid', compact('employees'));
        } catch (\Exception $e) {
            Log::error('Employee Grid Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    public function show($id)
    {
        try {
            $employeeId = Crypt::decrypt($id);
            $employee = Employee::where('workspace_id', getActiveWorkspace())
                ->with(['department', 'designation', 'branch', 'payFrequency', 'basicSalary', 'workingHours'])
                ->findOrFail($employeeId);

            $payslips = PaySlip::where('employee_id', $employee->id)
                ->where('workspace', getActiveWorkspace())
                ->orderBy('id', 'desc')
                ->paginate(10);

            // Preserve the active tab when paginating
            $payslips->appends(['tab' => 'payslip']);

            return view('hrm.employees.show', compact('employee', 'payslips'));
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return redirect()->route('employees.grid')
                ->with('error', 'Invalid employee ID.');
        } catch (\Exception $e) {
            Log::error('Employee Show Error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return redirect()->route('employees.grid')
                ->with('error', 'Error loading employee: ' . $e->getMessage());
        }
    }

    /**
     * Assign all existing entitlement policies to a newly created employee.
     * This ensures new employees inherit all leave entitlements configured for the workspace.
     *
     * @param Employee $employee
     * @return void
     */
    private function assignEntitlementPoliciesToEmployee(Employee $employee): void
    {
        // Get all entitlement policies for the workspace
        $entitlementPolicies = EntitlementPolicy::whereHas('leaveManagement', function ($query) use ($employee) {
            $query->where('workspace_id', $employee->workspace_id);
        })->get();

        foreach ($entitlementPolicies as $policy) {
            // Check if the employee already has this entitlement policy assigned
            $existingPolicy = EmployeeEntitlementPolicy::where([
                'employee_id' => $employee->id,
                'leave_management_id' => $policy->leave_management_id,
            ])->first();

            // Only create if not already assigned
            if (!$existingPolicy) {
                EmployeeEntitlementPolicy::create([
                    'employee_id' => $employee->id,
                    'leave_management_id' => $policy->leave_management_id,
                    'entitlement_id' => $policy->id,
                    'default_entitlement' => $policy->default_entitlement,
                    'workspace' => $employee->workspace_id,
                    'created_by' => creatorId(),
                ]);
            }
        }
    }

    /**
     * Show working hours configuration for an employee
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function workingHours($id)
    {
        $employee = Employee::findOrFail($id);
        
        if ($employee->workspace_id != getActiveWorkspace()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $workingHours = EmployeeWorkingHour::where('employee_id', $employee->id)
            ->orderByRaw("FIELD(day, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->get()
            ->keyBy('day');
        
        // Ensure all days exist
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        foreach ($days as $day) {
            if (!isset($workingHours[$day])) {
                // Create default from branch if available, or use standard defaults
                if ($employee->branch_id && $employee->branch) {
                    $branchHours = $employee->branch->getWorkingHoursForDay($day);
                    if ($branchHours) {
                        $workingHours[$day] = EmployeeWorkingHour::create([
                            'employee_id' => $employee->id,
                            'day' => $day,
                            'is_working_day' => $branchHours->is_working_day,
                            'start_time' => $branchHours->start_time,
                            'end_time' => $branchHours->end_time,
                            'lunch_start_time' => $branchHours->lunch_start_time,
                            'lunch_end_time' => $branchHours->lunch_end_time,
                        ]);
                        continue;
                    }
                }
                
                // Use defaults
                $workingHours[$day] = EmployeeWorkingHour::create([
                    'employee_id' => $employee->id,
                    'day' => $day,
                    'is_working_day' => in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
                    'start_time' => '08:00:00',
                    'end_time' => '17:00:00',
                ]);
            }
        }

        return view('hrm.employees.working-hours', compact('employee', 'workingHours'));
    }

    /**
     * Update working hours for an employee
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateWorkingHours(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        
        if ($employee->workspace_id != getActiveWorkspace()) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $validator = Validator::make($request->all(), [
            'working_hours' => 'required|array',
            'working_hours.*.day' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'working_hours.*.is_working_day' => 'required|boolean',
            'working_hours.*.start_time' => 'nullable|date_format:H:i',
            'working_hours.*.end_time' => 'nullable|date_format:H:i|after:working_hours.*.start_time',
            'working_hours.*.lunch_start_time' => 'nullable|date_format:H:i',
            'working_hours.*.lunch_end_time' => 'nullable|date_format:H:i|after:working_hours.*.lunch_start_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($request->working_hours as $data) {
                EmployeeWorkingHour::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'day' => $data['day'],
                    ],
                    [
                        'is_working_day' => $data['is_working_day'],
                        'start_time' => $data['is_working_day'] ? $data['start_time'] : null,
                        'end_time' => $data['is_working_day'] ? $data['end_time'] : null,
                        'lunch_start_time' => $data['is_working_day'] ? ($data['lunch_start_time'] ?? null) : null,
                        'lunch_end_time' => $data['is_working_day'] ? ($data['lunch_end_time'] ?? null) : null,
                    ]
                );
            }

            // Enable custom working hours flag
            $employee->use_custom_working_hours = true;
            $employee->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Working hours updated successfully.'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => __('Error updating working hours: ') . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Copy working hours from branch to employee
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function copyWorkingHoursFromBranch($id)
    {
        $employee = Employee::findOrFail($id);
        
        if ($employee->workspace_id != getActiveWorkspace()) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        if (!$employee->branch_id) {
            return response()->json([
                'success' => false,
                'message' => __('Employee is not assigned to a branch.'),
            ], 400);
        }

        DB::beginTransaction();
        try {
            EmployeeWorkingHour::copyFromBranch($employee->id, $employee->branch_id);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Working hours copied from branch successfully.'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => __('Error copying working hours: ') . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate Experience Certificate PDF
     * 
     * @param int $id Employee ID
     * @return \Illuminate\View\View
     */
    public function ExpCertificatePdf($id)
    {
        $currantLang = getActiveLanguage();
        $expCertificate = \App\Models\Hrm\ExperienceCertificate::where('lang', $currantLang)
            ->where('created_by', creatorId())
            ->where('workspace', getActiveWorkspace())
            ->first();

        $employee = Employee::where('employee_id', $id)
            ->where('created_by', creatorId())
            ->where('workspace_id', getActiveWorkspace())
            ->first();

        if (!$employee) {
            return redirect()->back()->with('error', __('Employee not found.'));
        }

        $date = date('Y-m-d');
        $obj = [
            'date' => companyDateFormate($date),
            'app_name' => env('APP_NAME'),
            'employee_name' => $employee->full_name,
            'designation' => !empty($employee->designation->name) ? $employee->designation->name : '',
        ];

        if ($expCertificate) {
            $expCertificate->content = \App\Models\Hrm\ExperienceCertificate::replaceVariable($expCertificate->content, $obj);
        }

        return view('hrm.employees.template.expcertificatepdf', compact('expCertificate', 'employee'));
    }

    /**
     * Generate Experience Certificate DOC
     * 
     * @param int $id Employee ID
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function ExpCertificateDoc($id)
    {
        $currantLang = getActiveLanguage();
        $expCertificate = \App\Models\Hrm\ExperienceCertificate::where('lang', $currantLang)
            ->where('created_by', creatorId())
            ->where('workspace', getActiveWorkspace())
            ->first();

        $employee = Employee::where('employee_id', $id)
            ->where('created_by', creatorId())
            ->where('workspace_id', getActiveWorkspace())
            ->first();

        if (!$employee) {
            return redirect()->back()->with('error', __('Employee not found.'));
        }

        $date = date('Y-m-d');
        $obj = [
            'date' => companyDateFormate($date),
            'app_name' => env('APP_NAME'),
            'employee_name' => $employee->full_name,
            'designation' => !empty($employee->designation->name) ? $employee->designation->name : '',
        ];

        if ($expCertificate) {
            $expCertificate->content = \App\Models\Hrm\ExperienceCertificate::replaceVariable($expCertificate->content, $obj);
        }

        return response()->streamDownload(function () use ($expCertificate) {
            echo $expCertificate ? $expCertificate->content : '';
        }, 'experience_certificate.doc', [
            'Content-Type' => 'application/msword',
        ]);
    }

    /**
     * Generate NOC PDF
     * 
     * @param int $id Employee ID
     * @return \Illuminate\View\View
     */
    public function NocPdf($id)
    {
        $currantLang = getActiveLanguage();
        $noc = \App\Models\Hrm\NOC::where('lang', $currantLang)
            ->where('created_by', creatorId())
            ->where('workspace', getActiveWorkspace())
            ->first();

        $employee = Employee::where('employee_id', $id)
            ->where('created_by', creatorId())
            ->where('workspace_id', getActiveWorkspace())
            ->first();

        if (!$employee) {
            return redirect()->back()->with('error', __('Employee not found.'));
        }

        $date = date('Y-m-d');
        $obj = [
            'date' => companyDateFormate($date),
            'app_name' => env('APP_NAME'),
            'employee_name' => $employee->full_name,
            'designation' => !empty($employee->designation->name) ? $employee->designation->name : '',
        ];

        if ($noc) {
            $noc->content = \App\Models\Hrm\NOC::replaceVariable($noc->content, $obj);
        }

        return view('hrm.employees.template.nocpdf', compact('noc', 'employee'));
    }

    /**
     * Generate NOC DOC
     * 
     * @param int $id Employee ID
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function NocDoc($id)
    {
        $currantLang = getActiveLanguage();
        $noc = \App\Models\Hrm\NOC::where('lang', $currantLang)
            ->where('created_by', creatorId())
            ->where('workspace', getActiveWorkspace())
            ->first();

        $employee = Employee::where('employee_id', $id)
            ->where('created_by', creatorId())
            ->where('workspace_id', getActiveWorkspace())
            ->first();

        if (!$employee) {
            return redirect()->back()->with('error', __('Employee not found.'));
        }

        $date = date('Y-m-d');
        $obj = [
            'date' => companyDateFormate($date),
            'app_name' => env('APP_NAME'),
            'employee_name' => $employee->full_name,
            'designation' => !empty($employee->designation->name) ? $employee->designation->name : '',
        ];

        if ($noc) {
            $noc->content = \App\Models\Hrm\NOC::replaceVariable($noc->content, $obj);
        }

        return response()->streamDownload(function () use ($noc) {
            echo $noc ? $noc->content : '';
        }, 'noc.doc', [
            'Content-Type' => 'application/msword',
        ]);
    }
}
