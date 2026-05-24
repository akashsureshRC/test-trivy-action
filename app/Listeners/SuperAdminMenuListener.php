<?php

namespace App\Listeners;

use App\Events\SuperAdminMenuEvent;

class SuperAdminMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(SuperAdminMenuEvent $event): void
    {
        $module = 'Base';
        $menu = $event->menu;
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
        $menu->add([
            'category' => 'General',
            'title' => __('Customers'),
            'icon' => 'users',
            'name' => 'customers',
            'parent' => null,
            'order' => 50,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'users.index',
            'module' => $module,
            'permission' => 'user manage'
        ]);
        
        // User Management
        $menu->add([
            'category' => 'Settings',
            'title' => __('User Management'),
            'icon' => 'shield-check',
            'name' => 'master-admin',
            'parent' => null,
            'order' => 990,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'master-admin.index',
            'module' => $module,
            'permission' => ''
        ]);

        // Payroll Cycles Menu
        $menu->add([
            'category' => 'General',
            'title' => __('Payroll Cycles'),
            'icon' => 'calendar-stats',
            'name' => 'payroll-cycles',
            'parent' => null,
            'order' => 60,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'super-admin.payroll-cycles',
            'module' => $module,
            'permission' => ''
        ]);

        // Reports Menu
        $menu->add([
            'category' => 'General',
            'title' => __('Reports'),
            'icon' => 'chart-bar',
            'name' => 'reports',
            'parent' => null,
            'order' => 65,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'super-admin.reports',
            'module' => $module,
            'permission' => ''
        ]);

        // Billing Menu with sub-items
        $menu->add([
            'category' => 'General',
            'title' => __('Billing'),
            'icon' => 'receipt-2',
            'name' => 'billing',
            'parent' => null,
            'order' => 55,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => ''
        ]);
        
        $menu->add([
            'category' => 'General',
            'title' => __('Invoices'),
            'icon' => '',
            'name' => 'billing-invoices',
            'parent' => 'billing',
            'order' => 1,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'billing.invoices.index',
            'module' => $module,
            'permission' => ''
        ]);
        
        $menu->add([
            'category' => 'General',
            'title' => __('Pricing Tiers'),
            'icon' => '',
            'name' => 'billing-tiers',
            'parent' => 'billing',
            'order' => 2,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'billing.tiers.index',
            'module' => $module,
            'permission' => ''
        ]);
        
        $menu->add([
            'category' => 'General',
            'title' => __('Settings'),
            'icon' => '',
            'name' => 'billing-settings',
            'parent' => 'billing',
            'order' => 3,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'billing.settings',
            'module' => $module,
            'permission' => ''
        ]);

        $menu->add([
            'category' => 'Operations',
            'title' => __('Helpdesk'),
            'icon' => 'headphones',
            'name' => 'helpdesk',
            'parent' => null,
            'order' => 200,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'helpdesk manage'
        ]);
        $menu->add([
            'category' => 'Operations',
            'title' => __('Tickets'),
            'icon' => '',
            'name' => 'tickets',
            'parent' => 'helpdesk',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'helpdesk.index',
            'module' => $module,
            'permission' => 'helpdesk ticket manage'
        ]);
        $menu->add([
            'category' => 'Operations',
            'title' => __('Categories'),
            'icon' => '',
            'name' => 'categories',
            'parent' => 'helpdesk',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'helpdeskticket-category.index',
            'module' => $module,
            'permission' => 'helpdeskticket setup manage'
        ]);

        $menu->add([
            'category' => 'Settings',
            'title' => __('Email Template'),
            'icon' => 'template',
            'name' => 'email-templates',
            'parent' => null,
            'order' => 150,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'email-templates.index',
            'module' => $module,
            'permission' => 'email template manage'
        ]);
        

        // Tax Settings
        $menu->add([
            'category' => 'General',
            'title' => __('Tax Settings'),
            'icon' => 'receipt-tax',
            'name' => 'tax-settings',
            'parent' => null,
            'order' => 70,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'tax-years.index',
            'module' => $module,
            'permission' => ''
        ]);

        $menu->add([
            'category' => 'Settings',
            'title' => __('Settings'),
            'icon' => 'settings',
            'name' => 'settings',
            'parent' => null,
            'order' => 1000,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'settings.index',
            'module' => $module,
            'permission' => 'setting manage'
        ]);
       

    }
}
