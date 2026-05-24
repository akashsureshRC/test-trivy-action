<?php

namespace App\Services\Hrm;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Models\WorkSpace;
use App\Models\Hrm\ExperienceCertificate;
use App\Models\Hrm\NOC;

/**
 * HrmSetupService
 * 
 * This service handles HRM module initialization and permission setup.
 * These methods were migrated from the legacy Employee model.
 */
class HrmSetupService
{
    /**
     * HR permission list
     */
    private static array $hr_permission = [
        'hrm manage',
        'hrm dashboard manage',
        'sidebar hrm report manage',
        'document manage',
        'document create',
        'document edit',
        'document delete',
        'attendance manage',
        'attendance create',
        'attendance edit',
        'attendance delete',
        'attendance import',
        'branch manage',
        'branch create',
        'branch edit',
        'branch delete',
        'department manage',
        'department create',
        'department edit',
        'department delete',
        'designation manage',
        'designation create',
        'designation edit',
        'designation delete',
        'employee manage',
        'employee create',
        'employee edit',
        'employee delete',
        'employee show',
        'employee profile manage',
        'employee profile show',
        'employee import',
        'documenttype manage',
        'documenttype create',
        'documenttype edit',
        'documenttype delete',
        'companypolicy manage',
        'companypolicy create',
        'companypolicy edit',
        'companypolicy delete',
        'leave manage',
        'leave create',
        'leave edit',
        'leave delete',
        'leave approver manage',
        'award manage',
        'award create',
        'award edit',
        'award delete',
        'transfer manage',
        'transfer create',
        'transfer edit',
        'transfer delete',
        'resignation manage',
        'resignation create',
        'resignation edit',
        'resignation delete',
        'travel manage',
        'travel create',
        'travel edit',
        'travel delete',
        'promotion manage',
        'promotion create',
        'promotion edit',
        'promotion delete',
        'complaint manage',
        'complaint create',
        'complaint edit',
        'complaint delete',
        'warning manage',
        'warning create',
        'warning edit',
        'warning delete',
        'termination manage',
        'termination create',
        'termination edit',
        'termination delete',
        'termination description',
        'announcement manage',
        'announcement create',
        'announcement edit',
        'announcement delete',
        'holiday manage',
        'holiday create',
        'holiday edit',
        'holiday delete',
        'holiday import',
        'attendance report manage',
        'leave report manage',
        'payroll report manage',
        'setsalary manage',
        'setsalary create',
        'setsalary edit',
        'setsalary pay slip manage',
        'setsalary show',
        'allowance manage',
        'allowance create',
        'allowance edit',
        'allowance delete',
        'commission manage',
        'commission create',
        'commission edit',
        'commission delete',
        'loan manage',
        'loan create',
        'loan edit',
        'loan delete',
        'saturation deduction manage',
        'saturation deduction create',
        'saturation deduction edit',
        'saturation deduction delete',
        'other payment manage',
        'other payment create',
        'other payment edit',
        'other payment delete',
        'overtime manage',
        'overtime create',
        'overtime edit',
        'overtime delete',
        'company contribution manage',
        'company contribution create',
        'company contribution edit',
        'company contribution delete',
        'branch name edit',
        'department name edit',
        'designation name edit',
        'event manage',
        'event create',
        'event edit',
        'event delete',
        'sidebar payroll manage',
        'sidebar hr admin manage',
        'letter joining manage',
        'letter certificate manage',
        'letter noc manage',
        'ip restrict manage',
        'ip restrict create',
        'ip restrict edit',
        'ip restrict delete',
        'bulk attendance manage',
        'tax bracket manage',
        'tax bracket create',
        'tax bracket edit',
        'tax bracket delete',
        'tax rebate manage',
        'tax rebate create',
        'tax rebate edit',
        'tax rebate delete',
        'tax threshold manage',
        'tax threshold create',
        'tax threshold edit',
        'tax threshold delete',
        'allowance tax manage',
        'allowance tax create',
        'allowance tax edit',
        'allowance tax delete',
        'user manage',
        'user chat manage',
        'user profile manage',
        'user logs history',
        'workspace manage',
        'roles manage',
    ];

    /**
     * Payroll Officer permissions
     */
    private static array $payroll_officer_permission = [
        'hrm manage',
        'hrm dashboard manage',
        'sidebar hrm report manage',
        'document manage',
        'document create',
        'document edit',
        'document delete',
        'attendance manage',
        'attendance create',
        'attendance edit',
        'attendance delete',
        'attendance import',
        'branch manage',
        'branch create',
        'branch edit',
        'branch delete',
        'department manage',
        'department create',
        'department edit',
        'department delete',
        'designation manage',
        'designation create',
        'designation edit',
        'designation delete',
        'employee manage',
        'employee create',
        'employee edit',
        'employee delete',
        'employee show',
        'employee profile manage',
        'employee profile show',
        'employee import',
        'documenttype manage',
        'documenttype create',
        'documenttype edit',
        'documenttype delete',
        'companypolicy manage',
        'companypolicy create',
        'companypolicy edit',
        'companypolicy delete',
        'leave manage',
        'leave create',
        'leave edit',
        'leave delete',
        'leave approver manage',
        'award manage',
        'award create',
        'award edit',
        'award delete',
        'transfer manage',
        'transfer create',
        'transfer edit',
        'transfer delete',
        'resignation manage',
        'resignation create',
        'resignation edit',
        'resignation delete',
        'travel manage',
        'travel create',
        'travel edit',
        'travel delete',
        'promotion manage',
        'promotion create',
        'promotion edit',
        'promotion delete',
        'complaint manage',
        'complaint create',
        'complaint edit',
        'complaint delete',
        'warning manage',
        'warning create',
        'warning edit',
        'warning delete',
        'termination manage',
        'termination create',
        'termination edit',
        'termination delete',
        'termination description',
        'announcement manage',
        'announcement create',
        'announcement edit',
        'announcement delete',
        'holiday manage',
        'holiday create',
        'holiday edit',
        'holiday delete',
        'holiday import',
        'attendance report manage',
        'leave report manage',
        'payroll report manage',
        'setsalary manage',
        'setsalary create',
        'setsalary edit',
        'setsalary pay slip manage',
        'setsalary show',
        'allowance manage',
        'allowance create',
        'allowance edit',
        'allowance delete',
        'commission manage',
        'commission create',
        'commission edit',
        'commission delete',
        'loan manage',
        'loan create',
        'loan edit',
        'loan delete',
        'saturation deduction manage',
        'saturation deduction create',
        'saturation deduction edit',
        'saturation deduction delete',
        'other payment manage',
        'other payment create',
        'other payment edit',
        'other payment delete',
        'overtime manage',
        'overtime create',
        'overtime edit',
        'overtime delete',
        'company contribution manage',
        'company contribution create',
        'company contribution edit',
        'company contribution delete',
        'branch name edit',
        'department name edit',
        'designation name edit',
        'event manage',
        'event create',
        'event edit',
        'event delete',
        'sidebar payroll manage',
        'sidebar hr admin manage',
        'letter joining manage',
        'letter certificate manage',
        'letter noc manage',
        'ip restrict manage',
        'ip restrict create',
        'ip restrict edit',
        'ip restrict delete',
        'bulk attendance manage',
        'tax bracket manage',
        'tax bracket create',
        'tax bracket edit',
        'tax bracket delete',
        'tax rebate manage',
        'tax rebate create',
        'tax rebate edit',
        'tax rebate delete',
        'tax threshold manage',
        'tax threshold create',
        'tax threshold edit',
        'tax threshold delete',
        'allowance tax manage',
        'allowance tax create',
        'allowance tax edit',
        'allowance tax delete',
        'user manage',
        'user chat manage',
        'user profile manage',
        'user logs history',
        'workspace manage',
    ];

    /**
     * Initialize default HRM data for a company/workspace
     */
    public static function defaultdata($company_id = null, $workspace_id = null): void
    {
        $company_setting = [
            "employee_prefix" => "#EMP",
            "company_start_time" => "09:00",
            "company_end_time" => "18:00",
            "ip_restrict" => "off",
        ];

        if ($company_id == null) {
            $companys = User::where('type', 'company')->get();
            foreach ($companys as $company) {
                self::createHrRole($company->id);

                $WorkSpaces = WorkSpace::where('created_by', $company->id)->get();
                foreach ($WorkSpaces as $WorkSpace) {
                    self::initializeWorkspaceDefaults($company->id, $WorkSpace->id, $company_setting);
                }
            }
        } elseif ($workspace_id == null) {
            self::createHrRole($company_id);

            $company = User::where('type', 'company')->where('id', $company_id)->first();
            if ($company) {
                $WorkSpaces = WorkSpace::where('created_by', $company->id)->get();
                foreach ($WorkSpaces as $WorkSpace) {
                    self::initializeWorkspaceDefaults($company->id, $WorkSpace->id, $company_setting);
                }
            }
        } else {
            self::createHrRole($company_id);

            $company = User::where('type', 'company')->where('id', $company_id)->first();
            if ($company) {
                $WorkSpace = WorkSpace::where('created_by', $company->id)->where('id', $workspace_id)->first();
                if ($WorkSpace) {
                    self::initializeWorkspaceDefaults($company->id, $WorkSpace->id, $company_setting);
                }
            }
        }
    }

    /**
     * Create HR role with permissions
     */
    private static function createHrRole($company_id): void
    {
        $hr_role = Role::where('name', 'hr')->where('created_by', $company_id)->where('guard_name', 'web')->first();
        if (empty($hr_role)) {
            $hr_role = new Role();
            $hr_role->name = 'hr';
            $hr_role->guard_name = 'web';
            $hr_role->module = 'Hrm';
            $hr_role->created_by = $company_id;
            $hr_role->save();

            foreach (self::$hr_permission as $permission_v) {
                $permission = Permission::where('name', $permission_v)->first();
                if (!empty($permission)) {
                    if (!$hr_role->hasPermission($permission_v)) {
                        $hr_role->givePermission($permission);
                    }
                }
            }
        }
    }

    /**
     * Initialize workspace-specific defaults
     */
    private static function initializeWorkspaceDefaults($company_id, $workspace_id, array $company_setting): void
    {
        ExperienceCertificate::defaultExpCertificat($company_id, $workspace_id);
        NOC::defaultNocCertificate($company_id, $workspace_id);

        foreach ($company_setting as $key => $p) {
            $data = [
                'key' => $key,
                'workspace' => !empty($workspace_id) ? $workspace_id : 0,
                'created_by' => $company_id,
            ];
            Setting::updateOrInsert($data, ['value' => $p]);
        }
    }

    /**
     * Give permissions to roles
     */
    public static function GivePermissionToRoles($role_id = null, $rolename = null): void
    {
        if ($role_id == null) {
            // payroll_officer
            $roles_v = Role::where('name', 'payroll_officer')->get();
            foreach ($roles_v as $role) {
                self::assignPermissionsToRole($role, self::$payroll_officer_permission);
            }

            // hr
            $roles_v = Role::where('name', 'hr')->get();
            foreach ($roles_v as $role) {
                self::assignPermissionsToRole($role, self::$hr_permission);
            }
        } else {
            if ($rolename == 'payroll_officer') {
                $role = Role::where('name', 'payroll_officer')->where('id', $role_id)->first();
                if ($role) {
                    self::assignPermissionsToRole($role, self::$payroll_officer_permission);
                }
            }

            if ($rolename == 'hr') {
                $role = Role::where('name', 'hr')->where('id', $role_id)->first();
                if ($role) {
                    self::assignPermissionsToRole($role, self::$hr_permission);
                }
            }
        }
    }

    /**
     * Assign permissions to a role
     */
    private static function assignPermissionsToRole($role, array $permissions): void
    {
        foreach ($permissions as $permission_v) {
            $permission = Permission::where('name', $permission_v)->first();
            if (!empty($permission)) {
                if (!$role->hasPermission($permission_v)) {
                    $role->givePermission($permission);
                }
            }
        }
    }
}
