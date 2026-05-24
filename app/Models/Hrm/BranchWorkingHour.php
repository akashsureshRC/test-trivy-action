<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BranchWorkingHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'day',
        'is_working_day',
        'start_time',
        'end_time',
        'lunch_start_time',
        'lunch_end_time',
    ];

    protected $casts = [
        'is_working_day' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'lunch_start_time' => 'datetime:H:i',
        'lunch_end_time' => 'datetime:H:i',
    ];

    /**
     * Days of the week
     */
    const DAYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    /**
     * Get the branch that owns this working hour
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Get expected working hours for this day in minutes (excluding lunch)
     */
    public function getExpectedMinutes(): int
    {
        if (!$this->is_working_day || !$this->start_time || !$this->end_time) {
            return 0;
        }

        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);

        $totalMinutes = $end->diffInMinutes($start);

        // Deduct lunch time if configured
        $lunchMinutes = $this->getLunchDurationMinutes();
        
        return max(0, $totalMinutes - $lunchMinutes);
    }

    /**
     * Get lunch duration in minutes
     */
    public function getLunchDurationMinutes(): int
    {
        if (!$this->lunch_start_time || !$this->lunch_end_time) {
            return 0;
        }

        $lunchStart = \Carbon\Carbon::parse($this->lunch_start_time);
        $lunchEnd = \Carbon\Carbon::parse($this->lunch_end_time);

        return $lunchEnd->diffInMinutes($lunchStart);
    }

    /**
     * Check if a given time falls within lunch break
     */
    public function isLunchTime(string $time): bool
    {
        if (!$this->lunch_start_time || !$this->lunch_end_time) {
            return false;
        }

        $checkTime = \Carbon\Carbon::parse($time);
        $lunchStart = \Carbon\Carbon::parse($this->lunch_start_time);
        $lunchEnd = \Carbon\Carbon::parse($this->lunch_end_time);

        return $checkTime->between($lunchStart, $lunchEnd);
    }

    /**
     * Get expected working hours formatted
     */
    public function getExpectedHoursFormatted(): string
    {
        $minutes = $this->getExpectedMinutes();
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }

    /**
     * Check if a given time is within working hours
     */
    public function isWithinWorkingHours(string $time): bool
    {
        if (!$this->is_working_day) {
            return false;
        }

        $checkTime = \Carbon\Carbon::parse($time);
        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);

        return $checkTime->between($start, $end);
    }

    /**
     * Create default working hours for a branch (Mon-Fri 08:00-17:00)
     */
    public static function createDefaultForBranch(int $branchId): void
    {
        $workingDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $nonWorkingDays = ['saturday', 'sunday'];

        foreach ($workingDays as $day) {
            self::updateOrCreate(
                ['branch_id' => $branchId, 'day' => $day],
                [
                    'is_working_day' => true,
                    'start_time' => '08:00:00',
                    'end_time' => '17:00:00',
                ]
            );
        }

        foreach ($nonWorkingDays as $day) {
            self::updateOrCreate(
                ['branch_id' => $branchId, 'day' => $day],
                [
                    'is_working_day' => false,
                    'start_time' => null,
                    'end_time' => null,
                ]
            );
        }
    }
}
