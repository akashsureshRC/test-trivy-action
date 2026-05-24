<?php

namespace App\Listeners;

use App\Events\MasterAdminMenuEvent;

class MasterAdminMenuListener
{
    /**
     * Handle the event.
     * Master Administrator Menu - manages multiple assigned companies
     */
    public function handle(MasterAdminMenuEvent $event): void
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
        
        // Customers (assigned customers) - use same page as Global Admin
        $menu->add([
            'category' => 'General',
            'title' => __('Customers'),
            'icon' => 'building',
            'name' => 'customers',
            'parent' => null,
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'users.index',
            'module' => $module,
            'permission' => ''
        ]);
        
        // Invoices - use same page as Global Admin
        $menu->add([
            'category' => 'General',
            'title' => __('Invoices'),
            'icon' => 'file-invoice',
            'name' => 'invoices',
            'parent' => null,
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'billing.invoices.index',
            'module' => $module,
            'permission' => ''
        ]);
        
        // Payroll Cycles
        $menu->add([
            'category' => 'General',
            'title' => __('Payroll Cycles'),
            'icon' => 'calendar-stats',
            'name' => 'payroll-cycles',
            'parent' => null,
            'order' => 25,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'master-admin.payroll-cycles',
            'module' => $module,
            'permission' => ''
        ]);
        
        // Reports
        $menu->add([
            'category' => 'General',
            'title' => __('Reports'),
            'icon' => 'report-analytics',
            'name' => 'reports',
            'parent' => null,
            'order' => 30,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'master-admin.reports',
            'module' => $module,
            'permission' => ''
        ]);
    }
}
