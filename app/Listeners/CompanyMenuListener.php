<?php

namespace App\Listeners;

use App\Events\CompanyMenuEvent;
use Illuminate\Support\Facades\Auth;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'Base';
        $menu = $event->menu;
        
        // Dashboard
        $menu->add([
            'category' => 'General',
            'title' => __('Dashboard'),
            'icon' => 'home',
            'name' => 'dashboard',
            'parent' => null,
            'order' => 1,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'dashboard',
            'module' => $module,
            'permission' => ''
        ]);
        
        // ===========================================
        // HRM / Payroll Menu Items
        // ===========================================
        
        // Employees
        $menu->add([
            'category' => 'HR',
            'title' => __('Employees'),
            'icon' => 'user',
            'name' => 'employee',
            'parent' => null,
            'order' => 100,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'employees.list',
            'module' => $module,
            'permission' => 'employee manage'
        ]);
        
        // Payment Run
        $menu->add([
            'category' => 'HR',
            'title' => __('Payment Run'),
            'icon' => 'credit-card',
            'name' => 'payrun',
            'parent' => null,
            'order' => 200,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'payrun.index',
            'module' => $module,
            'permission' => 'setsalary pay slip manage'
        ]);
        
        // Filing Menu
        $menu->add([
            'category' => 'HR',
            'title' => __('Filing'),
            'icon' => 'file-text',
            'name' => 'filing',
            'parent' => null,
            'order' => 300,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => ''
        ]);
        $menu->add([
            'category' => 'HR',
            'title' => __('Monthly Submissions'),
            'icon' => '',
            'name' => 'monthly-submissions',
            'parent' => 'filing',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'filing.create',
            'module' => $module,
            'permission' => ''
        ]);
        $menu->add([
            'category' => 'HR',
            'title' => __('Bi Annual Filing'),
            'icon' => '',
            'name' => 'biannual-filing',
            'parent' => 'filing',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'filing.annual',
            'module' => $module,
            'permission' => ''
        ]);
        $menu->add([
            'category' => 'HR',
            'title' => __('OID Return'),
            'icon' => '',
            'name' => 'oid-returns',
            'parent' => 'filing',
            'order' => 30,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'filing.return',
            'module' => $module,
            'permission' => ''
        ]);
        $menu->add([
            'category' => 'HR',
            'title' => __('Tax Year Report'),
            'icon' => '',
            'name' => 'tax-year-report',
            'parent' => 'filing',
            'order' => 40,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'filing.tax-year-report',
            'module' => $module,
            'permission' => ''
        ]);
        
        // Leave Menu
        $menu->add([
            'category' => 'HR',
            'title' => __('Leave'),
            'icon' => 'calendar',
            'name' => 'leave-policies',
            'parent' => null,
            'order' => 400,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => ''
        ]);
        $menu->add([
            'category' => 'HR',
            'title' => __('Leave Requests'),
            'icon' => '',
            'name' => 'leave-requests',
            'parent' => 'leave-policies',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'leave.index',
            'module' => $module,
            'permission' => ''
        ]);
        $menu->add([
            'category' => 'HR',
            'title' => __('Record Leave'),
            'icon' => 'calendar-minus',
            'name' => 'record-leave',
            'parent' => 'leave-policies',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'filing.leaverecord',
            'module' => $module,
            'permission' => ''
        ]);
        $menu->add([
            'category' => 'HR',
            'title' => __('Entitlement Policies'),
            'icon' => '',
            'name' => 'entilement-policies',
            'parent' => 'leave-policies',
            'order' => 30,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'employee-entitlement.index',
            'module' => $module,
            'permission' => ''
        ]);
        $menu->add([
            'category' => 'HR',
            'title' => __('Leave Management'),
            'icon' => 'calendar-event',
            'name' => 'leave-management',
            'parent' => 'leave-policies',
            'order' => 40,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'leave-management.index',
            'module' => $module,
            'permission' => ''
        ]);
        
        // Attendance Menu
        $menu->add([
            'category' => 'HR',
            'title' => __('Attendance'),
            'icon' => 'clock',
            'name' => 'attendance',
            'parent' => null,
            'order' => 500,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => ''
        ]);
        $menu->add([
            'category' => 'HR',
            'title' => __('Attendance'),
            'icon' => '',
            'name' => 'mark-attendance',
            'parent' => 'attendance',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'attendance.index',
            'module' => $module,
            'permission' => ''
        ]);
        $menu->add([
            'category' => 'HR',
            'title' => __('Bulk Attendance'),
            'icon' => '',
            'name' => 'bulk-attendance',
            'parent' => 'attendance',
            'order' => 15,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'attendance.bulkattendance',
            'module' => $module,
            'permission' => ''
        ]);
        $menu->add([
            'category' => 'HR',
            'title' => __('Report'),
            'icon' => '',
            'name' => 'attendance-report',
            'parent' => 'attendance',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'report.detailed.attendance',
            'module' => $module,
            'permission' => 'attendance report manage'
        ]);
        
        // ===========================================
        // Billing Menu (hidden from payroll officers)
        // ===========================================
        if (Auth::user()->type !== 'payroll_officer') {
            $menu->add([
                'category' => 'HR',
                'title' => __('My Billing'),
                'icon' => 'receipt-2',
                'name' => 'my-billing',
                'parent' => null,
                'order' => 600,
                'ignore_if' => [],
                'depend_on' => [],
                'route' => 'my-billing.index',
                'module' => $module,
                'permission' => ''
            ]);
        }
        
        // ===========================================
        // Settings Menu
        // ===========================================
        $menu->add([
            'category' => 'Settings',
            'title' => __('Settings'),
            'icon' => 'settings',
            'name' => 'settings',
            'parent' => null,
            'order' => 1000,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'setting manage'
        ]);

        $menu->add([
            'category' => 'Settings',
            'title' => __('ESS Management'),
            'icon' => '',
            'name' => 'ess-management',
            'parent' => 'settings',
            'order' => 6,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'ess-management.index',
            'module' => $module,
            'permission' => 'setting manage'
        ]);

        $menu->add([
            'category' => 'Settings',
            'title' => __('System Settings'),
            'icon' => '',
            'name' => 'system-settings',
            'parent' => 'settings',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'settings.index',
            'module' => $module,
            'permission' => 'setting manage'
        ]);

        $menu->add([
            'category' => 'Settings',
            'title' => __('System Setup'),
            'icon' => '',
            'name' => 'system-setup',
            'parent' => 'settings',
            'order' => 11,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'branch.index',
            'module' => $module,
            'permission' => 'setting manage'
        ]);

        $menu->add([
            'category' => 'Settings',
            'title' => __('User Management'),
            'icon' => 'users',
            'name' => 'user-management',
            'parent' => 'settings',
            'order' => 5,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'users.index',
            'module' => $module,
            'permission' => 'user manage'
        ]);
    }
}
