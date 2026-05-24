<?php

namespace Database\Seeders;

use App\Events\DefaultData;
use App\Models\User;
use App\Models\WorkSpace;
use App\Models\Warehouse;
use App\Models\Hrm\Employee;
use App\Events\GivePermissionToRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Super Admin (Global Administrator)
        $admin = User::where('type', 'super admin')->first();

        // =====================================================
        // Master Administrator
        // =====================================================
        $masterAdmin = User::where('type', 'master_admin')->first();
        if (empty($masterAdmin)) {
            $masterAdmin = new User();
            $masterAdmin->name = 'Master Admin';
            $masterAdmin->email = 'masteradmin@example.com';
            $masterAdmin->password = Hash::make('123456');
            $masterAdmin->email_verified_at = date('Y-m-d H:i:s');
            $masterAdmin->type = 'master_admin';
            $masterAdmin->active_status = 1;
            $masterAdmin->active_workspace = 0;
            $masterAdmin->dark_mode = 0;
            $masterAdmin->lang = 'en';
            $masterAdmin->workspace_id = 0;
            $masterAdmin->created_by = $admin->id;
            $masterAdmin->save();

            $role_r = Role::where('name', 'master_admin')->first();
            if ($role_r) {
                $masterAdmin->addRole($role_r);
            }
        }

        // =====================================================
        // Company Administrator
        // =====================================================
        $company = User::where('type', 'company')->first();
        if (empty($company)) {
            $company = new User();
            $company->name = 'Company Admin';
            $company->email = 'companyadmin@example.com';
            $company->password = Hash::make('123456');
            $company->email_verified_at = date('Y-m-d H:i:s');
            $company->type = 'company';
            $company->active_status = 1;
            $company->active_workspace = 1;
            $company->dark_mode = 0;
            $company->lang = 'en';
            $company->workspace_id = 1;
            $company->created_by = $admin->id;
            $company->save();

            $role_r = Role::where('name', 'company')->first();
            if ($role_r) {
                $company->addRole($role_r);
            }

            $company->MakeRole();

            // Create WorkSpace
            $workspace = new WorkSpace();
            $workspace->name = 'Reliance';
            $workspace->slug = 'reliance';
            $workspace->created_by = $company->id;
            $workspace->save();

            $company = User::find($company->id);
            $company->workspace_id = $workspace->id;
            $company->active_workspace = $workspace->id;
            $company->save();

            // Company setting save
            User::CompanySetting($company->id);
        }

        // Assign Master Admin to Company
        $masterAdmin = User::where('type', 'master_admin')->first();
        $company = User::where('type', 'company')->first();
        if ($masterAdmin && $company) {
            $masterAdmin->assignedCompanies()->syncWithoutDetaching([$company->id]);

            // Update master admin workspace
            if ($masterAdmin->active_workspace == 0) {
                $workspace = WorkSpace::where('created_by', $company->id)->first();
                if ($workspace) {
                    $masterAdmin->workspace_id = $workspace->id;
                    $masterAdmin->active_workspace = $workspace->id;
                    $masterAdmin->save();
                }
            }
        }

        // =====================================================
        // Payroll Officer
        // =====================================================
        $payrollOfficer = User::where('type', 'payroll_officer')->first();
        if (empty($payrollOfficer) && !empty($company)) {
            $payrollOfficer = new User();
            $payrollOfficer->name = 'Payroll Officer';
            $payrollOfficer->email = 'payroll@example.com';
            $payrollOfficer->password = Hash::make('123456');
            $payrollOfficer->email_verified_at = date('Y-m-d H:i:s');
            $payrollOfficer->type = 'payroll_officer';
            $payrollOfficer->active_status = 1;
            $payrollOfficer->dark_mode = 0;
            $payrollOfficer->lang = 'en';
            $payrollOfficer->created_by = $company->id;
            $payrollOfficer->save();

            // Assign workspace
            $workspace = WorkSpace::where('created_by', $company->id)->first();
            if ($workspace) {
                $payrollOfficer->workspace_id = $workspace->id;
                $payrollOfficer->active_workspace = $workspace->id;
                $payrollOfficer->save();
            }

            // Create and assign payroll_officer role
            $role = Role::where('name', 'payroll_officer')->where('guard_name', 'web')->first();
            if (!$role) {
                $role = Role::create([
                    'name' => 'payroll_officer',
                    'guard_name' => 'web',
                    'module' => 'Hrm',
                    'created_by' => $company->id,
                ]);
            }
            $payrollOfficer->addRole($role);
        }

        // =====================================================
        // Employee (John Doe)
        // =====================================================
        $employee = Employee::where('employee_id', 'EMP001')
            ->orWhere('email', 'partha_path@yahoo.co.in')
            ->first();
        if (empty($employee) && !empty($company)) {
            $workspace = WorkSpace::where('created_by', $company->id)->first();

            $employee = new Employee();
            $employee->employee_id = 'EMP001';
            $employee->first_name = 'John';
            $employee->last_name = 'Doe';
            $employee->email = 'partha_path@yahoo.co.in';
            $employee->password = Hash::make('asasasas');
            $employee->gender = 'Male';
            $employee->status = 'Active';
            $employee->date_of_appointment = date('Y-m-d');
            $employee->workspace_id = $workspace ? $workspace->id : 1;
            $employee->ess_enabled = 1;
            $employee->save();
        }
    }
}
