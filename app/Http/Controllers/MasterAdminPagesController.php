<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkSpace;
use App\Models\MasterAdminCompany;
use App\Models\Hrm\Employee;
use App\Models\Hrm\PaySlip;
use App\Models\Hrm\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MasterAdminPagesController extends Controller
{
    /**
     * Middleware to ensure only master_admin can access these pages
     * Note: returnToMasterAdmin() is excluded as user will be logged in as company at that point
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Allow returnToMasterAdmin even when logged in as company
            if (request()->routeIs('master-admin.return')) {
                return $next($request);
            }
            
            if (Auth::user()->type !== 'master_admin') {
                abort(403, 'Unauthorized - Master Administrator access only');
            }
            return $next($request);
        });
    }

    /**
     * Master Admin Dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get assigned company IDs
        $assignedCompanyIds = MasterAdminCompany::where('master_admin_id', $user->id)
            ->pluck('company_id')
            ->toArray();
        
        // Get dashboard data for assigned companies only
        $dashboardData = $this->getMasterAdminDashboardData($assignedCompanyIds);

        // Recent customers (latest 10, assigned only)
        $recentCustomers = User::whereIn('id', $assignedCompanyIds)
            ->where('type', 'company')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        // Flag to indicate this is master admin view (to show "Assigned Customers" instead of "Total Customers")
        $isMasterAdmin = true;
        
        return view('shared.dashboard', compact(
            'user',
            'dashboardData',
            'recentCustomers',
            'isMasterAdmin'
        ));
    }

    /**
     * Get Master Admin Dashboard Metrics (filtered by assigned companies)
     */
    private function getMasterAdminDashboardData($assignedCompanyIds)
    {
        $currentMonth = now()->format('Y-m');

        // Total Customers (assigned only)
        $totalCustomers = count($assignedCompanyIds);

        // Get workspace IDs for assigned companies
        $workspaceIds = WorkSpace::whereIn('created_by', $assignedCompanyIds)->pluck('id')->toArray();

        // Total Workspaces
        $totalWorkspaces = count($workspaceIds);

        // Total Employees across assigned companies (uses workspace_id column)
        $totalEmployees = Employee::whereIn('workspace_id', $workspaceIds)->count();
        
        // ESS Enabled Users
        $totalEssUsers = Employee::whereIn('workspace_id', $workspaceIds)->where('ess_enabled', 1)->count();
        
        // ESS Adoption Rate
        $essAdoptionRate = $totalEmployees > 0 ? ($totalEssUsers / $totalEmployees) * 100 : 0;

        // Total Payroll Cost (all time - finalized or processed payslips for assigned companies)
        $totalPayrollCost = PaySlip::whereIn('workspace', $workspaceIds)
            ->whereIn('status', [1, 2])
            ->sum('net_payble');
            
        // Processed Payslips
        $processedPayslips = PaySlip::whereIn('workspace', $workspaceIds)->where('status', 2)->count();

        // Current month payslips for statutory deductions (assigned companies only)
        $currentMonthPayslips = PaySlip::whereIn('workspace', $workspaceIds)
            ->where('salary_month', 'LIKE', $currentMonth . '%')
            ->whereIn('status', [1, 2])
            ->get();

        // Calculate PAYE, UIF, SDL from saturation_deduction (current month only)
        $totalPaye = 0;
        $totalUif = 0;
        $totalSdl = 0;

        foreach ($currentMonthPayslips as $payslip) {
            $deductions = json_decode($payslip->saturation_deduction, true);
            if (is_array($deductions)) {
                foreach ($deductions as $deduction) {
                    if (isset($deduction['key']) && isset($deduction['amount'])) {
                        $key = strtolower($deduction['key']);
                        $amount = floatval($deduction['amount']);
                        
                        if (strpos($key, 'paye') !== false || strpos($key, 'tax') !== false) {
                            $totalPaye += $amount;
                        } elseif (strpos($key, 'uif') !== false) {
                            $totalUif += $amount;
                        } elseif (strpos($key, 'sdl') !== false) {
                            $totalSdl += $amount;
                        }
                    }
                }
            }
        }

        // Active Payroll Cycles (payslips with status = 1 for assigned companies)
        $activePayrollCycles = PaySlip::whereIn('workspace', $workspaceIds)
            ->where('status', 1)
            ->count();

        // Pending Leave Requests
        $pendingLeaveRequests = Leave::whereIn('workspace', $workspaceIds)->where('status', 0)->count();

        return [
            'total_customers' => $totalCustomers,
            'total_workspaces' => $totalWorkspaces,
            'total_employees' => $totalEmployees,
            'total_ess_users' => $totalEssUsers,
            'ess_adoption_rate' => $essAdoptionRate,
            'total_payroll_cost' => $totalPayrollCost,
            'processed_payslips' => $processedPayslips,
            'pending_leave_requests' => $pendingLeaveRequests,
            'total_paye' => $totalPaye,
            'total_uif' => $totalUif,
            'total_sdl' => $totalSdl,
            'active_payroll_cycles' => $activePayrollCycles,
            'current_month' => now()->format('F Y'),
        ];
    }

    /**
     * List assigned companies
     */
    public function companies(Request $request)
    {
        $user = Auth::user();
        
        // Get assigned company IDs
        $assignedCompanyIds = MasterAdminCompany::where('master_admin_id', $user->id)
            ->pluck('company_id')
            ->toArray();
        
        $users = User::whereIn('id', $assignedCompanyIds)
            ->where('type', 'company');

        if($request->filled('name'))
        {
            $users = $users->where('name', 'like', '%' . $request->name . '%');
        }

        if($request->filled('status'))
        {
            if($request->status === 'active')
            {
                $users = $users->where('is_disable', 1);
            }
            elseif($request->status === 'suspended')
            {
                $users = $users->where('is_disable', 0);
            }
        }

        if($request->filled('plan_type'))
        {
            if($request->plan_type === 'trial')
            {
                $users = $users->onTrial();
            }
            elseif($request->plan_type === 'paid')
            {
                $users = $users->paid();
            }
        }

        $users = $users->orderBy('name')->paginate(12)->appends($request->query());

        $roles = [];
        return view('users.index', compact('users', 'roles'));
    }

    /**
     * List assigned companies (List View)
     */
    public function companiesList(Request $request)
    {
        $user = Auth::user();
        
        // Get assigned company IDs
        $assignedCompanyIds = MasterAdminCompany::where('master_admin_id', $user->id)
            ->pluck('company_id')
            ->toArray();
        
        $users = User::whereIn('id', $assignedCompanyIds)
            ->where('type', 'company');

        if($request->filled('name'))
        {
            $users = $users->where('name', 'like', '%' . $request->name . '%');
        }

        if($request->filled('status'))
        {
            if($request->status === 'active')
            {
                $users = $users->where('is_disable', 1);
            }
            elseif($request->status === 'suspended')
            {
                $users = $users->where('is_disable', 0);
            }
        }

        if($request->filled('plan_type'))
        {
            if($request->plan_type === 'trial')
            {
                $users = $users->onTrial();
            }
            elseif($request->plan_type === 'paid')
            {
                $users = $users->paid();
            }
        }

        $users = $users->orderBy('name')->paginate(20)->appends($request->query());

        $roles = [];
        return view('users.list', compact('users', 'roles'));
    }

    /**
     * Show form to create a new company
     */
    public function createCompany()
    {
        return view('users.create-page');
    }

    /**
     * Store a new company (created by Master Admin)
     */
    public function storeCompany(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'workSpace_name' => 'required|string|max:255',
            'plan_type' => 'required|in:trial,paid',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($request->ajax()) {
                return response()->json(['error' => $errors->first()]);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $masterAdmin = Auth::user();
        
        // Get company role
        $role = \App\Models\Role::where('name', 'company')->first();
        if (!$role) {
            if ($request->ajax()) {
                return response()->json(['error' => __('Company role not found.')]);
            }
            return redirect()->back()->with('error', __('Company role not found.'));
        }

        // Handle password
        $password = null;
        $isEnableLogin = 0;
        if ($request->password_switch == 'on' && !empty($request->password)) {
            $password = \Hash::make($request->password);
            $isEnableLogin = 1;
        }

        // Create the company user
        $company = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $password,
            'lang' => 'en',
            'is_enable_login' => $isEnableLogin,
            'created_by' => 1, // Super admin is creator
        ]);
        $company->forceFill([
            'type' => 'company',
            'email_verified_at' => now(),
        ])->save();

        // Create workspace
        $workspace = new WorkSpace();
        $workspace->name = $request->workSpace_name;
        $workspace->created_by = $company->id;
        $workspace->save();

        // Update company with workspace
        $company->active_workspace = $workspace->id;
        $company->workspace_id = $workspace->id;
        $company->save();

        // Company settings
        User::CompanySetting($company->id);

        // Create roles for company
        $company->MakeRole();

        // Assign company role
        $company->addRole($role);

        // Handle trial/paid plan
        if ($request->plan_type == 'paid') {
            // Mark as paid - clear trial settings
            $company->trial_ends_at = null;
            $company->trial_payslips_limit = 0;
            $company->trial_payslips_used = 0;
            $company->save();
        }
        // If trial, the default settings from migration will handle it

        // Assign this company to the Master Admin
        MasterAdminCompany::create([
            'master_admin_id' => $masterAdmin->id,
            'company_id' => $company->id,
        ]);

        // Send email notification if login is enabled
        if ($isEnableLogin == 1 && !empty($request->password)) {
            try {
                // Check if email notification is enabled in admin settings
                $createUserSetting = adminSetting('Create User');
                $emailNotificationEnabled = $createUserSetting == 'on' || $createUserSetting == '1' || $createUserSetting === true;
                
                if ($emailNotificationEnabled) {
                    $uArr = [
                        'email' => $company->email,
                        'password' => $request->password,
                    ];
                    // Pass Super Admin user_id (1) to use Super Admin's email configuration
                    $resp = \App\Models\EmailTemplate::sendEmailTemplate('New User', [$company->email], $uArr, 1, null);
                    \Log::info('Master Admin storeCompany email sent', [
                        'company_id' => $company->id,
                        'email' => $company->email,
                        'resp' => $resp
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Master Admin storeCompany email error: ' . $e->getMessage());
            }
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => __('Company created successfully.')]);
        }

        return redirect()->route('master-admin.companies')
            ->with('success', __('Company created successfully and assigned to your account.'));
    }

    /**
     * Login as a company administrator
     */
    public function loginAsCompany($companyId)
    {
        $user = Auth::user();
        
        // Verify this company is assigned to the master admin
        $isAssigned = MasterAdminCompany::where('master_admin_id', $user->id)
            ->where('company_id', $companyId)
            ->exists();
        
        if (!$isAssigned) {
            return redirect()->back()->with('error', __('You are not authorized to access this company.'));
        }
        
        $company = User::where('id', $companyId)
            ->where('type', 'company')
            ->first();
        
        if (!$company) {
            return redirect()->back()->with('error', __('Company not found.'));
        }
        
        // Store the master admin ID in session so we can return
        session(['impersonating_from' => $user->id]);
        session(['impersonating_from_type' => 'master_admin']);
        
        // Login as the company
        Auth::login($company);
        
        return redirect()->route('dashboard')->with('success', __('Now logged in as :company', ['company' => $company->name]));
    }

    /**
     * Return to Master Admin account
     */
    public function returnToMasterAdmin()
    {
        $masterAdminId = session('impersonating_from');
        $impersonatingType = session('impersonating_from_type');
        
        if (!$masterAdminId || $impersonatingType !== 'master_admin') {
            return redirect()->route('dashboard');
        }
        
        $masterAdmin = User::find($masterAdminId);
        
        if (!$masterAdmin || $masterAdmin->type !== 'master_admin') {
            session()->forget(['impersonating_from', 'impersonating_from_type']);
            return redirect()->route('dashboard');
        }
        
        // Clear session and login back as master admin
        session()->forget(['impersonating_from', 'impersonating_from_type']);
        Auth::login($masterAdmin);
        
        return redirect()->route('dashboard')->with('success', __('Returned to Master Administrator account.'));
    }

    /**
     * List invoices for assigned companies
     */
    public function invoices(Request $request)
    {
        $user = Auth::user();
        
        // Get assigned company IDs
        $assignedCompanyIds = MasterAdminCompany::where('master_admin_id', $user->id)
            ->pluck('company_id')
            ->toArray();
        
        // Check if Billing module has invoices
        $invoices = collect();
        $stats = [
            'total' => 0,
            'pending' => 0,
            'overdue' => 0,
            'revenue_this_month' => 0,
        ];
        
        // Try to get invoices from the billing system if it exists
        if (class_exists('\App\Models\Billing\Invoice')) {
            $query = \App\Models\Billing\Invoice::whereIn('user_id', $assignedCompanyIds);
            
            // Calculate stats before applying filters
            $statsQuery = \App\Models\Billing\Invoice::whereIn('user_id', $assignedCompanyIds);
            $stats['total'] = $statsQuery->count();
            $stats['pending'] = (clone $statsQuery)->where('status', 'pending')->count();
            $stats['overdue'] = (clone $statsQuery)->where('status', 'pending')
                ->where('due_date', '<', now())
                ->count();
            $stats['revenue_this_month'] = (clone $statsQuery)
                ->where('status', 'paid')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total_amount');
            
            // Filter by company
            if ($request->has('company_id') && !empty($request->company_id)) {
                $query->where('user_id', $request->company_id);
            }
            
            // Filter by status
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }
            
            // Filter by date range
            if ($request->has('from_date') && !empty($request->from_date)) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
            if ($request->has('to_date') && !empty($request->to_date)) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }
            
            // Filter overdue only
            if ($request->has('overdue') && $request->overdue) {
                $query->where('status', 'pending')
                    ->where('due_date', '<', now());
            }
            
            $invoices = $query->with('user')->orderBy('created_at', 'desc')->paginate(15);
        }
        
        // Get companies for filter dropdown
        $companies = User::whereIn('id', $assignedCompanyIds)
            ->where('type', 'company')
            ->orderBy('name')
            ->get();
        
        return view('master-admin-pages.invoices', compact('invoices', 'companies', 'stats'));
    }

    /**
     * Reports page for Master Admin
     */
    public function reports(Request $request)
    {
        $user = Auth::user();
        
        // Get assigned company IDs
        $assignedCompanyIds = MasterAdminCompany::where('master_admin_id', $user->id)
            ->pluck('company_id')
            ->toArray();
        
        // Get companies for filter
        $companies = User::whereIn('id', $assignedCompanyIds)
            ->where('type', 'company')
            ->orderBy('name')
            ->get();
        
        // Get all workspaces from assigned companies
        $workspaceIds = WorkSpace::whereIn('created_by', $assignedCompanyIds)->pluck('id')->toArray();
        
        // Calculate metrics
        $totalEmployees = Employee::whereIn('workspace_id', $workspaceIds)->count();
        $totalEssUsers = Employee::whereIn('workspace_id', $workspaceIds)->where('ess_enabled', 1)->count();
        $totalPayslips = PaySlip::whereIn('workspace', $workspaceIds)->count();
        $processedPayslips = PaySlip::whereIn('workspace', $workspaceIds)->where('status', 2)->count();
        $totalPayrollCost = PaySlip::whereIn('workspace', $workspaceIds)->where('status', 2)->sum('net_payble');
        $pendingLeaveRequests = Leave::whereIn('workspace', $workspaceIds)->where('status', 0)->count();
        
        $metrics = [
            'total_customers' => count($assignedCompanyIds),
            'total_workspaces' => count($workspaceIds),
            'total_employees' => $totalEmployees,
            'total_processed_payslips' => $processedPayslips,
            'total_payroll_cost' => $totalPayrollCost,
            'total_ess_users' => $totalEssUsers,
            'pending_leave_requests' => $pendingLeaveRequests,
            'ess_adoption_rate' => $totalEmployees > 0 ? ($totalEssUsers / $totalEmployees) * 100 : 0,
        ];
        
        // Employee count data (filterable by company)
        $employeeCountData = $this->getEmployeeCountData($assignedCompanyIds, $request->employee_company);
        
        // Payslip count data (filterable by company and status)
        $payslipCountData = $this->getPayslipCountData($assignedCompanyIds, $request->payslip_company, $request->payslip_status);
        
        // Payroll cost data (by month or financial year, filterable by customer)
        $payrollCostData = $this->getPayrollCostData($assignedCompanyIds, $request->cost_company, $request->cost_period ?? 'monthly');
        
        // Top customers
        $topCustomersByEmployees = $this->getTopCustomersByEmployees($assignedCompanyIds, 5);
        $topCustomersByPayroll = $this->getTopCustomersByPayroll($assignedCompanyIds, 5);
        
        return view('shared.reports', compact(
            'companies', 
            'metrics', 
            'employeeCountData', 
            'payslipCountData', 
            'payrollCostData',
            'topCustomersByEmployees',
            'topCustomersByPayroll'
        ))->with('filterRoute', route('master-admin.reports'));
    }

    /**
     * Get employee count data by company
     */
    private function getEmployeeCountData($companyIds, $filterCompany = null)
    {
        if ($filterCompany) {
            $companyIds = [$filterCompany];
        }
        
        $companies = User::whereIn('id', $companyIds)
            ->where('type', 'company')
            ->orderBy('name')
            ->get();
        
        $data = [];
        foreach ($companies as $company) {
            $workspaceIds = WorkSpace::where('created_by', $company->id)->pluck('id')->toArray();
            $employees = Employee::whereIn('workspace_id', $workspaceIds)->count();
            $essUsers = Employee::whereIn('workspace_id', $workspaceIds)->where('ess_enabled', 1)->count();
            
            $data[] = [
                'company' => $company->name,
                'employees' => $employees,
                'ess_users' => $essUsers,
                'adoption_rate' => $employees > 0 ? ($essUsers / $employees) * 100 : 0,
            ];
        }
        
        return $data;
    }

    /**
     * Get payslip count data by company and status
     */
    private function getPayslipCountData($companyIds, $filterCompany = null, $filterStatus = null)
    {
        if ($filterCompany) {
            $companyIds = [$filterCompany];
        }
        
        $companies = User::whereIn('id', $companyIds)
            ->where('type', 'company')
            ->orderBy('name')
            ->get();
        
        $data = [];
        foreach ($companies as $company) {
            $workspaceIds = WorkSpace::where('created_by', $company->id)->pluck('id')->toArray();
            
            $query = PaySlip::whereIn('workspace', $workspaceIds);
            
            $draft = (clone $query)->where('status', 0)->count();
            $finalized = (clone $query)->where('status', 1)->count();
            $processed = (clone $query)->where('status', 2)->count();
            
            // Apply status filter for display
            if ($filterStatus !== null && $filterStatus !== '') {
                if ($filterStatus == 0 && $draft == 0) continue;
                if ($filterStatus == 1 && $finalized == 0) continue;
                if ($filterStatus == 2 && $processed == 0) continue;
            }
            
            $data[] = [
                'company' => $company->name,
                'draft' => $draft,
                'finalized' => $finalized,
                'processed' => $processed,
                'total' => $draft + $finalized + $processed,
            ];
        }
        
        return $data;
    }

    /**
     * Get payroll cost data by period
     */
    private function getPayrollCostData($companyIds, $filterCompany = null, $period = 'monthly')
    {
        if ($filterCompany) {
            $workspaceIds = WorkSpace::where('created_by', $filterCompany)->pluck('id')->toArray();
        } else {
            $workspaceIds = WorkSpace::whereIn('created_by', $companyIds)->pluck('id')->toArray();
        }
        
        $data = [];
        
        if ($period == 'monthly') {
            // Last 12 months
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $month = $date->format('Y-m');
                $monthLabel = $date->format('M Y');
                
                $amount = PaySlip::whereIn('workspace', $workspaceIds)
                    ->where('status', 2)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('net_payble');
                
                $data[] = [
                    'period' => $monthLabel,
                    'amount' => $amount,
                ];
            }
        } else {
            // Financial years (March to February for South Africa)
            $currentYear = now()->year;
            $currentMonth = now()->month;
            
            // Determine current financial year
            if ($currentMonth >= 3) {
                $startYear = $currentYear;
            } else {
                $startYear = $currentYear - 1;
            }
            
            // Show last 3 financial years
            for ($i = 2; $i >= 0; $i--) {
                $fyStartYear = $startYear - $i;
                $fyEndYear = $fyStartYear + 1;
                $fyLabel = "FY {$fyStartYear}/{$fyEndYear}";
                
                $amount = PaySlip::whereIn('workspace', $workspaceIds)
                    ->where('status', 2)
                    ->where(function($query) use ($fyStartYear, $fyEndYear) {
                        $query->where(function($q) use ($fyStartYear) {
                            $q->whereYear('created_at', $fyStartYear)
                              ->whereMonth('created_at', '>=', 3);
                        })->orWhere(function($q) use ($fyEndYear) {
                            $q->whereYear('created_at', $fyEndYear)
                              ->whereMonth('created_at', '<', 3);
                        });
                    })
                    ->sum('net_payble');
                
                $data[] = [
                    'period' => $fyLabel,
                    'amount' => $amount,
                ];
            }
        }
        
        return $data;
    }

    /**
     * Get top customers by employee count
     */
    private function getTopCustomersByEmployees($companyIds, $limit = 5)
    {
        $companies = User::whereIn('id', $companyIds)
            ->where('type', 'company')
            ->get();
        
        $data = [];
        $maxEmployees = 0;
        
        foreach ($companies as $company) {
            $workspaceIds = WorkSpace::where('created_by', $company->id)->pluck('id')->toArray();
            $employees = Employee::whereIn('workspace_id', $workspaceIds)->count();
            
            if ($employees > $maxEmployees) {
                $maxEmployees = $employees;
            }
            
            $data[] = [
                'company' => $company->name,
                'employees' => $employees,
            ];
        }
        
        // Sort by employees and take top N
        usort($data, function($a, $b) {
            return $b['employees'] - $a['employees'];
        });
        
        $data = array_slice($data, 0, $limit);
        
        // Calculate percentages
        foreach ($data as &$item) {
            $item['percentage'] = $maxEmployees > 0 ? ($item['employees'] / $maxEmployees) * 100 : 0;
        }
        
        return $data;
    }

    /**
     * Get top customers by payroll cost
     */
    private function getTopCustomersByPayroll($companyIds, $limit = 5)
    {
        $companies = User::whereIn('id', $companyIds)
            ->where('type', 'company')
            ->get();
        
        $data = [];
        $maxPayroll = 0;
        
        foreach ($companies as $company) {
            $workspaceIds = WorkSpace::where('created_by', $company->id)->pluck('id')->toArray();
            $payrollCost = PaySlip::whereIn('workspace', $workspaceIds)->where('status', 2)->sum('net_payble');
            
            if ($payrollCost > $maxPayroll) {
                $maxPayroll = $payrollCost;
            }
            
            $data[] = [
                'company' => $company->name,
                'payroll_cost' => $payrollCost,
            ];
        }
        
        // Sort by payroll cost and take top N
        usort($data, function($a, $b) {
            return $b['payroll_cost'] - $a['payroll_cost'];
        });
        
        $data = array_slice($data, 0, $limit);
        
        // Calculate percentages
        foreach ($data as &$item) {
            $item['percentage'] = $maxPayroll > 0 ? ($item['payroll_cost'] / $maxPayroll) * 100 : 0;
        }
        
        return $data;
    }

    /**
     * Payroll Cycles Page for Master Admin
     */
    public function payrollCycles(Request $request)
    {
        $user = Auth::user();
        
        // Get assigned company IDs
        $assignedCompanyIds = MasterAdminCompany::where('master_admin_id', $user->id)
            ->pluck('company_id')
            ->toArray();

        // Get companies for filter (only assigned ones)
        $companies = User::whereIn('id', $assignedCompanyIds)->get(['id', 'name']);

        // Base query - only show payslips from assigned companies
        $query = PaySlip::with(['employee'])
            ->whereIn('workspace', $assignedCompanyIds);

        // Apply filters
        if ($request->filled('customer')) {
            // Find all workspaces created by this customer
            $workspaceIds = WorkSpace::where('created_by', $request->customer)->pluck('id');
            $query->whereIn('workspace', $workspaceIds);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Get paginated results
        $payslips = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get company names for each payslip
        foreach ($payslips as $payslip) {
            // Use PaySlip's workspace field to lookup workspace, then get created_by user
            $workspace = WorkSpace::find($payslip->workspace);
            if ($workspace) {
                $company = User::find($workspace->created_by);
                $payslip->company_name = $company ? $company->name : 'Unknown';
            } else {
                $payslip->company_name = 'Unknown';
            }
            
            if ($payslip->employee) {
                $payslip->employee_name = $payslip->employee->first_name . ' ' . $payslip->employee->last_name;
            } else {
                $payslip->employee_name = 'Unknown';
            }
        }

        return view('payroll-cycles.index', compact('payslips', 'companies'))
            ->with('filterRoute', route('master-admin.payroll-cycles'));
    }
}