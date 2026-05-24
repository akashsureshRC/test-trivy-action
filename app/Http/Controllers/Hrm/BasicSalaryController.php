<?php

namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\BasicSalaryHour;
use App\Models\Hrm\Employee;
use App\Models\Hrm\BasicSalary;
use App\Models\Hrm\Payroll;
use App\Services\AttendanceCalculationService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class BasicSalaryController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
{

    $employeeId = request()->employee_id;

    $basicSalary = BasicSalary::where('employee_id', $employeeId)->first();


    if (!$basicSalary) {
        return redirect()->route('basic-salariess.create', ['employee_id' => $employeeId])
                         ->with('error', 'Basic salary not found. Please add one.');
    }


    return view('payroll.index', compact('basicSalary'));
}

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
{
    $employeeId = $request->employee_id;
    $term = $request->term;

    if (!$employeeId) {
        return redirect()->route('payroll.index')->with('error', 'Employee ID is required.');
    }


    $employee = \App\Models\Hrm\Employee::find($employeeId);

    if (!$employee) {
        return redirect()->route('payroll.regularinputs')->with('error', 'Employee not found.');
    }

    return view('hrm.basic-salariess.create', compact('employee','term'));
}



public function store(Request $request)
{
    // Validate input data
    $validated = $request->validate([
        'employee_id'  => 'required|exists:employees,id',
        'fixed_salary' => 'nullable|numeric|min:0',
        'hourly_rate' => 'nullable|numeric|min:0',
    ]);

    DB::beginTransaction();
    try {
        // Create the BasicSalary record without any pre-checks
        $basicSalary = BasicSalary::create([
            'employee_id' => $request->employee_id,
            'fixed_salary' => $request->fixed_salary ? $request->fixed_salary : 0,
            'hourly_paid' =>$request->hourly_paid ? $request->hourly_paid : 0,
            'hourly_rate' =>$request->hourly_rate ? $request->hourly_rate : 0,
            'dont_auto_pay_public_holidays' =>$request->dont_auto_pay_public_holidays ? $request->dont_auto_pay_public_holidays : 0,
            'paid_for_additional_hours' =>$request->paid_for_additional_hours ? $request->paid_for_additional_hours : 0,
            'override_hourly_rate' =>$request->override_hourly_rate ? $request->override_hourly_rate : 0,
            'rate_override' =>$request->rate_override ? $request->rate_override : 0,
            'term' => $request->term
        ]);

        DB::commit();

        // If hourly paid, redirect to hourly hours submission form
        if ($request->hourly_paid) {
            return redirect()->route('basic-salariess.hourlyPay', ['id' => $basicSalary->id, 'term' => $request->term])
                             ->with('success', 'Basic salary added successfully. Please enter the hours worked.');
        }

        // Redirect to payroll.index with success message and pass employee_id
        return redirect()->route('payroll.index', ['employee_id' => $validated['employee_id'],'term' => $request->term,])
                         ->with('success', 'Basic salary added successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->route('basic-salariess.create', ['employee_id' => $validated['employee_id']])
                         ->with('error', 'An error occurred: ' . $e->getMessage());
    }
}
public function edit($employeeId)
{
    $term = request('term');
    $basicSalary = BasicSalary::where('employee_id', $employeeId)->where('term', $term)->firstOrFail();
    $employee = Employee::findOrFail($employeeId);
    return view('hrm.basic-salariess.edit', compact('basicSalary', 'employee','employeeId','term'));
}

public function update(Request $request, $basicSalaryId)
{

    $request->validate([
        'fixed_salary' => 'required|numeric|min:0',
    ]);

    $basicSalary = BasicSalary::findOrFail($basicSalaryId);
    $basicSalary->update([
        'fixed_salary' => $request->fixed_salary ? $request->fixed_salary : 0,
        'hourly_paid' =>$request->hourly_paid ? $request->hourly_paid : 0,
        'hourly_rate' =>$request->hourly_rate ? $request->hourly_rate : 0,
        'dont_auto_pay_public_holidays' =>$request->dont_auto_pay_public_holidays ? $request->dont_auto_pay_public_holidays : 0,
        'paid_for_additional_hours' =>$request->paid_for_additional_hours ? $request->paid_for_additional_hours : 0,
        'override_hourly_rate' =>$request->override_hourly_rate ? $request->override_hourly_rate : 0,
        'rate_override' =>$request->rate_override ? $request->rate_override : 0,
    ]);


    Payroll::where('employee_id', $basicSalary->employee_id)
        ->update(['basic_salary' => $request->input('fixed_salary')]);

    // If hourly paid, redirect to hourly hours submission form
    if ($request->hourly_paid) {
        return redirect()->route('basic-salariess.hourlyPay', ['id' => $basicSalary->id, 'term' => $request->input('term')])
                         ->with('success', 'Basic Salary updated. Please enter the hours worked.');
    }

    return redirect()->route('payroll.index', ['employee_id' => $basicSalary->employee_id,'term' => $request->input('term')])
        ->with('success', 'Basic Salary updated successfully, and Payroll table updated!');
}

    private function calculatePayroll($basicSalary)
    {

        $uiF = $basicSalary->fixed_salary * 0.01;
        $payTax = $basicSalary->fixed_salary * 0.15;
        $netPay = $basicSalary->fixed_salary - ($uiF + $payTax);


        Payroll::updateOrCreate(
            ['employee_id' => $basicSalary->employee_id],
            [
                'basic_salary' => $basicSalary->fixed_salary,
                'uif_amount' => $uiF,
                'tax_pay' => $payTax,
                'net_pay' => $netPay
            ]
        );
    }




    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */


    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('basic-salariess.show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    //public function edit(BasicSalary $basic_salariess)
    //{
       // return view('hrm.basic-salariess.edit', compact('basic_salariess'));
  //  }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param BasicSalary $salary
     * @return Renderable
     */

    /**
     * Remove the specified resource from storage.
     * @param BasicSalary $salary
     * @return Renderable
     */
    public function destroy(BasicSalary $basicSalary)
    {
        $basicSalary->delete();
        return redirect()->route('basic-salariess.create')
                         ->with('success', 'Basic salary record deleted successfully.');
    }
    public function hourlyPay($id, $term){
        $basicSalary = BasicSalary::find($id);
        $basicSalaryHour = BasicSalaryHour::where('employee_id', $basicSalary->employee_id)->where('term', $term)->first();
        
        if ($basicSalaryHour) {
            $basicSalary->normal_hour_value = $basicSalaryHour->normal_hours;
            $basicSalary->ot_hour_value = $basicSalaryHour->ot_hours;
        } else {
            $basicSalary->normal_hour_value = 0;
            $basicSalary->ot_hour_value = 0;
        }

        // Calculate hours from attendance records
        $attendanceHours = $this->calculateAttendanceHours($basicSalary->employee_id, $term);
        
        return view('hrm.basic-salariess.hourly-pay', compact('basicSalary', 'term', 'attendanceHours'));
    }

    /**
     * Calculate hours from attendance records for a specific term.
     * 
     * @param int $employeeId
     * @param string $term Format: YYYY-MM (monthly) or YYYY-MM-DD (weekly/daily)
     * @return array
     */
    private function calculateAttendanceHours($employeeId, $term)
    {
        // Get employee profile
        $profile = Employee::find($employeeId);
        
        if (!$profile || !$profile->attendance_enabled) {
            return [
                'available' => false,
                'normal_hours' => 0,
                'overtime_hours' => 0,
                'total_hours' => 0,
                'message' => 'Attendance tracking is not enabled for this employee.'
            ];
        }

        // Parse the term to get date range - supports both Y-m (monthly) and Y-m-d (weekly/daily) formats
        try {
            // Try full date format first (Y-m-d for weekly/daily)
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $term)) {
                $termDate = Carbon::parse($term);
                
                // Get pay frequency to determine the period type
                $payFrequency = $profile->pay_frequency ? \App\Models\Hrm\PayFrequency::find($profile->pay_frequency) : null;
                
                if ($payFrequency) {
                    $frequencyType = strtolower($payFrequency->pay_frequency);
                    
                    // DAILY - single day
                    if (str_contains($frequencyType, 'daily')) {
                        $startDate = $termDate->copy()->startOfDay();
                        $endDate = $termDate->copy()->endOfDay();
                    }
                    // WEEKLY - 7 days ending on term date
                    elseif (str_contains($frequencyType, 'weekly') && !str_contains($frequencyType, 'fortnightly')) {
                        $endDate = $termDate->copy();
                        $startDate = $termDate->copy()->subDays(6); // 7-day period ending on term date
                    }
                    // FORTNIGHTLY - 14 days ending on term date
                    elseif (str_contains($frequencyType, 'fortnightly') || str_contains($frequencyType, 'two weeks')) {
                        $endDate = $termDate->copy();
                        $startDate = $termDate->copy()->subDays(13); // 14-day period ending on term date
                    }
                    // MONTHLY - use the month of the term date
                    else {
                        $startDate = $termDate->copy()->startOfMonth();
                        $endDate = $termDate->copy()->endOfMonth();
                    }
                } else {
                    // No pay frequency set - default to week ending on term date
                    $endDate = $termDate->copy();
                    $startDate = $termDate->copy()->subDays(6);
                }
            }
            // Try Y-m format (monthly)
            elseif (preg_match('/^\d{4}-\d{2}$/', $term)) {
                $termDate = Carbon::createFromFormat('Y-m', $term);
                $startDate = $termDate->copy()->startOfMonth();
                $endDate = $termDate->copy()->endOfMonth();
            }
            else {
                throw new \Exception('Unrecognized term format');
            }
        } catch (\Exception $e) {
            return [
                'available' => false,
                'normal_hours' => 0,
                'overtime_hours' => 0,
                'total_hours' => 0,
                'message' => 'Invalid term format: ' . $term
            ];
        }

        // Use the static calculatePeriodSummary method from AttendanceCalculationService
        $summary = AttendanceCalculationService::calculatePeriodSummary($profile, $startDate, $endDate);

        // Check if there are any records
        if ($summary['actual']['days_present'] == 0) {
            return [
                'available' => false,
                'normal_hours' => 0,
                'overtime_hours' => 0,
                'total_hours' => 0,
                'message' => 'No attendance records found for this period.'
            ];
        }

        return [
            'available' => true,
            'normal_hours' => max(0, $summary['payroll']['normal_hours']),
            'overtime_hours' => max(0, $summary['payroll']['overtime_hours']),
            'total_hours' => round($summary['actual']['total_worked_minutes'] / 60, 2),
            'record_count' => $summary['actual']['days_present'],
            'period' => $startDate->format('d M') . ' - ' . $endDate->format('d M Y'),
            'message' => "Calculated from {$summary['actual']['days_present']} attendance records."
        ];
    }
    public function hourlyPayStore($id, Request $request){
        $request->validate([
            'normal_hours' => 'required|numeric|min:0',
            'ot_hours' => 'required|numeric|min:0',
        ]);
        $basicSalary = BasicSalary::findOrFail($id);
        $basicSalaryHour = BasicSalaryHour::where('employee_id',$basicSalary->employee_id)->where('term',$request->term)->first();
        if($basicSalaryHour){
            $basicSalaryHour->update([
                'normal_hours' => $request->input('normal_hours'),
                'ot_hours' => $request->input('ot_hours'),
            ]);
        }else{
            BasicSalaryHour::create([
                'employee_id' => $basicSalary->employee_id,
                'term' => $request->term,
                'normal_hours' => $request->input('normal_hours'),
                'ot_hours' => $request->input('ot_hours'),
            ]);
        }

        return redirect()->route('payroll.index', ['employee_id' => $basicSalary->employee_id,'term' => $request->term])
            ->with('success', 'Basic Salary Hours added successfully');

    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id'  => 'required|exists:employees,id',
            'fixed_salary' => 'nullable|numeric|min:0',
            'hourly_rate'  => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }

    public function ajaxValidateUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'fixed_salary' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }
}
