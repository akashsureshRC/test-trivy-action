<?php

namespace App\Http\Controllers\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Hrm\Attendance;
use App\Models\Hrm\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AttendanceReviewController extends Controller
{
    /**
     * Display list of attendance records pending HR review
     */
    public function index(Request $request)
    {
        if (!Auth::user()->isAbleTo('attendance manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $workspace = getActiveWorkspace();
        
        // Get filter parameters
        $perPage = $request->get('per_page', 20);
        $employeeId = $request->get('employee_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $status = $request->get('status', 'pending'); // pending, reviewed, all

        // Build query
        $query = Attendance::with(['employee', 'employees', 'branch', 'hrReviewer'])
            ->where('workspace', $workspace);

        if ($status === 'pending') {
            $query->where('requires_hr_review', true)
                  ->whereNull('hr_reviewed_at');
        } elseif ($status === 'reviewed') {
            $query->whereNotNull('hr_reviewed_at');
        } elseif ($status === 'incomplete') {
            // All incomplete records (no clock out)
            $query->where(function ($q) {
                $q->whereNull('clock_out')->orWhere('clock_out', '00:00:00');
            });
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($dateFrom) {
            $query->where('date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('date', '<=', $dateTo);
        }

        $attendances = $query->orderBy('date', 'desc')
                             ->orderBy('created_at', 'desc')
                             ->paginate($perPage)->appends($request->query());

        // Get employees for filter dropdown
        $employees = User::where('workspace_id', $workspace)
            ->where('type', 'employee')
            ->orderBy('name')
            ->get();

        // Count pending reviews
        $pendingCount = Attendance::where('workspace', $workspace)
            ->where('requires_hr_review', true)
            ->whereNull('hr_reviewed_at')
            ->count();

        return view('hrm.attendance.review.index', compact(
            'attendances',
            'employees',
            'pendingCount',
            'employeeId',
            'dateFrom',
            'dateTo',
            'status'
        ));
    }

    /**
     * Show the form to review a specific attendance record
     */
    public function show($id)
    {
        if (!Auth::user()->isAbleTo('attendance manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $attendance = Attendance::with(['employee', 'employees', 'branch', 'hrReviewer'])
            ->where('workspace', getActiveWorkspace())
            ->findOrFail($id);

        // Get the employee's shift/working hours for the day
        $employee = $attendance->employee ?? $attendance->employees;
        $workingHours = null;
        
        if ($employee && $employee->branch) {
            $branch = $employee->branch;
            $dayOfWeek = strtolower($attendance->date->format('l'));
            
            // Check if branch has working hours for this day
            $startField = $dayOfWeek . '_start';
            $endField = $dayOfWeek . '_end';
            
            if (!empty($branch->$startField) && !empty($branch->$endField)) {
                $workingHours = [
                    'start' => $branch->$startField,
                    'end' => $branch->$endField,
                ];
            }
        }

        return view('hrm.attendance.review.show', compact('attendance', 'workingHours'));
    }

    /**
     * Update the attendance record with HR review
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('attendance manage')) {
            return response()->json([
                'status' => 0,
                'message' => __('Permission denied.')
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'clock_out' => 'required|date_format:H:i',
            'hr_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $attendance = Attendance::where('workspace', getActiveWorkspace())
            ->findOrFail($id);

        // Validate clock_out is after clock_in
        $clockIn = Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $attendance->clock_in);
        $clockOut = Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $request->clock_out . ':00');
        
        // Handle overnight shift (clock out before clock in time)
        if ($clockOut->lt($clockIn)) {
            $clockOut->addDay();
        }

        // Update the attendance record
        $attendance->update([
            'clock_out' => $request->clock_out . ':00',
            'hr_reviewed_at' => now(),
            'hr_reviewed_by' => Auth::id(),
            'hr_notes' => $request->hr_notes,
        ]);

        return response()->json([
            'status' => 1,
            'message' => __('Attendance record reviewed successfully.'),
            'data' => [
                'id' => $attendance->id,
                'clock_out' => $attendance->clock_out,
                'worked_hours' => $attendance->getWorkedHoursFormatted(),
            ]
        ]);
    }

    /**
     * Bulk review multiple attendance records
     */
    public function bulkReview(Request $request)
    {
        if (!Auth::user()->isAbleTo('attendance manage')) {
            return response()->json([
                'status' => 0,
                'message' => __('Permission denied.')
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'attendance_ids' => 'required|array|min:1',
            'attendance_ids.*' => 'required|integer|exists:attendances,id',
            'action' => 'required|in:use_shift_end,custom_time',
            'custom_time' => 'required_if:action,custom_time|date_format:H:i',
            'hr_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $workspace = getActiveWorkspace();
        $attendances = Attendance::where('workspace', $workspace)
            ->whereIn('id', $request->attendance_ids)
            ->where('requires_hr_review', true)
            ->whereNull('hr_reviewed_at')
            ->get();

        if ($attendances->isEmpty()) {
            return response()->json([
                'status' => 0,
                'message' => __('No pending attendance records found.')
            ], 404);
        }

        $reviewedCount = 0;
        $errors = [];

        foreach ($attendances as $attendance) {
            try {
                $clockOut = null;

                if ($request->action === 'use_shift_end') {
                    // Use the employee's shift end time
                    $employee = $attendance->employee ?? $attendance->employees;
                    if ($employee && $employee->branch) {
                        $dayOfWeek = strtolower($attendance->date->format('l'));
                        $endField = $dayOfWeek . '_end';
                        $clockOut = $employee->branch->$endField ?? '18:00:00';
                    } else {
                        $clockOut = '18:00:00'; // Default end time
                    }
                } else {
                    $clockOut = $request->custom_time . ':00';
                }

                $attendance->update([
                    'clock_out' => $clockOut,
                    'hr_reviewed_at' => now(),
                    'hr_reviewed_by' => Auth::id(),
                    'hr_notes' => $request->hr_notes ?? 'Bulk reviewed by HR',
                ]);

                $reviewedCount++;
            } catch (\Exception $e) {
                $errors[] = "ID {$attendance->id}: " . $e->getMessage();
            }
        }

        $message = __(':count attendance records reviewed successfully.', ['count' => $reviewedCount]);
        if (!empty($errors)) {
            $message .= ' ' . __(':count errors occurred.', ['count' => count($errors)]);
        }

        return response()->json([
            'status' => 1,
            'message' => $message,
            'data' => [
                'reviewed_count' => $reviewedCount,
                'errors' => $errors
            ]
        ]);
    }

    /**
     * Flag an attendance record for HR review manually
     */
    public function flag($id)
    {
        if (!Auth::user()->isAbleTo('attendance manage')) {
            return response()->json([
                'status' => 0,
                'message' => __('Permission denied.')
            ], 403);
        }

        $attendance = Attendance::where('workspace', getActiveWorkspace())
            ->findOrFail($id);

        $attendance->update(['requires_hr_review' => true]);

        return response()->json([
            'status' => 1,
            'message' => __('Attendance record flagged for review.')
        ]);
    }

    /**
     * Get statistics for HR review dashboard
     */
    public function stats()
    {
        if (!Auth::user()->isAbleTo('attendance manage')) {
            return response()->json([
                'status' => 0,
                'message' => __('Permission denied.')
            ], 403);
        }

        $workspace = getActiveWorkspace();

        $stats = [
            'pending_reviews' => Attendance::where('workspace', $workspace)
                ->where('requires_hr_review', true)
                ->whereNull('hr_reviewed_at')
                ->count(),
            
            'reviewed_this_week' => Attendance::where('workspace', $workspace)
                ->whereNotNull('hr_reviewed_at')
                ->where('hr_reviewed_at', '>=', now()->startOfWeek())
                ->count(),
            
            'incomplete_today' => Attendance::where('workspace', $workspace)
                ->whereDate('date', today())
                ->where(function ($q) {
                    $q->whereNull('clock_out')->orWhere('clock_out', '00:00:00');
                })
                ->count(),
            
            'oldest_pending' => Attendance::where('workspace', $workspace)
                ->where('requires_hr_review', true)
                ->whereNull('hr_reviewed_at')
                ->orderBy('date', 'asc')
                ->first()?->date?->format('Y-m-d'),
        ];

        return response()->json([
            'status' => 1,
            'data' => $stats
        ]);
    }
}
