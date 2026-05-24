<?php

namespace App\Http\Controllers\Hrm\Api\Ess;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Hrm\Employee;
use App\Models\Hrm\Attendance;
use App\Models\Hrm\Branch;
use App\Services\GeolocationService;
use App\Services\WorkingHoursService;
use App\Services\AttendanceCalculationService;
use Carbon\Carbon;

class EssAttendanceApiController extends Controller
{
    /**
     * Get current attendance status
     * Returns whether employee is clocked in and their current session details
     * 
     * @response 200 {
     *   "status": 1,
     *   "message": "Attendance status retrieved",
     *   "data": {
     *     "is_clocked_in": true,
     *     "current_session": {...},
     *     "branch": {...},
     *     "working_hours": {...}
     *   }
     * }
     */
    public function status(Request $request)
    {
        try {
            $employee = $request->ess_employee;

            // If attendance is not enabled, return early with minimal data
            if (!$employee->attendance_enabled) {
                return response()->json([
                    'status' => 1,
                    'message' => 'Attendance status retrieved',
                    'data' => [
                        'attendance_enabled' => false,
                        'is_clocked_in' => false,
                        'current_session' => null,
                        'today_summary' => null,
                        'branch' => null,
                        'working_hours' => null,
                    ]
                ], 200);
            }

            // Get today's open attendance (clocked in but not clocked out)
            $currentSession = Attendance::where('employee_id', $employee->id)
                ->whereDate('date', Carbon::today())
                ->whereNotNull('clock_in')
                ->whereNull('clock_out')
                ->latest('clock_in')
                ->first();

            // Get today's completed sessions
            $todaySessions = Attendance::where('employee_id', $employee->id)
                ->whereDate('date', Carbon::today())
                ->completed()
                ->get();

            $totalWorkedMinutes = $todaySessions->sum(function ($att) {
                return $att->getWorkedMinutes();
            });

            // Get branch info
            $branch = $employee->branch;
            $branchData = null;
            if ($branch) {
                $branchData = [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'address' => $branch->address,
                    'has_geolocation' => $branch->hasGeolocation(),
                    'latitude' => $branch->latitude ? (float) $branch->latitude : null,
                    'longitude' => $branch->longitude ? (float) $branch->longitude : null,
                    'attendance_radius' => $branch->attendance_radius,
                ];
            }

            // Get working hours for today
            $workingHours = WorkingHoursService::getWorkingHoursForDate($employee, Carbon::today());
            $workingHoursData = null;
            if ($workingHours && $workingHours->is_working_day) {
                $workingHoursData = [
                    'is_working_day' => true,
                    'start_time' => $workingHours->start_time ? Carbon::parse($workingHours->start_time)->format('H:i') : null,
                    'end_time' => $workingHours->end_time ? Carbon::parse($workingHours->end_time)->format('H:i') : null,
                    'expected_minutes' => $workingHours->getExpectedMinutes(),
                ];
            } else {
                $workingHoursData = [
                    'is_working_day' => false,
                    'start_time' => null,
                    'end_time' => null,
                    'expected_minutes' => 0,
                ];
            }

            return response()->json([
                'status' => 1,
                'message' => 'Attendance status retrieved',
                'data' => [
                    'attendance_enabled' => true,
                    'is_clocked_in' => $currentSession !== null,
                    'current_session' => $currentSession ? [
                        'id' => $currentSession->id,
                        'clock_in' => $currentSession->clock_in,
                        'clock_in_latitude' => $currentSession->clock_in_latitude ? (float) $currentSession->clock_in_latitude : null,
                        'clock_in_longitude' => $currentSession->clock_in_longitude ? (float) $currentSession->clock_in_longitude : null,
                        'duration_minutes' => Carbon::parse($currentSession->clock_in)->diffInMinutes(Carbon::now()),
                    ] : null,
                    'today_summary' => [
                        'total_worked_minutes' => $totalWorkedMinutes,
                        'total_worked_hours' => WorkingHoursService::formatHours($totalWorkedMinutes),
                    ],
                    'branch' => $branchData,
                    'working_hours' => $workingHoursData ? [
                        'is_working_day' => $workingHoursData['is_working_day'],
                        'start_time' => $workingHoursData['start_time'],
                        'end_time' => $workingHoursData['end_time'],
                    ] : null,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to retrieve attendance status',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Clock In
     * Records employee clock-in with geolocation validation
     * 
     * @bodyParam latitude float required Employee's current latitude. Example: -26.2041
     * @bodyParam longitude float required Employee's current longitude. Example: 28.0473
     * 
     * @response 200 {
     *   "status": 1,
     *   "message": "Clocked in successfully",
     *   "data": {...}
     * }
     */
    public function clockIn(Request $request)
    {
        try {
            $employee = $request->ess_employee;

            // Check if attendance is enabled
            if (!$employee->attendance_enabled) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Attendance tracking is not enabled for your account',
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if already clocked in TODAY
            $existingSession = Attendance::where('employee_id', $employee->id)
                ->whereDate('date', Carbon::today())
                ->whereNotNull('clock_in')
                ->whereNull('clock_out')
                ->first();

            if ($existingSession) {
                return response()->json([
                    'status' => 0,
                    'message' => 'You are already clocked in. Please clock out first.',
                    'data' => [
                        'existing_session' => [
                            'id' => $existingSession->id,
                            'clock_in' => $existingSession->clock_in,
                        ]
                    ]
                ], 400);
            }

            // Flag any incomplete records from PREVIOUS days for HR review
            // This allows the employee to clock in today while flagging missed clock-outs
            $incompleteRecords = Attendance::where('employee_id', $employee->id)
                ->where('date', '<', Carbon::today())
                ->whereNotNull('clock_in')
                ->where(function ($q) {
                    $q->whereNull('clock_out')->orWhere('clock_out', '00:00:00');
                })
                ->where('requires_hr_review', false)
                ->get();

            if ($incompleteRecords->count() > 0) {
                // Flag all incomplete previous records for HR review
                foreach ($incompleteRecords as $record) {
                    $record->update(['requires_hr_review' => true]);
                }
            }

            // Validate geolocation against branch
            $branch = $employee->branch;
            $geofenceResult = ['within_geofence' => true, 'distance' => 0];
            
            if ($branch && $branch->hasGeolocation()) {
                $geofenceResult = GeolocationService::checkBranchGeofence(
                    $request->latitude,
                    $request->longitude,
                    $branch
                );

                if (!$geofenceResult['within_geofence']) {
                    return response()->json([
                        'status' => 0,
                        'message' => $geofenceResult['message'],
                        'data' => [
                            'distance' => $geofenceResult['distance'],
                            'required_radius' => $geofenceResult['radius'],
                            'branch' => [
                                'name' => $branch->name,
                                'latitude' => (float) $branch->latitude,
                                'longitude' => (float) $branch->longitude,
                            ]
                        ]
                    ], 403);
                }
            }

            // Create attendance record
            DB::beginTransaction();
            try {
                $attendance = Attendance::create([
                    'employee_id' => $employee->id,
                    'branch_id' => $employee->branch_id,
                    'date' => Carbon::today(),
                    'clock_in' => Carbon::now()->format('H:i:s'),
                    'clock_in_latitude' => $request->latitude,
                    'clock_in_longitude' => $request->longitude,
                    'status' => 'Present',
                    'marked_by' => Attendance::MARKED_BY_EMPLOYEE,
                    'workspace' => $employee->workspace_id,
                    'created_by' => $employee->id,
                ]);

                DB::commit();

                return response()->json([
                    'status' => 1,
                    'message' => 'Clocked in successfully',
                    'data' => [
                        'attendance_id' => $attendance->id,
                        'clock_in' => $attendance->clock_in,
                        'date' => $attendance->date->format('Y-m-d'),
                        'location' => [
                            'latitude' => (float) $attendance->clock_in_latitude,
                            'longitude' => (float) $attendance->clock_in_longitude,
                            'distance_from_branch' => $geofenceResult['distance'],
                        ],
                    ]
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to clock in',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Clock Out
     * Records employee clock-out with geolocation validation
     * 
     * @bodyParam latitude float required Employee's current latitude. Example: -26.2041
     * @bodyParam longitude float required Employee's current longitude. Example: 28.0473
     * 
     * @response 200 {
     *   "status": 1,
     *   "message": "Clocked out successfully",
     *   "data": {...}
     * }
     */
    public function clockOut(Request $request)
    {
        try {
            $employee = $request->ess_employee;

            // Check if attendance is enabled
            if (!$employee->attendance_enabled) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Attendance tracking is not enabled for your account',
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find active clock-in session
            $attendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('date', Carbon::today())
                ->whereNotNull('clock_in')
                ->whereNull('clock_out')
                ->latest('clock_in')
                ->first();

            if (!$attendance) {
                return response()->json([
                    'status' => 0,
                    'message' => 'No active clock-in found. Please clock in first.',
                ], 400);
            }

            // Validate geolocation against branch
            $branch = $employee->branch;
            $geofenceResult = ['within_geofence' => true, 'distance' => 0];
            
            if ($branch && $branch->hasGeolocation()) {
                $geofenceResult = GeolocationService::checkBranchGeofence(
                    $request->latitude,
                    $request->longitude,
                    $branch
                );

                if (!$geofenceResult['within_geofence']) {
                    return response()->json([
                        'status' => 0,
                        'message' => $geofenceResult['message'],
                        'data' => [
                            'distance' => $geofenceResult['distance'],
                            'required_radius' => $geofenceResult['radius'],
                            'branch' => [
                                'name' => $branch->name,
                                'latitude' => (float) $branch->latitude,
                                'longitude' => (float) $branch->longitude,
                            ]
                        ]
                    ], 403);
                }
            }

            // Update attendance record
            DB::beginTransaction();
            try {
                $attendance->clock_out = Carbon::now()->format('H:i:s');
                $attendance->clock_out_latitude = $request->latitude;
                $attendance->clock_out_longitude = $request->longitude;
                $attendance->save();

                DB::commit();

                return response()->json([
                    'status' => 1,
                    'message' => 'Clocked out successfully',
                    'data' => [
                        'attendance_id' => $attendance->id,
                        'clock_in' => $attendance->clock_in,
                        'clock_out' => $attendance->clock_out,
                        'date' => $attendance->date->format('Y-m-d'),
                        'location' => [
                            'latitude' => (float) $attendance->clock_out_latitude,
                            'longitude' => (float) $attendance->clock_out_longitude,
                            'distance_from_branch' => $geofenceResult['distance'],
                        ],
                    ]
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to clock out',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get attendance history
     * Returns attendance records for the employee
     * 
     * @queryParam start_date string Start date (Y-m-d). Example: 2025-01-01
     * @queryParam end_date string End date (Y-m-d). Example: 2025-01-31
     * @queryParam page int Page number for pagination. Example: 1
     * 
     * @response 200 {
     *   "status": 1,
     *   "message": "Attendance history retrieved",
     *   "data": {...}
     * }
     */
    public function history(Request $request)
    {
        try {
            $employee = $request->ess_employee;

            $validator = Validator::make($request->all(), [
                'start_date' => 'nullable|date|date_format:Y-m-d',
                'end_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date',
                'page' => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            // Default to current month
            $startDate = $request->start_date 
                ? Carbon::parse($request->start_date) 
                : Carbon::now()->startOfMonth();
            $endDate = $request->end_date 
                ? Carbon::parse($request->end_date) 
                : Carbon::now()->endOfMonth();

            // Get attendance records
            $attendances = Attendance::where('employee_id', $employee->id)
                ->dateRange($startDate, $endDate)
                ->orderBy('date', 'desc')
                ->orderBy('clock_in', 'desc')
                ->paginate(20);

            // Format records
            $records = $attendances->map(function ($att) {
                return [
                    'id' => $att->id,
                    'date' => $att->date->format('Y-m-d'),
                    'day' => $att->date->format('l'),
                    'clock_in' => $att->clock_in,
                    'clock_out' => $att->clock_out,
                    'status' => $att->status,
                    'worked_minutes' => $att->clock_out ? $att->getWorkedMinutes() : null,
                    'worked_hours' => $att->clock_out ? WorkingHoursService::formatHours($att->getWorkedMinutes()) : null,
                ];
            });

            // Calculate period summary
            $summary = AttendanceCalculationService::calculatePeriodSummary(
                $employee,
                $startDate,
                $endDate
            );

            return response()->json([
                'status' => 1,
                'message' => 'Attendance history retrieved',
                'data' => [
                    'records' => $records,
                    'summary' => [
                        'period' => $summary['period'],
                        'actual' => [
                            'days_present' => $summary['actual']['days_present'],
                            'total_worked_minutes' => $summary['actual']['total_worked_minutes'],
                            'total_worked_hours' => $summary['actual']['total_worked_hours'],
                        ],
                    ],
                    'pagination' => [
                        'current_page' => $attendances->currentPage(),
                        'last_page' => $attendances->lastPage(),
                        'per_page' => $attendances->perPage(),
                        'total' => $attendances->total(),
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to retrieve attendance history',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
