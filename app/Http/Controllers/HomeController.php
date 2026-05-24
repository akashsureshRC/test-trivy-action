<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\Hrm\Employee;
use App\Models\Hrm\PaySlip;
use App\Models\Hrm\Leave;
use App\Models\Hrm\LeaveManagement;
use App\Models\Billing\Invoice;
use App\Models\TaxYear;
use App\Models\WorkSpace;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // dd("test");
        if(Auth::check())
        {
            return redirect('dashboard');
        }
        else
        {
            return redirect('login');
        }
    }

    public function Dashboard()
    {
        if(Auth::check())
        {
            if(Auth::user()->type == 'super admin')
            {
                $user                       = Auth::user();
                $user['total_user']         = $user->countCompany();
                $user['total_paid_user']    = $user->countPaidCompany();
                // Subscription system removed - set to 0
                $user['total_orders']       = 0;
                $user['total_orders_price'] = 0;
                $chartData                  = $this->getOrderChart(['duration' => 'week']);
                // Subscription system removed - unlimited plans
                $user['total_plans'] = 0;

                // Subscription system removed - no popular plan tracking
                $user['popular_plan'] = null;

                // New Super Admin Dashboard Metrics
                $dashboardData = $this->getSuperAdminDashboardData();

                // Recent customers (latest 10)
                $recentCustomers = User::where('type', 'company')
                    ->orderBy('created_at', 'desc')
                    ->take(10)
                    ->get();
                
                // Flag to indicate this is NOT master admin view
                $isMasterAdmin = false;

                return view('shared.dashboard', compact('user', 'chartData', 'dashboardData', 'recentCustomers', 'isMasterAdmin'));
            }
            elseif(Auth::user()->type == 'master_admin')
            {
                // Master Administrator - redirect to their dedicated dashboard
                return redirect()->route('master-admin.dashboard');
            }
            else
            {
                $user = auth()->user();

                // Get company dashboard data
                $dashboardData = $this->getCompanyDashboardData();

                return view('dashboard', compact('dashboardData'));
            }
        }
        else
        {
            return redirect()->route('start');
        }
    }

    /**
     * Get Super Admin Dashboard Metrics
     */
    private function getSuperAdminDashboardData()
    {
        $currentMonth = now()->format('Y-m');

        // Total Customers
        $totalCustomers = User::where('type', 'company')->count();
        
        // Get all company IDs and workspace IDs
        $companyIds = User::where('type', 'company')->pluck('id')->toArray();
        $workspaceIds = WorkSpace::whereIn('created_by', $companyIds)->pluck('id')->toArray();

        // Total Workspaces
        $totalWorkspaces = count($workspaceIds);

        // Total Employees across all companies
        $totalEmployees = Employee::count();
        
        // ESS Enabled Users
        $totalEssUsers = Employee::where('ess_enabled', 1)->count();
        
        // ESS Adoption Rate
        $essAdoptionRate = $totalEmployees > 0 ? ($totalEssUsers / $totalEmployees) * 100 : 0;

        // Total Payroll Cost (all time - finalized or processed payslips)
        $totalPayrollCost = PaySlip::whereIn('status', [1, 2])->sum('net_payble');
        
        // Processed Payslips
        $processedPayslips = PaySlip::where('status', 2)->count();
        
        // Pending Leave Requests
        $pendingLeaveRequests = Leave::where('status', 0)->count();

        // Current month payslips for statutory deductions
        $currentMonthPayslips = PaySlip::where('salary_month', 'LIKE', $currentMonth . '%')
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

        // Active Payroll Cycles (payslips with status = 1 - finalized but not yet processed into payrun)
        $activePayrollCycles = PaySlip::where('status', 1)->count();

        // Upcoming tax year alert: warn if no locked tax year covers the current
        // payroll period, or if the next SARS tax year (March 1) is within 90 days
        // and no locked tax year covers it
        $upcomingTaxYearAlert = null;
        $now = now();

        // Check 1: Is the current date covered by a locked tax year?
        $currentCovered = TaxYear::locked()
            ->where('effective_from', '<=', $now)
            ->where('effective_to', '>=', $now)
            ->exists();

        if (!$currentCovered) {
            $upcomingTaxYearAlert = 'No locked tax year covers the current date (' . $now->format('d M Y') .
                '). Please configure and lock the SARS tax year to avoid payroll disruptions.';
        } else {
            // Check 2: Is the next March 1 within 90 days and not yet covered?
            $nextMarch = $now->copy()->month >= 3
                ? Carbon::create($now->year + 1, 3, 1)
                : Carbon::create($now->year, 3, 1);

            if ($nextMarch->isFuture() && $now->diffInDays($nextMarch) <= 90) {
                $covered = TaxYear::locked()
                    ->where('effective_from', '<=', $nextMarch)
                    ->where('effective_to', '>=', $nextMarch)
                    ->exists();

                if (!$covered) {
                    $upcomingTaxYearAlert = 'No locked tax year covers ' . $nextMarch->format('d M Y') .
                        '. Please configure and lock the upcoming SARS tax year before ' .
                        $nextMarch->format('1 M Y') . ' to avoid payroll disruptions.';
                }
            }
        }

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
            'upcoming_tax_year_alert' => $upcomingTaxYearAlert,
        ];
    }

    public function getOrderChart($arrParam)
    {
        $arrDuration = [];
        if($arrParam['duration'])
        {
            if($arrParam['duration'] == 'week')
            {
                $previous_week = strtotime("-2 week +1 day");
                for($i = 0; $i < 14; $i++)
                {
                    $arrDuration[date('Y-m-d', $previous_week)] = date('d-M', $previous_week);
                    $previous_week                              = strtotime(date('Y-m-d', $previous_week) . " +1 day");
                }
            }
        }
        // $arrTask          = [];
        // $arrTask['label'] = [];
        // $arrTask['data']  = [];
        // foreach($arrDuration as $date => $label)
        // {
        //     $data               = Order::select(\DB::raw('count(*) as total'))->whereDate('created_at', '=', $date)->first();
        //     $arrTask['label'][] = $label;
        //     $arrTask['data'][]  = $data->total;
        // }
        // return $arrTask;

        // Create an array of dates from your $arrDuration array
        $dates = array_keys($arrDuration);

        // Subscription system removed - return empty order data
        $orders = collect([]);
        // Initialize an empty $arrTask array
        $arrTask = ['label' => [], 'data' => []];

        foreach ($dates as $date) {
            $label = $arrDuration[$date];
            $total = 0;

            foreach ($orders as $item) {
                if ($item->date == $date) {
                    $total = $item->total;
                    break;
                }
            }

            $arrTask['label'][] = $label;
            $arrTask['data'][] = $total;
        }
        return $arrTask;
    }
    public function SoftwareDetails($slug)
    {
        $modules_all = Module::getByStatus(1);
        $modules = [];
        if(count($modules_all) > 0)
        {
            $modules = array_intersect_key(
                $modules_all,  // the array with all keys
                array_flip(array_rand($modules_all,(count($modules_all) <  6) ? count($modules_all) : 6 )) // keys to be extracted
            );
        }
        // Subscription system removed - unlimited access for all
        $plan = null;
        // AddOn system removed
        $addon = null;
        if(!empty($addon) && !empty($addon->module))
        {
            $module = Module::find($addon->module);
            if(!empty($module))
            {
                try {
                    if(moduleIsActive('LandingPage'))
                    {
                        return view('landingpage::marketplace.index',compact('modules','module','plan'));
                    }
                    else{
                        return view($module->getLowerName().'::marketplace.index',compact('modules','module','plan'));
                    }
                } catch (\Throwable $th) {

                }
            }
        }

        if (moduleIsActive('LandingPage')) {
            $layout = 'landingpage::layouts.marketplace';
        } else {
            $layout = 'marketplace.marketplace';
        }

        return view('marketplace.detail_not_found',compact('modules','layout'));

    }

    public function Software(Request $request)
    {
        // Get the query parameter from the request
        $query = $request->query('query');
        // Get all modules (assuming Module::getByStatus(1) returns all modules)
        $modules = Module::getByStatus(1);

        // Filter modules based on the query parameter
        if ($query) {
            $modules = array_filter($modules, function ($module) use ($query) {
                // You may need to adjust this condition based on your requirements
                return stripos($module->getName(), $query) !== false;
            });
        }
        // Rest of your code
        if (moduleIsActive('LandingPage')) {
            $layout = 'landingpage::layouts.marketplace';
        } else {
            $layout = 'marketplace.marketplace';
        }

        return view('marketplace.software', compact('modules', 'layout'));
    }

    public function Pricing()
    {
        $admin_settings = getAdminAllSetting();
        if(moduleIsActive('GoogleCaptcha') && (isset($admin_settings['google_recaptcha_is_on']) ? $admin_settings['google_recaptcha_is_on'] : 'off') == 'on' )
        {
            config(['captcha.secret' => isset($admin_settings['google_recaptcha_secret']) ? $admin_settings['google_recaptcha_secret'] : '']);
            config(['captcha.sitekey' => isset($admin_settings['google_recaptcha_key']) ? $admin_settings['google_recaptcha_key'] : '']);
        }
        if(Auth::check())
        {
            if(Auth::user()->type == 'company')
            {
                return redirect('plans');
            }
            else
            {
                return redirect('dashboard');
            }
        }
        else
        {
            // Subscription system removed - unlimited access for all
            $plan = null;
            $modules = Module::getByStatus(1);

            if (moduleIsActive('LandingPage')) {
                $layout = 'landingpage::layouts.marketplace';
                return view('landingpage::layouts.pricing',compact('modules','plan','layout'));

            } else {
                $layout = 'marketplace.marketplace';
            }

            return view('marketplace.pricing',compact('modules','plan','layout'));
        }
    }

    public function CustomPage(Request $request)
    {
        $modules = Module::getByStatus(1);

        if (moduleIsActive('LandingPage')) {
            $layout = 'landingpage::layouts.marketplace';
        } else {
            $layout = 'marketplace.marketplace';
        }
        if($request['page'] == 'terms_and_conditions' || $request['page'] == 'privacy_policy')
        {
            return view('custompage.'.$request['page'],compact('modules','layout'));
        }
        else
        {
            return view('marketplace.detail_not_found',compact('modules','layout'));
        }

    }

    /**
     * Get Company Dashboard Metrics for company and payroll_officer users
     */
    private function getCompanyDashboardData()
    {
        $user = Auth::user();
        $workspaceId = getActiveWorkspace();

        // Total Employees
        $totalEmployees = Employee::where('workspace_id', $workspaceId)
            ->whereNull('deleted_at')
            ->count();

        // Total ESS Users (employees with ESS enabled)
        $totalEssUsers = Employee::where('workspace_id', $workspaceId)
            ->whereNull('deleted_at')
            ->where('ess_enabled', true)
            ->whereNotNull('password')
            ->count();

        // Total Payslips (all time)
        $totalPayslips = PaySlip::where('workspace', $workspaceId)->count();

        // Total Payroll Cost (all finalized/processed payslips)
        $totalPayrollCost = PaySlip::where('workspace', $workspaceId)
            ->whereIn('status', [1, 2])
            ->sum('net_payble');

        // Pending Leave Requests
        $pendingLeaveRequests = Leave::where('workspace', $workspaceId)
            ->where('status', 'Pending')
            ->count();

        // Current Billing Amount (latest unpaid invoice)
        $currentBillingAmount = 0;
        $latestInvoice = Invoice::where('user_id', $user->created_by ?? $user->id)
            ->whereIn('status', ['pending', 'sent', 'overdue'])
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($latestInvoice) {
            $currentBillingAmount = $latestInvoice->total_amount;
        }

        // Recent 5 Employees (newest registrations)
        $recentEmployees = Employee::where('workspace_id', $workspaceId)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Recent 5 Payslips
        $recentPayslips = PaySlip::where('workspace', $workspaceId)
            ->with('employee')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Pending Leave Requests for approval (with details)
        $pendingLeaves = Leave::where('workspace', $workspaceId)
            ->where('status', 'Pending')
            ->with(['leaveManagement'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get employee names for pending leaves
        foreach ($pendingLeaves as $leave) {
            $employee = Employee::find($leave->employee_id);
            $leave->employee_name = $employee ? $employee->first_name . ' ' . $employee->last_name : 'Unknown';
        }

        return [
            'total_employees' => $totalEmployees,
            'total_ess_users' => $totalEssUsers,
            'total_payslips' => $totalPayslips,
            'total_payroll_cost' => $totalPayrollCost,
            'pending_leave_requests' => $pendingLeaveRequests,
            'current_billing_amount' => $currentBillingAmount,
            'recent_employees' => $recentEmployees,
            'recent_payslips' => $recentPayslips,
            'pending_leaves' => $pendingLeaves,
            'user_type' => $user->type,
        ];
    }

    /**
     * Payroll Cycles Page for Super Admin
     */
    public function payrollCycles(Request $request)
    {
        if (Auth::user()->type !== 'super admin') {
            return redirect()->route('dashboard')->with('error', __('Permission denied.'));
        }

        // Get all companies for filter
        $companies = User::where('type', 'company')->get(['id', 'name']);

        // Base query
        $query = PaySlip::with(['employee']);

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
            ->with('filterRoute', route('super-admin.payroll-cycles'));
    }

    /**
     * Reports page for Super Admin (Global Admin)
     */
    public function reports(Request $request)
    {
        // Get all company IDs
        $companyIds = User::where('type', 'company')->pluck('id')->toArray();
        
        // Get companies for filter
        $companies = User::where('type', 'company')
            ->orderBy('name')
            ->get();
        
        // Get all workspaces
        $workspaceIds = WorkSpace::whereIn('created_by', $companyIds)->pluck('id')->toArray();
        
        // Calculate metrics
        $totalEmployees = Employee::whereIn('workspace_id', $workspaceIds)->count();
        $totalEssUsers = Employee::whereIn('workspace_id', $workspaceIds)->where('ess_enabled', 1)->count();
        $totalPayslips = PaySlip::whereIn('workspace', $workspaceIds)->count();
        $processedPayslips = PaySlip::whereIn('workspace', $workspaceIds)->where('status', 2)->count();
        $totalPayrollCost = PaySlip::whereIn('workspace', $workspaceIds)->where('status', 2)->sum('net_payble');
        $pendingLeaveRequests = Leave::whereIn('workspace', $workspaceIds)->where('status', 0)->count();
        
        $metrics = [
            'total_customers' => count($companyIds),
            'total_workspaces' => count($workspaceIds),
            'total_employees' => $totalEmployees,
            'total_processed_payslips' => $processedPayslips,
            'total_payroll_cost' => $totalPayrollCost,
            'total_ess_users' => $totalEssUsers,
            'pending_leave_requests' => $pendingLeaveRequests,
            'ess_adoption_rate' => $totalEmployees > 0 ? ($totalEssUsers / $totalEmployees) * 100 : 0,
        ];
        
        // Employee count data (filterable by company)
        $employeeCountData = $this->getReportEmployeeCountData($companyIds, $request->employee_company);
        
        // Payslip count data (filterable by company and status)
        $payslipCountData = $this->getReportPayslipCountData($companyIds, $request->payslip_company, $request->payslip_status);
        
        // Payroll cost data (by month or financial year, filterable by customer)
        $payrollCostData = $this->getReportPayrollCostData($companyIds, $request->cost_company, $request->cost_period ?? 'monthly');
        
        // Top customers
        $topCustomersByEmployees = $this->getReportTopCustomersByEmployees($companyIds, 5);
        $topCustomersByPayroll = $this->getReportTopCustomersByPayroll($companyIds, 5);
        
        return view('shared.reports', compact(
            'companies', 
            'metrics', 
            'employeeCountData', 
            'payslipCountData', 
            'payrollCostData',
            'topCustomersByEmployees',
            'topCustomersByPayroll'
        ))->with('filterRoute', route('super-admin.reports'));
    }

    /**
     * Get employee count data by company for reports
     */
    private function getReportEmployeeCountData($companyIds, $filterCompany = null)
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
     * Get payslip count data by company and status for reports
     */
    private function getReportPayslipCountData($companyIds, $filterCompany = null, $filterStatus = null)
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
     * Get payroll cost data by period for reports
     */
    private function getReportPayrollCostData($companyIds, $filterCompany = null, $period = 'monthly')
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
     * Get top customers by employee count for reports
     */
    private function getReportTopCustomersByEmployees($companyIds, $limit = 5)
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
     * Get top customers by payroll cost for reports
     */
    private function getReportTopCustomersByPayroll($companyIds, $limit = 5)
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

}
