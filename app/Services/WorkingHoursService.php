<?php

namespace App\Services;

use App\Models\Hrm\Employee;
use App\Models\Hrm\BranchWorkingHour;
use App\Models\Hrm\EmployeeWorkingHour;
use Carbon\Carbon;

class WorkingHoursService
{
    /**
     * Get working hours for an employee for a specific day
     * Uses custom hours if enabled, otherwise falls back to branch hours
     * 
     * @param Employee $employee
     * @param string $day Day name (e.g., 'monday')
     * @return object|null Working hours object or null
     */
    public static function getWorkingHoursForDay(Employee $employee, string $day): ?object
    {
        $day = strtolower($day);

        // Check if employee uses custom working hours
        if ($employee->use_custom_working_hours) {
            $customHours = EmployeeWorkingHour::where('employee_id', $employee->id)
                ->where('day', $day)
                ->first();
            
            if ($customHours) {
                return $customHours;
            }
        }

        // Fall back to branch working hours
        if ($employee->branch_id) {
            return BranchWorkingHour::where('branch_id', $employee->branch_id)
                ->where('day', $day)
                ->first();
        }

        return null;
    }

    /**
     * Get working hours for a specific date
     * 
     * @param Employee $employee
     * @param Carbon|string $date
     * @return object|null
     */
    public static function getWorkingHoursForDate(Employee $employee, $date): ?object
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        $dayName = strtolower($date->format('l')); // e.g., 'monday'
        
        return self::getWorkingHoursForDay($employee, $dayName);
    }

    /**
     * Check if a date is a working day for an employee
     * 
     * @param Employee $employee
     * @param Carbon|string $date
     * @return bool
     */
    public static function isWorkingDay(Employee $employee, $date): bool
    {
        $workingHours = self::getWorkingHoursForDate($employee, $date);
        return $workingHours ? $workingHours->is_working_day : false;
    }

    /**
     * Get expected working minutes for a specific date (excluding lunch)
     * 
     * @param Employee $employee
     * @param Carbon|string $date
     * @return int Minutes
     */
    public static function getExpectedMinutesForDate(Employee $employee, $date): int
    {
        $workingHours = self::getWorkingHoursForDate($employee, $date);
        
        if (!$workingHours || !$workingHours->is_working_day) {
            return 0;
        }

        // Use the model's method which now accounts for lunch time
        return $workingHours->getExpectedMinutes();
    }

    /**
     * Get lunch duration in minutes for a specific date
     * 
     * @param Employee $employee
     * @param Carbon|string $date
     * @return int Minutes
     */
    public static function getLunchMinutesForDate(Employee $employee, $date): int
    {
        $workingHours = self::getWorkingHoursForDate($employee, $date);
        
        if (!$workingHours) {
            return 0;
        }

        return $workingHours->getLunchDurationMinutes();
    }

    /**
     * Check if a time falls within lunch period for a specific date
     * 
     * @param Employee $employee
     * @param Carbon|string $date
     * @param string $time
     * @return bool
     */
    public static function isLunchTimeForDate(Employee $employee, $date, string $time): bool
    {
        $workingHours = self::getWorkingHoursForDate($employee, $date);
        
        if (!$workingHours) {
            return false;
        }

        return $workingHours->isLunchTime($time);
    }

    /**
     * Get all working hours configuration for an employee
     * 
     * @param Employee $employee
     * @return array Keyed by day name
     */
    public static function getAllWorkingHours(Employee $employee): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $hours = [];

        foreach ($days as $day) {
            $wh = self::getWorkingHoursForDay($employee, $day);
            $hours[$day] = $wh ? [
                'is_working_day' => $wh->is_working_day,
                'start_time' => $wh->start_time ? Carbon::parse($wh->start_time)->format('H:i') : null,
                'end_time' => $wh->end_time ? Carbon::parse($wh->end_time)->format('H:i') : null,
            ] : [
                'is_working_day' => false,
                'start_time' => null,
                'end_time' => null,
            ];
        }

        return $hours;
    }

    /**
     * Get total expected working hours for a date range
     * 
     * @param Employee $employee
     * @param Carbon|string $startDate
     * @param Carbon|string $endDate
     * @return array ['total_minutes' => int, 'working_days' => int]
     */
    public static function getExpectedHoursForPeriod(
        Employee $employee, 
        $startDate, 
        $endDate
    ): array {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);
        
        $totalMinutes = 0;
        $workingDays = 0;
        $current = $start->copy();

        while ($current <= $end) {
            $minutes = self::getExpectedMinutesForDate($employee, $current);
            if ($minutes > 0) {
                $totalMinutes += $minutes;
                $workingDays++;
            }
            $current->addDay();
        }

        return [
            'total_minutes' => $totalMinutes,
            'total_hours' => round($totalMinutes / 60, 2),
            'working_days' => $workingDays,
        ];
    }

    /**
     * Get formatted expected hours string
     * 
     * @param int $minutes
     * @return string
     */
    public static function formatHours(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%d:%02d', $hours, $mins);
    }
}
