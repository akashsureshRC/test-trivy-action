<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Events\DefaultData;
use App\Events\GivePermissionToRole;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;
use Lab404\Impersonate\Models\Impersonate;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements LaratrustUser, MustVerifyEmail, JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasRolesAndPermissions, Impersonate;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile_no',
        'password',
        'remember_token',
        'active_status',
        'active_workspace',
        'avatar',
        'dark_mode',
        'messenger_color',
        'active_plan',
        'seeder_run',
        'created_by',
        'lang',
        'is_enable_login',
        'is_disable',
        // Billing fields
        'trial_ends_at',
        'trial_payslips_limit',
        'trial_payslips_used',
        'billing_status',
        'suspended_at',
        'suspension_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'trial_ends_at' => 'datetime',
        'suspended_at' => 'datetime',
    ];

    public static $superadmin_activated_module = [
        'ProductService',
        'LandingPage'
    ];

    /**
     * RC ClearPay Role Hierarchy:
     * - super admin: Global Administrator (platform owner)
     * - master_admin: Master Administrator (manages multiple companies)
     * - company: Company Administrator (manages single company)
     * - payroll_officer: Payroll/HR Officer (processes payroll)
     * - employee: ESS Portal users (via separate employee auth)
     */
    public static $not_edit_role = [
        'super admin',
        'master_admin',
        'company',
        'payroll_officer',
    ];
    public  $not_emp_type = [
        'super admin',
        'master_admin',
        'company',
        'payroll_officer',
    ];
    
    /**
     * Get companies assigned to this Master Administrator.
     * Only applicable for users with type = 'master_admin'
     */
    public function assignedCompanies()
    {
        return $this->belongsToMany(User::class, 'master_admin_companies', 'master_admin_id', 'company_id')
            ->withTimestamps();
    }

    /**
     * Get Master Administrators who manage this company.
     * Only applicable for users with type = 'company'
     */
    public function masterAdmins()
    {
        return $this->belongsToMany(User::class, 'master_admin_companies', 'company_id', 'master_admin_id')
            ->withTimestamps();
    }
    
    public function scopeEmp($query)
    {
        return $query->whereNotIn('type', $this->not_emp_type);
    }
    public static function hex2rgb($hex)
    {
        $hex = str_replace("#", "", $hex);

        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        $rgb = array(
            $r,
            $g,
            $b,
        );

        //return implode(",", $rgb); // returns the rgb values separated by commas
        return $rgb; // returns an array with the rgb values
    }
    public static function getFontColor($color_code)
    {
        $rgb = self::hex2rgb($color_code);
        $R   = $G = $B = $C = $L = $color = '';

        $R = (floor($rgb[0]));
        $G = (floor($rgb[1]));
        $B = (floor($rgb[2]));

        $C = [
            $R / 255,
            $G / 255,
            $B / 255,
        ];

        for ($i = 0; $i < count($C); ++$i) {
            if ($C[$i] <= 0.03928) {
                $C[$i] = $C[$i] / 12.92;
            } else {
                $C[$i] = pow(($C[$i] + 0.055) / 1.055, 2.4);
            }
        }

        $L = 0.2126 * $C[0] + 0.7152 * $C[1] + 0.0722 * $C[2];

        if ($L > 0.179) {
            $color = 'black';
        } else {
            $color = 'white';
        }

        return $color;
    }

    /**
     * Create default roles for a company
     * Creates: payroll_officer role with basic permissions
     * 
     * @return array
     */
    public function MakeRole()
    {
        $data = [];
        
        // Payroll Officer permissions
        $payroll_officer_permission = [
            'user chat manage',
            'user profile manage',
            'user logs history',
        ];

        // Create Payroll Officer role 
        $payroll_officer_role = Role::where('name', 'payroll_officer')->where('created_by', $this->id)->where('guard_name', 'web')->first();
        if (empty($payroll_officer_role)) {
            $payroll_officer_role = new Role();
            $payroll_officer_role->name = 'payroll_officer';
            $payroll_officer_role->guard_name = 'web';
            $payroll_officer_role->module = 'Base';
            $payroll_officer_role->created_by = $this->id;
            $payroll_officer_role->save();

            foreach ($payroll_officer_permission as $permission_name) {
                $permission = Permission::where('name', $permission_name)->first();
                if ($permission) {
                    $payroll_officer_role->givePermission($permission);
                }
            }
        }
        
        $data['payroll_officer_role'] = $payroll_officer_role;

        return $data;
    }
    public static function CompanySetting($id = null, $workspace_id = null)
    {

        if (!empty($id)) {
            $company = User::find($id);
            if (empty($workspace_id)) {
                $workspace_id = $company->active_workspace;
            }
            $admin_settings = getAdminAllSetting();

            $company_setting = [
                "defult_language" => !empty($admin_settings['defult_language']) ? $admin_settings['defult_language'] : 'en',
                "defult_timezone" => !empty($admin_settings['defult_timezone']) ? $admin_settings['defult_timezone'] : 'Asia/Kolkata',
                "site_date_format" => "d-m-Y",
                "site_time_format" => "g:i A",
                "title_text" => !empty($admin_settings['title_text']) ? $admin_settings['title_text'] : "RC ClearPay",
                "footer_text" => !empty($admin_settings['footer_text']) ? $admin_settings['footer_text'] : "Copyright © RC ClearPay",
                "site_rtl" => !empty($admin_settings['site_rtl']) ? $admin_settings['site_rtl'] : "off",
                "cust_darklayout" => !empty($admin_settings['cust_darklayout']) ? $admin_settings['cust_darklayout'] : "off",
                "site_transparent" => !empty($admin_settings['site_transparent']) ? $admin_settings['site_transparent'] : "on",
                "color" =>  "theme-1",
                "invoice_prefix" => "#INVO",
                "invoice_starting_number" => "1",
                "proforma_invoice_prefix" => "#PRIN0",
                "proforma_invoice_starting_number" => "1",
                "invoice_template" => "template1",
                "invoice_color" => "ffffff",
                "invoice_shipping_display" => "on",
                "invoice_title" => "",
                "invoice_notes" => "",
                "proposal_prefix" => "#PROP0",
                "proposal_starting_number" => "1",
                "proposal_template" => "template1",
                "proposal_color" => "ffffff",
                "proposal_shipping_display" => "on",
                "proposal_title" => "",
                "proposal_notes" => "",
                "email_setting" => "smtp",
                "storage_setting" => !empty($admin_settings['storage_setting']) ? $admin_settings['storage_setting'] : "local",

            ];
            foreach ($company_setting as $key => $value) {
                // Define the data to be updated or inserted
                $data = [
                    'key' => $key,
                    'workspace' => $workspace_id,
                    'created_by' => $id,
                ];
                // Check if the record exists, and update or insert accordingly
                Setting::updateOrInsert($data, ['value' => $value]);
            }
        }
    }
    public function ActiveWorkspaceName()
    {
        $name = $this->name;
        $workspace = WorkSpace::find(getActiveWorkspace());
        if ($workspace) {
            $name = $workspace->name;
        }
        return $name;
    }
    public function countCompany()
    {
        return User::where('type', '=', 'company')->where('created_by', '=', creatorId())->count();
    }
    public function countPaidCompany()
    {
        // Subscription system removed - return 0
        return 0;
    }

    /**
     * Coupon relationship removed - subscription system disabled
     */
    public function user_coupon_user($coupon)
    {
        return 0;
    }

    /**
     * Plan assignment removed - subscription system disabled
     * All users have unlimited access by default
     */
    public function assignPlan($plan_id = null, $duration = null, $modules = null, $counter = null, $user_id = null)
    {
        // Subscription system removed - all users have unlimited access
        return ['is_success' => true];
    }

    /**
     * User count by plan - subscription system disabled
     */
    public function UserCount($id, $plan)
    {
        // Subscription system removed - no user limits
        return true;
    }

    // ==========================================
    // BILLING SYSTEM METHODS
    // ==========================================

    /**
     * Get billing cycles for this user
     */
    public function billingCycles()
    {
        return $this->hasMany(\App\Models\Billing\BillingCycle::class);
    }

    /**
     * Get invoices for this user
     */
    public function invoices()
    {
        return $this->hasMany(\App\Models\Billing\Invoice::class);
    }

    /**
     * Get payments for this user
     */
    public function billingPayments()
    {
        return $this->hasMany(\App\Models\Billing\BillingPayment::class);
    }

    /**
     * Scope: users currently on trial (time-based OR usage-based).
     */
    public function scopeOnTrial($query)
    {
        $defaultLimit = \App\Models\Billing\BillingSetting::getTrialPayslipsLimit();

        return $query->where(function ($q) use ($defaultLimit) {
            $q->where(function ($q2) {
                $q2->whereNotNull('trial_ends_at')->where('trial_ends_at', '>', now());
            })->orWhere(function ($q2) use ($defaultLimit) {
                $q2->whereRaw('trial_payslips_used < COALESCE(trial_payslips_limit, ?)', [$defaultLimit]);
            });
        });
    }

    /**
     * Scope: users NOT on trial (paid).
     */
    public function scopePaid($query)
    {
        $defaultLimit = \App\Models\Billing\BillingSetting::getTrialPayslipsLimit();

        return $query->where(function ($q) use ($defaultLimit) {
            $q->where(function ($q2) {
                $q2->whereNull('trial_ends_at')->orWhere('trial_ends_at', '<=', now());
            })->where(function ($q2) use ($defaultLimit) {
                $q2->whereRaw('trial_payslips_used >= COALESCE(trial_payslips_limit, ?)', [$defaultLimit]);
            });
        });
    }

    /**
     * Check if user is on trial
     */
    public function isOnTrial(): bool
    {
        // Check time-based trial
        if ($this->trial_ends_at && $this->trial_ends_at->isFuture()) {
            return true;
        }

        // Check usage-based trial
        $limit = $this->trial_payslips_limit ?? \App\Models\Billing\BillingSetting::getTrialPayslipsLimit();
        if ($this->trial_payslips_used < $limit) {
            return true;
        }

        return false;
    }

    /**
     * Check if trial has expired
     */
    public function hasTrialExpired(): bool
    {
        // If never had a trial, not expired
        if (!$this->trial_ends_at && $this->trial_payslips_used == 0) {
            return false;
        }

        return !$this->isOnTrial();
    }

    /**
     * Get remaining trial days
     */
    public function getRemainingTrialDays(): int
    {
        if (!$this->trial_ends_at || $this->trial_ends_at->isPast()) {
            return 0;
        }
        return now()->diffInDays($this->trial_ends_at);
    }

    /**
     * Get remaining trial payslips
     */
    public function getRemainingTrialPayslips(): int
    {
        $limit = $this->trial_payslips_limit ?? \App\Models\Billing\BillingSetting::getTrialPayslipsLimit();
        return max(0, $limit - $this->trial_payslips_used);
    }

    /**
     * Start trial for user
     */
    public function startTrial(): void
    {
        $trialDays = \App\Models\Billing\BillingSetting::getTrialDays();
        $trialPayslips = \App\Models\Billing\BillingSetting::getTrialPayslipsLimit();

        $this->update([
            'trial_ends_at' => now()->addDays($trialDays),
            'trial_payslips_limit' => $trialPayslips,
            'trial_payslips_used' => 0,
        ]);
    }

    /**
     * Increment trial payslips used
     */
    public function incrementTrialPayslips(int $count = 1): void
    {
        $this->increment('trial_payslips_used', $count);
    }

    /**
     * Check if user is suspended
     */
    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    /**
     * Suspend user account
     */
    public function suspend(string $reason = null): void
    {
        $this->forceFill([
            'billing_status' => 'suspended',
            'suspended_at' => now(),
            'suspension_reason' => $reason ?? 'Payment overdue',
        ])->save();
    }

    /**
     * Reinstate user account
     */
    public function reinstate(): void
    {
        $this->forceFill([
            'billing_status' => 'active',
            'suspended_at' => null,
            'suspension_reason' => null,
        ])->save();
    }

    /**
     * Get outstanding balance
     */
    public function getOutstandingBalance(): float
    {
        return $this->invoices()
            ->whereIn('status', ['pending', 'overdue'])
            ->sum('total_amount');
    }

    /**
     * Get active billing cycle
     */
    public function getActiveBillingCycle()
    {
        return \App\Models\Billing\BillingCycle::getOrCreateForUser($this->id);
    }

    /**
     * Check if user is a company owner (billable)
     */
    public function isBillable(): bool
    {
        return $this->type === 'company';
    }
}
