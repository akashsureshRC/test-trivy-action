<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    use \App\Models\Concerns\BelongsToWorkspace;

    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'attendance_radius',
        'clock_in_tolerance_minutes',
        'clock_out_tolerance_minutes',
        'workspace',
        'created_by'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'attendance_radius' => 'integer',
        'clock_in_tolerance_minutes' => 'integer',
        'clock_out_tolerance_minutes' => 'integer',
    ];
    
    protected static function newFactory()
    {
        return null; // Factory not migrated
    }

    /**
     * Get employees assigned to this branch
     */
    public function employeeProfiles()
    {
        return $this->hasMany(Employee::class, 'branch_id');
    }

    /**
     * Get working hours for this branch
     */
    public function workingHours()
    {
        return $this->hasMany(BranchWorkingHour::class, 'branch_id');
    }

    /**
     * Get attendances recorded at this branch
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'branch_id');
    }

    /**
     * Check if branch has geolocation configured
     */
    public function hasGeolocation(): bool
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    /**
     * Get working hours for a specific day
     */
    public function getWorkingHoursForDay(string $day): ?BranchWorkingHour
    {
        return $this->workingHours()->where('day', strtolower($day))->first();
    }

    /**
     * Get all working hours as keyed collection
     */
    public function getWorkingHoursArray(): array
    {
        $hours = [];
        foreach ($this->workingHours as $wh) {
            $hours[$wh->day] = [
                'is_working_day' => $wh->is_working_day,
                'start_time' => $wh->start_time,
                'end_time' => $wh->end_time,
            ];
        }
        return $hours;
    }
}
