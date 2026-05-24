<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Employee extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;
    use \App\Models\Concerns\BelongsToWorkspace;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'employees';

    protected $fillable = [
        'profile_pic_path',
        'employee_id',
        'salutation',
        'first_name',
        'last_name',
        'department_id',
        'designation_id',
        'phone_number',
        'email',
        'date_of_birth',
        'gender',
        'temp_flat_no',
        'temp_pincode',
        'temp_street',
        'temp_city',
        'temp_state',
        'temp_country',
        'flat_no',
        'pincode',
        'street',
        'city',
        'state',
        'country',
        'permanent_address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'bank' ,
        'account_number',
        'branch_code' ,
        'account_type',
        'holder_relationship',
        'pay_frequency',
        'date_of_appointment',
        'identification_type',
        'id_number',
        'passport_country',
        'tax_reference_number',
        'branch_name',
        'status',
        // Attendance fields
        'branch_id',
        'attendance_enabled',
        'use_custom_working_hours',
        // ESS fields
        'ess_setup_token',
        'ess_setup_token_expires_at',
        'ess_last_login_at',
        'deletion_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'ess_setup_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'ess_setup_token_expires_at' => 'datetime',
        'ess_last_login_at' => 'datetime',
        'ess_enabled' => 'boolean',
        'attendance_enabled' => 'boolean',
        'use_custom_working_hours' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'employee_id' => $this->employee_id,
            'email' => $this->email,
            'type' => 'employee',
        ];
    }

    /**
     * Generate ESS setup token for initial password setup
     */
    public function generateEssSetupToken(): string
    {
        $token = Str::random(64);
        $this->update([
            'ess_setup_token' => $token,
            'ess_setup_token_expires_at' => now()->addHours(48),
        ]);
        return $token;
    }

    /**
     * Check if ESS setup token is valid
     */
    public function hasValidEssSetupToken(string $token): bool
    {
        return $this->ess_setup_token === $token
            && $this->ess_setup_token_expires_at
            && $this->ess_setup_token_expires_at->isFuture();
    }

    /**
     * Clear ESS setup token after password is set
     */
    public function clearEssSetupToken(): void
    {
        $this->forceFill([
            'ess_setup_token' => null,
            'ess_setup_token_expires_at' => null,
            'ess_enabled' => true,
        ])->save();
    }

    /**
     * Check if employee can access ESS
     */
    public function canAccessEss(): bool
    {
        return $this->ess_enabled && $this->password !== null;
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        $this->forceFill(['ess_last_login_at' => now()])->save();
    }

   // protected static function newFactory()
    //{

   // }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    // Relationship with Designation
    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }
    public function salary()
    {
        return $this->hasOne(EmployeeSalary::class, 'employee_id', 'id');
    }
    public function basicSalary()
    {
        return $this->hasOne(BasicSalary::class, 'employee_id');
    }
    public function travelAllowances()
    {
        return $this->hasMany(TravelAllowance::class);
    }
    public function incomePolicies(): HasMany
    {
        return $this->hasMany(IncomePolicy::class, 'employee_id', 'id');
    }
    public function bursariesScholarship()
    {
        return $this->hasOne(BursariesScholarship::class, 'employee_id');
    }
    public function companyCars()
    {
        return $this->hasOne(CompanyCar::class, 'employee_id');
    }
    public function companyCarUnderOperating()
    {
        return $this->belongsTo(CompanyCarUnderOperating::class, 'employee_id', 'employee_id');
    }
    public function garnishees()
    {
        return $this->hasMany(Garnishee::class, 'employee_id');
    }
    public function taxDirectiveEntries()
    {
        return $this->hasMany(TaxDirectiveEntry::class, 'employee_id');
    }
    public function accommodationBenefit()
{
    return $this->hasOne(AccommodationBenefit::class, 'employee_id', 'id');
}
public function garnishee()
{
    return $this->hasMany(Garnishee::class, 'employee_id');
}

public function incomeProtection()
{
    return $this->hasMany(IncomeProtection::class, 'employee_id');
}

public function maintenanceOrder()
{
    return $this->hasMany(MaintenanceOrder::class, 'employee_id');
}

public function medicalAid()
{
    return $this->hasMany(MedicalAid::class, 'employee_id');
}

public function pensionFund()
{
    return $this->hasMany(PensionFund::class, 'employee_id');
}

public function providentFund()
{
    return $this->hasMany(ProvidentFund::class, 'employee_id');
}

public function retirementAnnuity()
{
    return $this->hasMany(RetirementAnnuitie::class, 'employee_id');
}

public function unionMembershipFee()
{
    return $this->hasMany(UnionMembershipFee::class, 'employee_id');
}

public function voluntaryTaxOverDeduction()
{
    return $this->hasMany(TaxOverDeduction::class, 'employee_id');
}
public function payroll()
{
    return $this->hasOne(Payroll::class, 'employee_id', 'id');
}
public function payFrequency()
{
    return $this->belongsTo(PayFrequency::class, 'pay_frequency', 'id');
}
public function branch()
{
    return $this->belongsTo(Branch::class, 'branch_id');
}

public function workingHours()
{
    return $this->hasMany(EmployeeWorkingHour::class, 'employee_id');
}

public function attendances()
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }

/**
 * Check if employee can use mobile attendance
 */
public function canUseAttendance(): bool
{
    return $this->attendance_enabled && $this->branch_id !== null;
}

/**
 * Get working hours for a specific day (custom or branch)
 */
public function getWorkingHoursForDay(string $day): ?object
{
    $day = strtolower($day);
    
    if ($this->use_custom_working_hours) {
        return $this->workingHours()->where('day', $day)->first();
    }
    
    if ($this->branch) {
        return $this->branch->getWorkingHoursForDay($day);
    }
    
    return null;
}

/**
 * Check if employee has custom working hours configured
 */
public function hasCustomWorkingHours(): bool
{
    return $this->use_custom_working_hours && $this->workingHours()->count() > 0;
}

public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
    public function scopeCurrentWorkspace($query)
{
    return $query->where('workspace_id', getActiveWorkspace());
}
public function pay_slips()
{
    return $this->hasMany(PaySlip::class, 'employee_id');
}
public static function employeeIdFormat($id)
{
    return '#EMP' . str_pad($id, 4, '0', STR_PAD_LEFT);
}


public function salary_month()
{
    $latestSlip = $this->pay_slips()->latest('salary_month')->first();
    return $latestSlip ? $latestSlip->salary_month : null;
}    
}

