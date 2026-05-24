<?php

namespace App\Services;

use App\Models\Hrm\Employee;
use App\Models\Hrm\Attendance;
use Carbon\Carbon;

class AttendanceCalculationService
{
    /**
     * Normalize a clock time value to H:i:s format
     * Handles Carbon instances, datetime strings, and time strings
     */
    protected static function normalizeClockTime($clockTime): ?string
    {
        if (empty($clockTime) || $clockTime === '00:00:00') {
            return null;
        }
        
        if ($clockTime instanceof Carbon) {
            return $clockTime->format('H:i:s');
        }
        
        // If it's a datetime string (longer than 8 chars), parse and extract time
        if (strlen($clockTime) > 8) {
            return Carbon::parse($clockTime)->format('H:i:s');
        }
        
        // Already a time string
        return $clockTime;
    }

    /**
     * Calculate late arrival minutes
     * 
     * @param Employee $employee
     * @param Carbon|string $date
     * @param string $clockInTime
     * @param int $toleranceMinutes
     * @return int Late minutes (0 if not late)
     */
    public static function calculateLateMinutes(
        Employee $employee,
        $date,
        $clockInTime,
        int $toleranceMinutes = 0
    ): int {
        $workingHours = WorkingHoursService::getWorkingHoursForDate($employee, $date);
        
        if (!$workingHours || !$workingHours->is_working_day || !$workingHours->start_time) {
            return 0;
        }

        $normalizedClockIn = self::normalizeClockTime($clockInTime);
        if (!$normalizedClockIn) {
            return 0;
        }

        $expectedStart = Carbon::parse($workingHours->start_time);
        $actualClockIn = Carbon::parse($normalizedClockIn);
        
        // Apply tolerance
        $toleranceEnd = $expectedStart->copy()->addMinutes($toleranceMinutes);
        
        if ($actualClockIn->gt($toleranceEnd)) {
            return $actualClockIn->diffInMinutes($expectedStart);
        }

        return 0;
    }

    /**
     * Calculate early leaving minutes
     * 
     * @param Employee $employee
     * @param Carbon|string $date
     * @param string $clockOutTime
     * @param int $toleranceMinutes
     * @return int Early leaving minutes (0 if not early)
     */
    public static function calculateEarlyLeavingMinutes(
        Employee $employee,
        $date,
        $clockOutTime,
        int $toleranceMinutes = 0
    ): int {
        $workingHours = WorkingHoursService::getWorkingHoursForDate($employee, $date);
        
        if (!$workingHours || !$workingHours->is_working_day || !$workingHours->end_time) {
            return 0;
        }

        $normalizedClockOut = self::normalizeClockTime($clockOutTime);
        if (!$normalizedClockOut) {
            return 0;
        }

        $expectedEnd = Carbon::parse($workingHours->end_time);
        $actualClockOut = Carbon::parse($normalizedClockOut);
        
        // Apply tolerance
        $toleranceStart = $expectedEnd->copy()->subMinutes($toleranceMinutes);
        
        if ($actualClockOut->lt($toleranceStart)) {
            return $expectedEnd->diffInMinutes($actualClockOut);
        }

        return 0;
    }

    /**
     * Calculate overtime minutes
     * Overtime is only when actual worked hours EXCEED expected working hours
     * 
     * @param Employee $employee
     * @param Carbon|string $date
     * @param int $actualWorkedMinutes Total minutes actually worked
     * @return int Overtime minutes (0 if no overtime)
     */
    public static function calculateOvertimeMinutes(
        Employee $employee,
        $date,
        int $actualWorkedMinutes
    ): int {
        $expectedMinutes = WorkingHoursService::getExpectedMinutesForDate($employee, $date);
        
        if ($expectedMinutes <= 0) {
            // If not a working day, any work is overtime
            return $actualWorkedMinutes;
        }

        // Overtime is only the amount worked BEYOND expected hours
        return max(0, $actualWorkedMinutes - $expectedMinutes);
    }

    /**
     * Calculate total rest/break minutes for a day
     * This is the sum of gaps between consecutive clock_out and clock_in entries
     * 
     * @param int $employeeId
     * @param Carbon|string $date
     * @param int $workspaceId
     * @return int Total rest minutes
     */
    public static function calculateRestMinutesForDate(int $employeeId, $date, int $workspaceId = null): int
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        
        $query = Attendance::where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->where('status', 'Present')
            ->orderBy('clock_in', 'asc');
        
        if ($workspaceId) {
            $query->where('workspace', $workspaceId);
        }
        
        $attendances = $query->get();
        
        if ($attendances->count() <= 1) {
            return 0; // No breaks if only one entry
        }

        $totalRestMinutes = 0;
        $previousClockOut = null;

        foreach ($attendances as $attendance) {
            $normalizedClockIn = self::normalizeClockTime($attendance->clock_in);
            $normalizedClockOut = self::normalizeClockTime($attendance->clock_out);
            
            if ($previousClockOut && $normalizedClockIn) {
                $clockIn = Carbon::parse($normalizedClockIn);
                $prevOut = Carbon::parse($previousClockOut);
                
                if ($clockIn->gt($prevOut)) {
                    $totalRestMinutes += $clockIn->diffInMinutes($prevOut);
                }
            }
            
            if ($normalizedClockOut) {
                $previousClockOut = $normalizedClockOut;
            }
        }

        return $totalRestMinutes;
    }

    /**
     * Calculate total worked minutes from attendance records for a date
     * Handles multiple clock in/out entries per day
     * Only counts hours for complete records OR HR-reviewed records
     * 
     * @param int $employeeId
     * @param Carbon|string $date
     * @return int Total worked minutes
     */
    public static function calculateWorkedMinutesForDate(int $employeeId, $date): int
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        
        // Get all attendance records for the date (including incomplete but reviewed)
        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->where(function($query) {
                // Include complete records
                $query->where(function($q) {
                    $q->whereNotNull('clock_out')
                      ->where('clock_out', '!=', '00:00:00');
                })
                // OR incomplete but HR reviewed
                ->orWhere(function($q) {
                    $q->whereNotNull('hr_reviewed_at');
                });
            })
            ->get();

        $totalMinutes = 0;
        foreach ($attendances as $attendance) {
            $totalMinutes += $attendance->getCountableWorkedMinutes();
        }

        return $totalMinutes;
    }

    /**
     * Calculate working hours summary for an employee for a date range
     * 
     * @param Employee $employee
     * @param Carbon|string $startDate
     * @param Carbon|string $endDate
     * @return array Detailed summary
     */
    public static function calculatePeriodSummary(
        Employee $employee,
        $startDate,
        $endDate
    ): array {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

        // Get expected hours
        $expectedData = WorkingHoursService::getExpectedHoursForPeriod($employee, $start, $end);
        
        // Get actual attendance data - include complete records and HR-reviewed incomplete records
        $attendances = Attendance::where('employee_id', $employee->id)
            ->dateRange($start, $end)
            ->where(function($query) {
                // Include complete records
                $query->where(function($q) {
                    $q->whereNotNull('clock_out')
                      ->where('clock_out', '!=', '00:00:00');
                })
                // OR incomplete but HR reviewed
                ->orWhere(function($q) {
                    $q->whereNotNull('hr_reviewed_at');
                });
            })
            ->get();

        $totalWorkedMinutes = 0;
        $totalLateMinutes = 0;
        $totalEarlyLeavingMinutes = 0;
        $totalOvertimeMinutes = 0;
        $daysPresent = 0;
        $dailyDetails = [];

        // Group attendances by date
        $attendancesByDate = $attendances->groupBy(function ($attendance) {
            return $attendance->date->format('Y-m-d');
        });

        $current = $start->copy();
        while ($current <= $end) {
            $dateKey = $current->format('Y-m-d');
            $dayName = strtolower($current->format('l'));
            $workingHours = WorkingHoursService::getWorkingHoursForDate($employee, $current);
            $isWorkingDay = $workingHours && $workingHours->is_working_day;
            
            $dayData = [
                'date' => $dateKey,
                'day' => ucfirst($dayName),
                'is_working_day' => $isWorkingDay,
                'expected_minutes' => $isWorkingDay ? WorkingHoursService::getExpectedMinutesForDate($employee, $current) : 0,
                'worked_minutes' => 0,
                'late_minutes' => 0,
                'early_leaving_minutes' => 0,
                'overtime_minutes' => 0,
                'status' => 'absent',
                'entries' => [],
            ];

            if (isset($attendancesByDate[$dateKey])) {
                $dayAttendances = $attendancesByDate[$dateKey];
                $dayWorkedMinutes = 0;
                $firstClockIn = null;
                $lastClockOut = null;

                foreach ($dayAttendances as $att) {
                    $dayWorkedMinutes += $att->getCountableWorkedMinutes();
                    
                    if (!$firstClockIn || Carbon::parse($att->clock_in)->lt(Carbon::parse($firstClockIn))) {
                        $firstClockIn = $att->clock_in;
                    }
                    if (!$lastClockOut || Carbon::parse($att->clock_out)->gt(Carbon::parse($lastClockOut))) {
                        $lastClockOut = $att->clock_out;
                    }

                    $dayData['entries'][] = [
                        'clock_in' => $att->clock_in,
                        'clock_out' => $att->clock_out,
                        'minutes' => $att->getCountableWorkedMinutes(),
                    ];
                }

                $dayData['worked_minutes'] = $dayWorkedMinutes;
                $totalWorkedMinutes += $dayWorkedMinutes;
                $daysPresent++;
                $dayData['status'] = 'present';

                // Calculate late, early leaving, overtime based on first clock-in and last clock-out
                if ($isWorkingDay && $firstClockIn) {
                    $toleranceIn = $employee->branch ? $employee->branch->clock_in_tolerance_minutes : 0;
                    $toleranceOut = $employee->branch ? $employee->branch->clock_out_tolerance_minutes : 0;

                    $late = self::calculateLateMinutes($employee, $current, $firstClockIn, $toleranceIn);
                    $dayData['late_minutes'] = $late;
                    $totalLateMinutes += $late;

                    if ($lastClockOut && $lastClockOut !== '00:00:00') {
                        $early = self::calculateEarlyLeavingMinutes($employee, $current, $lastClockOut, $toleranceOut);
                        $dayData['early_leaving_minutes'] = $early;
                        $totalEarlyLeavingMinutes += $early;
                    }
                }

                // Calculate overtime based on actual worked minutes exceeding expected
                $overtime = self::calculateOvertimeMinutes($employee, $current, $dayWorkedMinutes);
                $dayData['overtime_minutes'] = $overtime;
                $totalOvertimeMinutes += $overtime;
            }

            $dailyDetails[] = $dayData;
            $current->addDay();
        }

        // Calculate normal hours vs overtime for payroll
        $expectedMinutes = $expectedData['total_minutes'];
        $normalMinutes = min($totalWorkedMinutes, $expectedMinutes);
        $overtimeFromTotal = max(0, $totalWorkedMinutes - $expectedMinutes);

        return [
            'period' => [
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
            ],
            'expected' => [
                'working_days' => $expectedData['working_days'],
                'total_minutes' => $expectedMinutes,
                'total_hours' => WorkingHoursService::formatHours($expectedMinutes),
            ],
            'actual' => [
                'days_present' => $daysPresent,
                'total_worked_minutes' => $totalWorkedMinutes,
                'total_worked_hours' => WorkingHoursService::formatHours($totalWorkedMinutes),
                'total_late_minutes' => $totalLateMinutes,
                'total_late_hours' => WorkingHoursService::formatHours($totalLateMinutes),
                'total_early_leaving_minutes' => $totalEarlyLeavingMinutes,
                'total_early_leaving_hours' => WorkingHoursService::formatHours($totalEarlyLeavingMinutes),
                'total_overtime_minutes' => $totalOvertimeMinutes,
                'total_overtime_hours' => WorkingHoursService::formatHours($totalOvertimeMinutes),
            ],
            'payroll' => [
                'normal_minutes' => $normalMinutes,
                'normal_hours' => round($normalMinutes / 60, 2),
                'overtime_minutes' => $overtimeFromTotal,
                'overtime_hours' => round($overtimeFromTotal / 60, 2),
            ],
            'daily_details' => $dailyDetails,
        ];
    }

    /**
     * Format minutes as HH:MM string
     */
    public static function formatMinutes(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }
}
