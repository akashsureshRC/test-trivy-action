<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class PermissionTableSeeder extends Seeder
{
    /**
     * RC ClearPay Role Hierarchy:
     * - super admin: Global Administrator (platform owner)
     * - master_admin: Master Administrator (manages multiple companies)
     * - company: Company Administrator (manages single company)
     * - payroll_officer: Payroll/HR Officer (processes payroll)
     * - employee: ESS Portal users (via separate employee auth)
     *
     * @return void
     */
    public function run()
    {
        Artisan::call('cache:forget spatie.permission.cache');
        Artisan::call('cache:clear');

        // Super Admin (Global Administrator)
        $admin = User::where('type','super admin')->first();
        if(empty($admin))
        {
            $admin = new User();
            $admin->name = 'Global Admin';
            $admin->email = 'globaladmin@example.com';
            $admin->password = Hash::make('123456');
            $admin->email_verified_at = date('Y-m-d H:i:s');
            $admin->type = 'super admin';
            $admin->active_status = 1;
            $admin->active_workspace = 0;
            $admin->dark_mode = 0;
            $admin->lang = 'en';
            $admin->workspace_id = 0;
            $admin->created_by = 0;
            $admin->save();

            $role = Role::where('name','super admin')->where('guard_name','web')->exists();
            if(!$role)
            {
                $superAdminRole = Role::create(
                    [
                        'name' => 'super admin',
                        'created_by' => 0,
                    ]
                );
            }
            $role_r = Role::where('name','super admin')->first();
            $admin->addRole($role_r);
        }

        // Global Administrator (Super Admin) Permissions
        $admin_permission = [
            'user manage',
            'user create',
            'user edit',
            'user delete',
            'user profile manage',
            'user reset password',
            'user login manage',
            'user import',
            'user logs history',
            'setting manage',
            'setting storage manage',
            'module manage',
            'module add',
            'module remove',
            'module edit',
            'email template manage',
            'language manage',
            'language create',
            'language delete',
            'helpdesk manage',
            'helpdesk ticket manage',
            'helpdesk ticket create',
            'helpdesk ticket edit',
            'helpdesk ticket show',
            'helpdesk ticket reply',
            'helpdesk ticket delete',
            'helpdeskticket setup manage',
            'helpdesk ticketcategory manage',
            'helpdesk ticketcategory create',
            'helpdesk ticketcategory edit',
            'helpdesk ticketcategory delete',
            'api key setting manage',
            'api key setting create',
            'api key setting edit',
            'api key setting delete',
            'notification template manage',
            // Master Admin management
            'master admin manage',
            'master admin create',
            'master admin edit',
            'master admin delete',
            // Company oversight
            'company manage',
            'company create',
            'company edit',
            'company delete',
            'company deactivate',
            'company reactivate',
            // Global payroll oversight
            'payroll cycles view all',
            'statutory reporting view all',
        ];

        // Master Administrator Permissions
        $master_admin_permission = [
            'user manage',
            'user create',
            'user edit',
            'user delete',
            'user profile manage',
            'user chat manage',
            'user reset password',
            'user login manage',
            'user import',
            'user logs history',
            'workspace manage',
            'workspace create',
            'workspace edit',
            'workspace delete',
            'roles manage',
            'roles create',
            'roles edit',
            'roles delete',
            'setting manage',
            'helpdesk ticket manage',
            'helpdesk ticket create',
            'helpdesk ticket edit',
            'helpdesk ticket show',
            'helpdesk ticket reply',
            'helpdesk ticket delete',
            // Company management (assigned companies only)
            'company manage',
            'company create',
            'company edit',
            // Consolidated reporting
            'consolidated reports manage',
        ];

        // Company Administrator Permissions
        // Note: Company admins cannot manage roles - they can only create payroll_officer users
        $company_permission = [
            'user manage',
            'user create',
            'user edit',
            'user delete',
            'user profile manage',
            'user chat manage',
            'user reset password',
            'user login manage',
            'user import',
            'user logs history',
            'workspace manage',
            'workspace create',
            'workspace edit',
            'workspace delete',
            'setting manage',
            'helpdesk ticket manage',
            'helpdesk ticket create',
            'helpdesk ticket edit',
            'helpdesk ticket show',
            'helpdesk ticket reply',
            'helpdesk ticket delete',
        ];

        // Payroll Officer Permissions
        // Company permissions subset + required HR/payroll access permissions.
        // Excluded: workspace create/edit/delete, setting manage, billing
        $payroll_officer_permission = array_values(array_filter($company_permission, function ($permission) {
            if (in_array($permission, ['workspace create', 'workspace edit', 'workspace delete', 'setting manage'])) {
                return false;
            }

            if (stripos($permission, 'billing') !== false) {
                return false;
            }

            return true;
        }));

        $payroll_officer_permission = array_values(array_unique(array_merge($payroll_officer_permission, [
            'hrm manage',
            'hrm dashboard manage',
            'sidebar payroll manage',
            'employee manage',
            'employee show',
            'attendance manage',
            'leave manage',
            'setsalary manage',
            'setsalary show',
            'setsalary pay slip manage',
        ])));

        // =====================================================
        // Create Permissions and Assign to Roles
        // =====================================================

        $superAdminRole = Role::where('name','super admin')->first();
        
        // Create and assign Super Admin permissions
        foreach ($admin_permission as $value)
        {
            $permission = Permission::where('name',$value)->first();
            if(empty($permission))
            {
                $permission = Permission::create(
                    [
                        'name' => $value,
                        'guard_name' => 'web',
                        'module' => 'General',
                        'created_by' => $admin->id,
                        "created_at" => date('Y-m-d H:i:s'),
                        "updated_at" => date('Y-m-d H:i:s')
                    ]
                );
            }
            if(!$superAdminRole->hasPermission($value))
            {
                $superAdminRole->givePermission($permission);
            }
        }

        // Create Master Administrator role
        $role = Role::where('name','master_admin')->where('guard_name','web')->exists();
        if(!$role)
        {
            $master_admin_role = Role::create(
                [
                    'name' => 'master_admin',
                    'created_by' => $admin->id,
                ]
            );
        }
        $master_admin_role = Role::where('name','master_admin')->first();
        foreach ($master_admin_permission as $value)
        {
            $permission = Permission::where('name',$value)->first();
            if(empty($permission))
            {
                $permission = Permission::create(
                    [
                        'name' => $value,
                        'guard_name' => 'web',
                        'module' => 'General',
                        'created_by' => $admin->id,
                        "created_at" => date('Y-m-d H:i:s'),
                        "updated_at" => date('Y-m-d H:i:s')
                    ]
                );
            }
            if(!$master_admin_role->hasPermission($value))
            {
                $master_admin_role->givePermission($permission);
            }
        }

        // Create Company Administrator role
        $role = Role::where('name','company')->where('guard_name','web')->exists();
        if(!$role)
        {
            $company_role = Role::create(
                [
                    'name' => 'company',
                    'created_by' => $admin->id,
                ]
            );
        }
        $company_role = Role::where('name','company')->first();
        foreach ($company_permission as $value)
        {
            $permission = Permission::where('name',$value)->first();
            if(empty($permission))
            {
                $permission = Permission::create(
                    [
                        'name' => $value,
                        'guard_name' => 'web',
                        'module' => 'General',
                        'created_by' => $admin->id,
                        "created_at" => date('Y-m-d H:i:s'),
                        "updated_at" => date('Y-m-d H:i:s')
                    ]
                );
            }
            if(!$company_role->hasPermission($value))
            {
                $company_role->givePermission($permission);
            }
        }

        // Create Payroll Officer role
        $role = Role::where('name','payroll_officer')->where('guard_name','web')->exists();
        if(!$role)
        {
            $payroll_officer_role = Role::create(
                [
                    'name' => 'payroll_officer',
                    'created_by' => $admin->id,
                ]
            );
        }
        $payroll_officer_role = Role::where('name','payroll_officer')->first();
        foreach ($payroll_officer_permission as $value)
        {
            $permission = Permission::where('name',$value)->first();
            if(empty($permission))
            {
                $permission = Permission::create(
                    [
                        'name' => $value,
                        'guard_name' => 'web',
                        'module' => 'General',
                        'created_by' => $admin->id,
                        "created_at" => date('Y-m-d H:i:s'),
                        "updated_at" => date('Y-m-d H:i:s')
                    ]
                );
            }
            if(!$payroll_officer_role->hasPermission($value))
            {
                $payroll_officer_role->givePermission($permission);
            }
        }

        // Assign role to existing company user if exists
        $company = User::where('type','company')->first();
        try{
            $assigned_role = $company->roles->first();
        }catch(\Exception $e){
            $assigned_role = null;
        }
        if(!$assigned_role && !empty($company))
        {
            $company->addRole($company_role);
        }

        // Assign role to existing payroll officer users if they don't already have one
        $payrollOfficers = User::where('type','payroll_officer')->get();
        foreach ($payrollOfficers as $payrollOfficer) {
            try {
                $assigned_role = $payrollOfficer->roles->first();
            } catch (\Exception $e) {
                $assigned_role = null;
            }

            if(!$assigned_role)
            {
                $payrollOfficer->addRole($payroll_officer_role);
            }
        }
    }
}
