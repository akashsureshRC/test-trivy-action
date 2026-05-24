<?php

namespace App\Models\Hrm;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use \App\Models\Concerns\BelongsToWorkspace;

    use HasFactory;

    // Marked by constants
    const MARKED_BY_EMPLOYEE = 1;
    const MARKED_BY_HR = 2;

    protected $fillable = [
        'employee_id',
        'branch_id',
        'date',
        'status',
        'clock_in',
        'clock_out',
        'clock_in_latitude',
        'clock_in_longitude',
        'clock_out_latitude',
        'clock_out_longitude',
        'marked_by',
        'late',
        'early_leaving',
        'overtime',
        'total_rest',
        'workspace',
        'created_by',
        // HR Review fields
        'requires_hr_review',
        'hr_reviewed_at',
        'hr_reviewed_by',
        'hr_notes',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in_latitude' => 'decimal:8',
        'clock_in_longitude' => 'decimal:8',
        'clock_out_latitude' => 'decimal:8',
        'clock_out_longitude' => 'decimal:8',
        'marked_by' => 'integer',
        'requires_hr_review' => 'boolean',
        'hr_reviewed_at' => 'datetime',
    ];
    
    protected static function newFactory()
    {
        return null; // Factory not migrated
    }

    public function employees()
    {
        return $this->hasOne('App\Models\User', 'id', 'employee_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Get the branch where attendance was recorded
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Check if this attendance was marked by employee (via mobile app)
     */
    public function isMarkedByEmployee(): bool
    {
        return $this->marked_by === self::MARKED_BY_EMPLOYEE;
    }

    /**
     * Check if this attendance was marked by HR
     */
    public function isMarkedByHR(): bool
    {
        return $this->marked_by === self::MARKED_BY_HR;
    }

    /**
     * Check if employee is currently clocked in (no clock out)
     */
    public function isClockedIn(): bool
    {
        return !empty($this->clock_in) && (empty($this->clock_out) || $this->clock_out === '00:00:00');
    }

    /**
     * Check if this attendance record has geolocation data
     */
    public function hasClockInGeolocation(): bool
    {
        return !is_null($this->clock_in_latitude) && !is_null($this->clock_in_longitude);
    }

    /**
     * Check if this attendance record has clock out geolocation data
     */
    public function hasClockOutGeolocation(): bool
    {
        return !is_null($this->clock_out_latitude) && !is_null($this->clock_out_longitude);
    }

    /**
     * Calculate total worked minutes for this attendance record
     */
    public function getWorkedMinutes(): int
    {
        if (empty($this->clock_in) || empty($this->clock_out) || $this->clock_out === '00:00:00') {
            return 0;
        }

        // Get just the time portion in case clock_in/clock_out are already datetime strings
        $clockInTime = $this->clock_in;
        $clockOutTime = $this->clock_out;
        
        // If it's a Carbon instance or contains a date, extract just the time
        if ($clockInTime instanceof \Carbon\Carbon) {
            $clockInTime = $clockInTime->format('H:i:s');
        } elseif (strlen($clockInTime) > 8) {
            // It's a datetime string, extract the time portion
            $clockInTime = \Carbon\Carbon::parse($clockInTime)->format('H:i:s');
        }
        
        if ($clockOutTime instanceof \Carbon\Carbon) {
            $clockOutTime = $clockOutTime->format('H:i:s');
        } elseif (strlen($clockOutTime) > 8) {
            $clockOutTime = \Carbon\Carbon::parse($clockOutTime)->format('H:i:s');
        }

        $dateStr = $this->date instanceof \Carbon\Carbon ? $this->date->format('Y-m-d') : $this->date;
        
        $clockIn = \Carbon\Carbon::parse($dateStr . ' ' . $clockInTime);
        $clockOut = \Carbon\Carbon::parse($dateStr . ' ' . $clockOutTime);

        // Handle cases where clock_out might be after midnight (rare but possible)
        if ($clockOut->lt($clockIn)) {
            $clockOut->addDay();
        }

        return $clockOut->diffInMinutes($clockIn);
    }

    /**
     * Get formatted worked hours string
     */
    public function getWorkedHoursFormatted(): string
    {
        $minutes = $this->getWorkedMinutes();
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }

    /**
     * Scope to get attendance for a specific date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to get only completed attendance (has clock out)
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('clock_out')->where('clock_out', '!=', '00:00:00');
    }

    /**
     * Scope to get open attendance (no clock out)
     */
    public function scopeOpen($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('clock_out')->orWhere('clock_out', '00:00:00');
        });
    }

    /**
     * Get the HR user who reviewed this attendance record
     */
    public function hrReviewer()
    {
        return $this->belongsTo(User::class, 'hr_reviewed_by');
    }

    /**
     * Check if this record is incomplete (no clock out and not yet reviewed)
     */
    public function isIncomplete(): bool
    {
        return $this->isClockedIn() && !$this->isHrReviewed();
    }

    /**
     * Check if this record requires HR review
     */
    public function requiresHrReview(): bool
    {
        return $this->requires_hr_review && !$this->isHrReviewed();
    }

    /**
     * Check if this record has been reviewed by HR
     */
    public function isHrReviewed(): bool
    {
        return !is_null($this->hr_reviewed_at);
    }

    /**
     * Mark this record as requiring HR review
     */
    public function flagForHrReview(): bool
    {
        return $this->update(['requires_hr_review' => true]);
    }

    /**
     * Mark this record as reviewed by HR
     * 
     * @param int $reviewerId The HR user ID who reviewed
     * @param string|null $clockOut The correct clock out time (optional)
     * @param string|null $notes HR notes explaining the review
     */
    public function markAsReviewed(int $reviewerId, ?string $clockOut = null, ?string $notes = null): bool
    {
        $data = [
            'hr_reviewed_at' => now(),
            'hr_reviewed_by' => $reviewerId,
            'hr_notes' => $notes,
        ];

        // If a clock out time was provided, update it
        if ($clockOut) {
            $data['clock_out'] = $clockOut;
        }

        return $this->update($data);
    }

    /**
     * Scope to get attendance records pending HR review
     */
    public function scopePendingHrReview($query)
    {
        return $query->where('requires_hr_review', true)
                     ->whereNull('hr_reviewed_at');
    }

    /**
     * Scope to get attendance records that have been HR reviewed
     */
    public function scopeHrReviewed($query)
    {
        return $query->whereNotNull('hr_reviewed_at');
    }

    /**
     * Scope to get incomplete records from previous days (not today)
     * These are candidates for HR review flagging
     */
    public function scopeIncompletePreviousDays($query)
    {
        return $query->open()
                     ->where('date', '<', now()->toDateString())
                     ->where('requires_hr_review', false);
    }

    /**
     * Check if worked hours should be counted in totals
     * Only counts if: complete (has clock_out) OR has been HR reviewed
     */
    public function shouldCountHours(): bool
    {
        // If it has a valid clock out, count it
        if (!empty($this->clock_out) && $this->clock_out !== '00:00:00') {
            return true;
        }

        // If incomplete but HR has reviewed (and presumably provided clock out), count it
        if ($this->isHrReviewed()) {
            return true;
        }

        return false;
    }

    /**
     * Get worked minutes only if hours should be counted
     * Returns 0 for incomplete/unreviewed records
     */
    public function getCountableWorkedMinutes(): int
    {
        if (!$this->shouldCountHours()) {
            return 0;
        }

        return $this->getWorkedMinutes();
    }
}
