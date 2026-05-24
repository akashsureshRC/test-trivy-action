<?php

namespace App\Http\Controllers\Hrm;

use App\Models\WorkSpace;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Hrm\AccommodationBenefit;
use App\Models\Hrm\Allowance;
use App\Models\Hrm\AllowanceInternational;
use App\Models\Hrm\AnnualBonus;
use App\Models\Hrm\AnnualPayment;
use App\Models\Hrm\ArbitrationAward;
use App\Models\Hrm\BasicSalary;
use App\Models\Hrm\BasicSalaryHour;
use App\Models\Hrm\BroadBasedEmployee;
use App\Models\Hrm\BursariesScholarship;
use App\Models\Hrm\Bursary;
use App\Models\Hrm\Commission;
use App\Models\Hrm\CompanyCar;
use App\Models\Hrm\CompanyCarUnderOperating;
use App\Models\Hrm\ComputerAllowance;
use App\Models\Hrm\Covid19Disaster;
use App\Models\Hrm\DividendsSubject;
use App\Models\Hrm\Donation;
use App\Models\Hrm\EmployeeBenefit;
use App\Models\Hrm\EmployeeEntitlementPolicy;
use App\Models\Hrm\Employee;
use App\Models\Hrm\EmployerLoan;
use App\Models\Hrm\EquityInstrument;
use App\Models\Hrm\ExpenseClaim;
use App\Services\EssPushNotificationService;
use App\Services\TaxCalculationService;
use App\Models\TaxYear;
use App\Models\Hrm\ExtraPay;
use App\Models\Hrm\Garnishee;
use App\Models\Hrm\IncomePolicy;
use App\Models\Hrm\IncomeProtection;
use App\Models\Hrm\LeaveRecord;
use App\Models\Hrm\LongServiceAward;
use App\Models\Hrm\MaintenanceOrder;
use App\Models\Hrm\MedicalAid;
use App\Models\Hrm\MedicalCost;
use App\Models\Hrm\OnceOffCommission;
use App\Models\Hrm\PaySlip;
use App\Models\Hrm\Payroll;
use App\Models\Hrm\Payrun;
use App\Models\Hrm\PayslipCommission;
use App\Models\Hrm\PensionFund;
use App\Models\Hrm\PhoneAllowance;
use App\Models\Hrm\ProvidentFundPayroll;
use App\Models\Hrm\RelocationAllowance;
use App\Models\Hrm\Repayments;
use App\Models\Hrm\RestraintOfTrade;
use App\Models\Hrm\RetirementAnnuityFundPayroll;
use App\Models\Hrm\SavingsDeduction;
use App\Models\Hrm\StaffPurchase;
use App\Models\Hrm\SubsistenceAllowance;
use App\Models\Hrm\TaxDirectiveEntry;
use App\Models\Hrm\TaxOverDeduction;
use App\Models\Hrm\TerminationLump;
use App\Models\Hrm\TersPayout;
use App\Models\Hrm\ToolAllowance;
use App\Models\Hrm\TravelAllowance;
use App\Models\Hrm\UniformAllowance;
use App\Models\Hrm\UnionMembershipFee;
use App\Events\Hrm\PayslipSend;
use App\Events\Hrm\PayrunProcessed;
use App\Services\LeaveAccrualService;

class PayrunController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $payrun_terms = PaySlip::distinct('salary_month')->where('status', '<>', 2)->where('workspace', getActiveWorkspace())->where('status', 1)->pluck('salary_month');
        $pending_payruns = [];
        foreach ($payrun_terms as $term) {
            $total = PaySlip::where('salary_month', $term)->where('status', 1)->where('workspace', getActiveWorkspace())->count();
            $finalized = PaySlip::where('salary_month', $term)->where('status', 1)->where('workspace', getActiveWorkspace())->count();
            $pending = 0;
            $pending_payruns[$term] = [
                'term' => $term,
                'total' => $total,
                'finalized' => $finalized,
                'pending' => $pending,
            ];
        }
        $payslips = Payslip::where('workspace', getActiveWorkspace())->pluck('id')->toArray();
        $workspace_payruns = Payrun::whereIn('payslip_id', $payslips)->orderBy('id', 'DESC')->get();
        $stored_payrun_terms = $workspace_payruns->pluck('term')->unique()->values();
        
        // Build payruns array for all terms (needed for stats)
        $all_payruns = [];
        foreach ($stored_payrun_terms as $stored_payrun_term) {
            $term_payruns = $workspace_payruns->where('term', $stored_payrun_term);
            $all = $term_payruns->count();
            $eft = $term_payruns->where('payment_method', 'EFT')->count();
            $cheque = $term_payruns->where('payment_method', 'Cheque')->count();
            $cash = $term_payruns->where('payment_method', 'Cash')->count();
            $date1 = Carbon::parse($stored_payrun_term . '-01')->endOfMonth();
            $date2 = Carbon::now();
            $month_difference = $date1->diffInMonths($date2);
            $payslip_ids = $term_payruns->pluck('payslip_id');
            $total_netpay = Payslip::whereIn('id', $payslip_ids)->where('workspace', getActiveWorkspace())->sum('net_payble');
            $all_payruns[] = [
                'term' => $stored_payrun_term,
                'all' => $all,
                'eft' => $eft,
                'cheque' => $cheque,
                'cash' => $cash,
                'total_netpay' => $total_netpay,
                'payslips' => count($payslip_ids),
                'month_difference' => $month_difference,
            ];
        }
        
        // Paginate the payruns collection
        $perPage = 10;
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage('page');
        $pagedData = array_slice($all_payruns, ($currentPage - 1) * $perPage, $perPage);
        $payruns = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedData,
            count($all_payruns),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );
        
        // Keep all_payruns for stats calculation
        return view('hrm.payrun.index', compact('pending_payruns', 'payruns', 'all_payruns'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('hrm.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $request->validate([
            'term' => 'required|string|max:255',
            'payment_method' => 'required|in:EFT,Cheque,Cash',
        ]);

        // Check if billing allows payslip generation
        $billingCheck = $this->checkBillingAccess();
        if ($billingCheck !== true) {
            return redirect()->back()->with('error', $billingCheck);
        }

        // Ensure a locked tax year exists for this term
        $taxYearCheck = TaxYear::resolveForTerm($request->term);
        if (!$taxYearCheck) {
            return redirect()->back()->with('error', 'No Tax Year configuration found for the selected term. Please contact customer support for assistance.');
        }

        DB::beginTransaction();

        try {
            $payslips = PaySlip::where('salary_month', $request->term)->where('workspace', getActiveWorkspace())->where('status', 1)->get();
            
            // Prepare journal entries for RC Books API
            $journalEntries = [];
            
            // Track created payruns for billing events (dispatched after commit)
            $processedPayruns = [];
            $workspaceId = getActiveWorkspace();
            
            foreach ($payslips as $payslip) {
                $employeeId = $payslip->employee_id;
                $term = $request->term;

                // Calculate basic salary
                $basicSalaryData = BasicSalary::where('employee_id', $employeeId)->where('term', $term)->first();
                if ($basicSalaryData) {
                    $basicSalaryNormal = $basicSalaryData->fixed_salary ?? 0;
                    $basicSalaryOT = $basicSalaryData->ot_salary ?? 0;
                    $basicSalary = round(($basicSalaryNormal + $basicSalaryOT), 2);
                } else {
                    $basicSalary = 0;
                }

                // Regular inputs
                $regularIncomeData = $this->calculateRegularIncomeItems($employeeId, $term);
                $regularDeductionData = $this->calculateRegularDeductionItems($employeeId, $term);

                $totalRegularInputIncome = $regularIncomeData['totalRegularInputIncome'];
                $withoutTaxRegularInputIncome = $regularIncomeData['withoutTaxRegularInputIncome'];
                $totalRegularInputAllowance = $regularIncomeData['totalRegularInputAllowance'];
                $totalRegularInputDeduction = $regularDeductionData['totalRegularInputDeduction'];

                // Payslip inputs
                $incomeData = $this->calculateIncomeItems($employeeId, $term);
                $allowanceData = $this->calculateAllowanceItems($employeeId, $term);
                $deductionData = $this->calculateDeductionItems($employeeId, $term);
                $benefitsData = $this->calculateBenefitItems($employeeId, $term);

                $totalIncome = $incomeData['totalIncome'];
                $totalAllowance = $allowanceData['totalAllowance'] + $regularDeductionData['additionalIncome']
                    + $regularIncomeData['totalRegularInputAllowance'] + $benefitsData['benefitAllowance']
                    + $regularIncomeData['withoutTaxRegularInputIncome'];
                $totalDeduction = $deductionData['totalDeduction'];
                $totalBenefit = $benefitsData['totalBenefit'];

                // Calculate UIF
                $taxYear = TaxYear::resolveForTerm($term);
                $uifRate = $taxYear ? $taxYear->uif_rate : 0.01;
                $uifCeiling = $taxYear ? $taxYear->uif_ceiling : 177.12;
                $sdlRate = $taxYear ? $taxYear->sdl_rate : 0.01;

                $uif = ($basicSalary + $totalIncome - $totalDeduction) * $uifRate;
                if ($uif > $uifCeiling) {
                    $uif = $uifCeiling;
                }

                // Calculate SDL
                $company_settings = getCompanyAllSetting();
                $sdl = 0;
                if (!empty($company_settings['is_sdl_calculate']) && $company_settings['is_sdl_calculate'] == 1) {
                    $sdl = ($payslip->basic_salary + $payslip->allowance + $payslip->other_payment) * $sdlRate;
                }

                // Calculate PAYE
                $taxableIncome = $basicSalary + $totalIncome + $totalAllowance + $totalBenefit + $totalRegularInputIncome - $withoutTaxRegularInputIncome;
                $payTax = $this->calculateTax($employeeId, $taxableIncome, $term);

                $payrun = Payrun::create([
                    'date' => Carbon::today()->format('Y-m-d'),
                    'term' => $request->term,
                    'payslip_id' => $payslip->id,
                    'payment_method' => $request->payment_method,
                ]);
                // Freeze unpaid leave deduction at payrun time
                $leaveData = $this->getEmployeeLeaveData($employeeId, Carbon::parse($term . '-01')->endOfMonth()->format('Y-m-d'));
                $totalUnpaidLeaveDays = array_sum(array_column($leaveData, 'unpaid_leave'));
                $frozenUnpaidLeave = 0;
                if ($totalUnpaidLeaveDays > 0) {
                    $frozenUnpaidLeave = ($basicSalary / 30) * $totalUnpaidLeaveDays;
                }

                $payslip->status = 2;
                $payslip->tax_year_id = $taxYear?->id;
                $payslip->paye_amount = $payTax;
                $payslip->uif_amount = $uif;
                $payslip->sdl_amount = $sdl;
                $payslip->unpaid_leave_deduction = $frozenUnpaidLeave;
                $payslip->save();
                
                // Track for billing event dispatch after commit
                $processedPayruns[] = ['payrun' => $payrun, 'payslip' => $payslip];

                // Collect salary wages entry
                $journalEntries[] = [
                    'account_code' => '5410',
                    'date' => Carbon::today()->format('Y-m-d'),
                    'description' => 'Payrun - ' . $payrun->id . ' - ' . $payrun->term,
                    'debit' => $payslip->net_payble,
                    'credit' => 0,
                ];

                // PAYE entries
                if ($payTax > 0) {
                    // Debit Payroll Taxes
                    $journalEntries[] = [
                        'account_code' => '5440',
                        'date' => Carbon::today()->format('Y-m-d'),
                        'description' => 'Payrun PAYE - ' . $payrun->id . ' - ' . $payrun->term,
                        'debit' => $payTax,
                        'credit' => 0,
                    ];

                    // Credit PAYE Control
                    $journalEntries[] = [
                        'account_code' => '2390',
                        'date' => Carbon::today()->format('Y-m-d'),
                        'description' => 'Payrun PAYE - ' . $payrun->id . ' - ' . $payrun->term,
                        'debit' => 0,
                        'credit' => $payTax,
                    ];
                }

                // UIF entries
                if ($uif > 0) {
                    // Debit UIF Payments
                    $journalEntries[] = [
                        'account_code' => '5430',
                        'date' => Carbon::today()->format('Y-m-d'),
                        'description' => 'Payrun UIF - ' . $payrun->id . ' - ' . $payrun->term,
                        'debit' => $uif,
                        'credit' => 0,
                    ];

                    // Credit UIF Control
                    $journalEntries[] = [
                        'account_code' => '2400',
                        'date' => Carbon::today()->format('Y-m-d'),
                        'description' => 'Payrun UIF - ' . $payrun->id . ' - ' . $payrun->term,
                        'debit' => 0,
                        'credit' => $uif,
                    ];
                }

                // SDL entries
                if ($sdl > 0) {
                    // Debit SDL Payments
                    $journalEntries[] = [
                        'account_code' => '5435',
                        'date' => Carbon::today()->format('Y-m-d'),
                        'description' => 'Payrun SDL - ' . $payrun->id . ' - ' . $payrun->term,
                        'debit' => $sdl,
                        'credit' => 0,
                    ];

                    // Credit SDL Control
                    $journalEntries[] = [
                        'account_code' => '2410',
                        'date' => Carbon::today()->format('Y-m-d'),
                        'description' => 'Payrun SDL - ' . $payrun->id . ' - ' . $payrun->term,
                        'debit' => 0,
                        'credit' => $sdl,
                    ];
                }
            }

            // Send journal entries to RC Books API
            if (config('services.rc_books.enabled') && !empty($journalEntries)) {
                try {
                    $response = Http::withToken(config('services.rc_books.api_token'))
                        ->post(config('services.rc_books.url') . '/api/payroll/journal-entry', [
                            'workspace_id' => getActiveWorkspace(),
                            'term' => $request->term,
                            'entries' => $journalEntries,
                        ]);

                    if ($response->failed()) {
                        Log::error('RC Books API failed', [
                            'status' => $response->status(),
                            'response' => $response->json()
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('RC Books API error', ['error' => $e->getMessage()]);
                }
            }

            DB::commit();
            
            // Dispatch billing events after successful commit
            foreach ($processedPayruns as $processed) {
                event(new PayrunProcessed($processed['payrun'], $processed['payslip'], $workspaceId));
            }
            
            // Queue email notifications for new payroll (processed in background)
            $company_settings = getCompanyAllSetting();
            if (!empty($company_settings['New Payroll']) && $company_settings['New Payroll'] == true) {
                $emailCount = 0;
                $userId = \Auth::id();
                foreach ($processedPayruns as $processed) {
                    $payslip = $processed['payslip'];
                    
                    // Dispatch job with delay to prevent rate limiting (stagger by 2 seconds each)
                    \App\Jobs\SendPayslipEmailJob::dispatch($payslip->id, $request->term, $userId, $workspaceId)
                        ->delay(now()->addSeconds($emailCount * 2));
                    
                    $emailCount++;
                }
                
                Log::info('Payrun emails queued', [
                    'term' => $request->term,
                    'email_count' => $emailCount
                ]);
            }
            
            // Send push notifications for new payslips
            try {
                $pushService = new EssPushNotificationService();
                $monthName = Carbon::parse($request->term)->format('F Y');
                
                foreach ($processedPayruns as $processed) {
                    $payslip = $processed['payslip'];
                    if ($payslip->employee_id) {
                        $pushService->sendPayslipNotification($payslip->employee_id, $monthName);
                    }
                }
                
                Log::info('Payrun push notifications sent', [
                    'term' => $request->term,
                    'count' => count($processedPayruns)
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send payslip push notifications: ' . $e->getMessage());
            }
            
            return redirect()->route('payrun.index')->with('success', 'Payrun added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('hrm.show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('hrm.edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
    public function finalized_pdf($term)
    {
        // Validate workspace exists
        $workspace = WorkSpace::find(Auth::user()->active_workspace);
        if (!$workspace) {
            return redirect()->back()->with('error', __('No active workspace found. Please select a workspace.'));
        }
        
        $payslips = PaySlip::where('salary_month', $term)->where('status', 1)->where('workspace', getActiveWorkspace())->get();

        foreach ($payslips as $payslip) {
            $originalTerm = $term;

            $endOfMonthTerm = Carbon::parse($term . '-01')->endOfMonth()->format('Y-m-d');
            $employeeId = $payslip->employee_id;

            if (!$employeeId) {
                return redirect()->back()->with('error', 'Employee ID is missing.');
            }

            $employee = Employee::find($employeeId);
            if (!$employee) {
                return redirect()->back()->with('error', 'Employee not found.');
            }

            //$payroll = Payroll::where('employee_id', $employeeId)->first();
            $payroll = Payroll::where('employee_id', $employeeId)
                ->where('term', $term)
                ->first();
            $basicSalaryData = BasicSalary::where('employee_id', $employeeId)->where('term', $term)->first();
            if ($basicSalaryData && $basicSalaryData->hourly_paid == 1) {
                $basicSalaryHour = BasicSalaryHour::where('employee_id', $employeeId)->where('term', $term)->first();
                if ($basicSalaryHour) {
                    $basicSalaryNormal = ($basicSalaryData->hourly_rate * $basicSalaryHour->normal_hours);
                    $taxYear = TaxYear::resolveForTerm($term);
                    $otMultiplier = $taxYear ? $taxYear->ot_multiplier : 1.5;
                    $basicSalaryOT = ($basicSalaryData->hourly_rate * $basicSalaryHour->ot_hours * $otMultiplier);
                    $basicSalary = round(($basicSalaryNormal + $basicSalaryOT), 2);
                } else {
                    $basicSalary = 0;
                }
            } else {
                $basicSalary = $basicSalaryData ? $basicSalaryData->fixed_salary : 0;
            }


            //regular inputs
            $regularIncomeData = $this->calculateRegularIncomeItems($employeeId, $term);
            $regularDeductionData = $this->calculateRegularDeductionItems($employeeId, $term);

            $totalRegularInputIncome = $regularIncomeData['totalRegularInputIncome'];
            $withoutTaxRegularInputIncome = $regularIncomeData['withoutTaxRegularInputIncome'];
            $totalRegularInputAllowance = $regularIncomeData['totalRegularInputAllowance'];
            $totalRegularInputDeduction = $regularDeductionData['totalRegularInputDeduction'];

            // payslip inputs
            $incomeData = $this->calculateIncomeItems($employeeId, $originalTerm);
            $allowanceData = $this->calculateAllowanceItems($employeeId, $originalTerm);
            $deductionData = $this->calculateDeductionItems($employeeId, $originalTerm);
            $benefitsData = $this->calculateBenefitItems($employeeId, $originalTerm);

            $totalIncome    = $incomeData['totalIncome'];
            $totalAllowance = $allowanceData['totalAllowance'] + $regularDeductionData['additionalIncome']
                + $regularIncomeData['totalRegularInputAllowance'] + $benefitsData['benefitAllowance']
                + $regularIncomeData['withoutTaxRegularInputIncome'];
            $totalDeduction = $deductionData['totalDeduction'];
            $totalBenefit   = $benefitsData['totalBenefit'];

            // Get employee leave data
            $leaveData = $this->getEmployeeLeaveData($employeeId, $endOfMonthTerm);
            $totalUnpaidLeaveDays = array_sum(array_column($leaveData, 'unpaid_leave'));
            $unpaidLeaveDeduction = 0;
            if ($totalUnpaidLeaveDays > 0) {
                $dailySalary = $basicSalary / 30;
                $unpaidLeaveDeduction = $dailySalary * $totalUnpaidLeaveDays;
            }
            // Calculate deductions
            $company_settings = getCompanyAllSetting();
            $taxableIncome = $basicSalary + $totalIncome + $totalAllowance + $totalBenefit + $totalRegularInputIncome - $withoutTaxRegularInputIncome;

            if ($payslip->tax_year_id !== null) {
                // Use frozen tax values from finalized payslip
                $uif = (float) $payslip->uif_amount;
                $sdl = (float) $payslip->sdl_amount;
                $payTax = (float) $payslip->paye_amount;
                // Use frozen unpaid leave deduction if available
                if ($payslip->unpaid_leave_deduction !== null) {
                    $unpaidLeaveDeduction = (float) $payslip->unpaid_leave_deduction;
                }
            } else {
                $taxYear = TaxYear::resolveForTerm($term);
                $uifRate = $taxYear ? $taxYear->uif_rate : 0.01;
                $uifCeiling = $taxYear ? $taxYear->uif_ceiling : 177.12;
                $sdlRate = $taxYear ? $taxYear->sdl_rate : 0.01;

                $uif = ($basicSalary + $totalIncome - $totalDeduction) * $uifRate;
                if ($uif > $uifCeiling) {
                    $uif = $uifCeiling;
                }
                $sdl = 0;
                if (!empty($company_settings['is_sdl_calculate']) && $company_settings['is_sdl_calculate'] == 1) {
                    $sdl = ($payslip->basic_salary + $payslip->allowance + $payslip->other_payment) * $sdlRate;
                }
                $payTax = $this->calculateTax($employeeId, $taxableIncome, $originalTerm);
            }

            $totalDeduction += $uif + $payTax + $sdl + $totalRegularInputDeduction + $incomeData['additionalDeductions'] + $allowanceData['allowanceDeductions'] + $benefitsData['benefitDeduction'] - $unpaidLeaveDeduction;
            $netPay = $basicSalary + $totalIncome + $totalAllowance + $totalBenefit + $totalRegularInputIncome - $totalDeduction;

            $incomeItems = [
                ['name' => 'Basic Salary', 'amount' => $basicSalary]
            ];

            if (isset($regularIncomeData['items']['commission'])) {
                $incomeItems[] = [
                    'name' => 'Commission',
                    'amount' => $regularIncomeData['items']['commission']->commission_amount
                ];
            }
            if (isset($regularIncomeData['items']['income_policy'])) {
                $incomeItems[] = [
                    'name' => 'Loss of Income Policy Payout',
                    'amount' => $regularIncomeData['items']['income_policy']->payout_amount
                ];
            }
            if (isset($regularIncomeData['items']['accommodation_benefit'])) {
                $incomeItems[] = [
                    'name' => 'Accommodation Benefits',
                    'amount' => $regularIncomeData['items']['accommodation_benefit']->amount
                ];
            }
            // if (isset($regularIncomeData['items']['bursaries_scholarships'])) {
            //     $incomeItems[] = [
            //         'name' => 'Bursaries Scholarships - Taxable Portion',
            //         'amount' => $regularIncomeData['items']['bursaries_scholarships']->taxable_portion
            //     ];
            //     $incomeItems[] = [
            //         'name' => 'Bursaries Scholarships - Exempt Portion',
            //         'amount' => $regularIncomeData['items']['bursaries_scholarships']->exempt_portion
            //     ];
            // }
            // if (isset($regularIncomeData['items']['companyCar'])) {
            //     $incomeItems[] = [
            //         'name' => 'Company Car',
            //         'amount' => $regularIncomeData['items']['companyCar']->taxable_value
            //     ];
            // }
            if (isset($regularIncomeData['items']['companyCarUnderOperating'])) {
                $incomeItems[] = [
                    'name' => 'Company Car Under Operating Lease',
                    'amount' => $regularIncomeData['items']['companyCarUnderOperating']->taxable_value
                ];
            }
            if (isset($regularIncomeData['items']['savings_deduction'])) {
                $incomeItems[] = [
                    'name' => 'Savings',
                    'amount' => $regularIncomeData['items']['savings_deduction']->regular_deduction
                ];
            }
            // Add other income items
            if (isset($incomeData['items']['annual_bonus'])) {
                $incomeItems[] = [
                    'name' => 'Annual Bonus',
                    'amount' => $incomeData['items']['annual_bonus']->bonus_amount
                ];
            }

            if (isset($incomeData['items']['annual_payment'])) {
                $incomeItems[] = [
                    'name' => 'Annual Payment',
                    'amount' => $incomeData['items']['annual_payment']->annual_amount
                ];
            }

            if (isset($incomeData['items']['extra_pay'])) {
                $incomeItems[] = [
                    'name' => 'Extra Pay',
                    'amount' => $incomeData['items']['extra_pay']->amount
                ];
            }

            if (isset($incomeData['items']['once_off_commission'])) {
                $incomeItems[] = [
                    'name' => 'Once-off Commission',
                    'amount' => $incomeData['items']['once_off_commission']->commission_amount
                ];
            }

            if (isset($incomeData['items']['restraints_of_trade'])) {
                $incomeItems[] = [
                    'name' => 'Restraint of Trade',
                    'amount' => $incomeData['items']['restraints_of_trade']->amount
                ];
            }
            if (isset($incomeData['items']['arbitration_award'])) {
                $incomeItems[] = [
                    'name' => 'Arbitration Award',
                    'amount' => $incomeData['items']['arbitration_award']->directive_income_amount
                ];
            }
            if (isset($incomeData['items']['dividends_subject'])) {
                $incomeItems[] = [
                    'name' => 'Dividends Subject',
                    'amount' => $incomeData['items']['dividends_subject']->directive_income_amount
                ];
            }

            // Prepare allowance items array
            $allowanceItems = [];
            if (isset($regularIncomeData['items']['travel_allowance'])) {
                $allowanceItems[] = [
                    'name' => 'Travel Allowance',
                    'amount' => $regularIncomeData['items']['travel_allowance']->fixed_amount
                ];
            }
            if (isset($regularIncomeData['items']['employer_loan'])) {
                $allowanceItems[] = [
                    'name' => 'Employer Loan',
                    'amount' => $regularIncomeData['items']['employer_loan']->regular_repayment
                ];
            }
            if (isset($allowanceData['items']['broad_based_employee'])) {
                $allowanceItems[] = [
                    'name' => 'Broad Based Employee Share Plan',
                    'amount' => $allowanceData['items']['broad_based_employee']->amount
                ];
            }

            if (isset($allowanceData['items']['computer_allowance'])) {
                $allowanceItems[] = [
                    'name' => 'Computer Allowance',
                    'amount' => $allowanceData['items']['computer_allowance']->computer_allowance
                ];
            }
            if (isset($allowanceData['items']['expense_claim'])) {
                $allowanceItems[] = [
                    'name' => 'Expense Claim',
                    'amount' => $allowanceData['items']['expense_claim']->amount
                ];
            }

            if (isset($allowanceData['items']['phone_allowance'])) {
                $allowanceItems[] = [
                    'name' => 'Phone Allowance',
                    'amount' => $allowanceData['items']['phone_allowance']->phone_allowance_amount
                ];
            }
            if (isset($allowanceData['items']['relocation_allowance'])) {
                $allowanceItems[] = [
                    'name' => 'Relocation Allowance - Taxable',
                    'amount' => $allowanceData['items']['relocation_allowance']->taxable_allowance
                ];
                $allowanceItems[] = [
                    'name' => 'Relocation Allowance - Non-Taxable',
                    'amount' => $allowanceData['items']['relocation_allowance']->non_taxable_allowance
                ];
            }
            if (isset($allowanceData['items']['allowance_international'])) {
                $allowanceItems[] = [
                    'name' => 'Subsistence Allowance International',
                    'amount' => $allowanceData['items']['allowance_international']->paid_to_employee
                ];
            }
            if (isset($allowanceData['items']['subsistence_allowance'])) {
                $allowanceItems[] = [
                    'name' => 'Subsistence Allowance Local',
                    'amount' => $allowanceData['items']['subsistence_allowance']->full_amount_paid
                ];
            }
            if (isset($allowanceData['items']['tool_allowance'])) {
                $allowanceItems[] = [
                    'name' => 'Tool Allowance',
                    'amount' => $allowanceData['items']['tool_allowance']->amount
                ];
            }
            if (isset($allowanceData['items']['uniform_allowance'])) {
                $allowanceItems[] = [
                    'name' => 'Uniform Allowance',
                    'amount' => $allowanceData['items']['uniform_allowance']->amount
                ];
            }
            if (isset($allowanceData['items']['equity_instruments'])) {
                $allowanceItems[] = [
                    'name' => 'Gain from Vesting Equity',
                    'amount' => $allowanceData['items']['equity_instruments']->directive_income_amount
                ];
            }
            if (isset($allowanceData['items']['taxDirectiveEntry'])) {
                $allowanceItems[] = [
                    'name' => 'Directive Income Amount',
                    'amount' => $allowanceData['items']['taxDirectiveEntry']->directive_income_amount
                ];
            }
            if (isset($benefitsData['items']['ters_payout'])) {
                $allowanceItems[] = [
                    'name' => 'TERS Payout',
                    'amount' => $benefitsData['items']['ters_payout']->amount
                ];
            }
            if (isset($regularDeductionData['items']['medical_aid'])) {
                $allowanceItems[] = [
                    'name' => 'Medical Aid Benefit Paid Out',
                    'amount' => $regularDeductionData['items']['medical_aid']->employer_contribution
                ];
            }
            if (isset($allowanceData['items']['bursaries_scholarships'])) {
                if ($allowanceData['items']['bursaries_scholarships']->employee_handles_payment == 1) {
                    $allowanceItems[] = [
                        'name' => 'Bursaries And Scholarships - taxable (Regular)',
                        'amount' => $allowanceData['items']['bursaries_scholarships']->taxable_portion
                    ];
                    $allowanceItems[] = [
                        'name' => 'Bursaries And Scholarships - exempt (Regular)',
                        'amount' => $allowanceData['items']['bursaries_scholarships']->exempt_portion
                    ];
                }
            }
            if (isset($allowanceData['items']['bursary'])) {
                if ($allowanceData['items']['bursary']->employee_handles_payment == 1) {
                    $allowanceItems[] = [
                        'name' => 'Bursaries And Scholarships - taxable',
                        'amount' => $allowanceData['items']['bursary']->taxable_portion
                    ];
                    $allowanceItems[] = [
                        'name' => 'Bursaries And Scholarships - exempt',
                        'amount' => $allowanceData['items']['bursary']->exempt_portion
                    ];
                }
            }
            // Prepare deduction items array
            $deductionItems = [
                ['name' => 'UIF', 'amount' => $uif],
                ['name' => 'Vat (PAYE)', 'amount' => $payTax],
                ['name' => 'SDL', 'amount' => $sdl]
            ];

            if ($unpaidLeaveDeduction > 0) {
                $deductionItems[] = [
                    'name' => 'Loss of Pay (Unpaid Leave)',
                    'amount' => $unpaidLeaveDeduction
                ];
            }
            // Add other deductions if they exist in your data
            if (isset($incomeData['items']['arbitration_award'])) {
                $deductionItems[] = [
                    'name' => 'Tax on Arbitration Award',
                    'amount' => $incomeData['items']['arbitration_award']->tax_to_deduct
                ];
            }
            if (isset($incomeData['items']['dividends_subject'])) {
                $deductionItems[] = [
                    'name' => 'Tax on Dividends Restricted Equity',
                    'amount' => $incomeData['items']['dividends_subject']->tax_to_deduct
                ];
            }
            if (isset($allowanceData['items']['equity_instruments'])) {
                $deductionItems[] = [
                    'name' => 'Tax on Gain from Vesting Equity',
                    'amount' => $allowanceData['items']['equity_instruments']->tax_deduct_amount
                ];
            }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['garnishee'])) {
                $deductionItems[] = [
                    'name' => 'Garnishee',
                    'amount' => $regularDeductionData['items']['garnishee']->installment
                ];
            }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['incomeProtection'])) {
                $deductionItems[] = [
                    'name' => 'Income Protection',
                    'amount' => $regularDeductionData['items']['incomeProtection']->amount_deducted
                ];
            }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['maintenance_order'])) {
                $deductionItems[] = [
                    'name' => 'Maintenance Order',
                    'amount' => $regularDeductionData['items']['maintenance_order']->installment
                ];
            }
            
            // if ($regularDeductionData['items'] && isset($regularDeductionData['items']['medical_aid'])) {
            //     $deductionItems[] = [
            //         'name' => 'Medical Aid',
            //         'amount' => $regularDeductionData['items']['medical_aid']->medical_aid
            //     ];
            // }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['pension_fund'])) {
                $deductionItems[] = [
                    'name' => 'Pension Fund',
                    'amount' => $regularDeductionData['items']['pension_fund']->fixed_contribution_employee
                ];
            }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['provident_fund'])) {
                $deductionItems[] = [
                    'name' => 'Provident Fund',
                    'amount' => $regularDeductionData['items']['provident_fund']->fixed_contribution_employee
                ];
            }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['retirement_annuity'])) {
                $deductionItems[] = [
                    'name' => 'Retirement Annuity',
                    'amount' => $regularDeductionData['items']['retirement_annuity']->amount
                ];
            }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['union_membership'])) {
                $deductionItems[] = [
                    'name' => 'Union Membership',
                    'amount' => $regularDeductionData['items']['union_membership']->amount_per_period
                ];
            }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['tax_over_deduction'])) {
                $deductionItems[] = [
                    'name' => 'Voluntary Tax Over Deduction',
                    'amount' => $regularDeductionData['items']['tax_over_deduction']->per_period
                ];
            }
            if ($deductionData['items'] && isset($deductionData['items']['medical_aid'])) {
                $deductionItems[] = [
                    'name' => 'Medical Aid - Employee',
                    'amount' => $deductionData['items']['medical_aid']->amount
                ];
            }
            if ($deductionData['items'] && isset($deductionData['items']['donation'])) {
                $deductionItems[] = [
                    'name' => 'Donations',
                    'amount' => $deductionData['items']['donation']->amount
                ];
            }
            if ($deductionData['items'] && isset($deductionData['items']['staff_purchase'])) {
                $deductionItems[] = [
                    'name' => 'Staff Purchases',
                    'amount' => $deductionData['items']['staff_purchase']->amount
                ];
            }
            if ($deductionData['items'] && isset($deductionData['items']['repayment'])) {
                $deductionItems[] = [
                    'name' => 'Repayment of Loan',
                    'amount' => $deductionData['items']['repayment']->amount
                ];
            }
            if ($deductionData && isset($deductionData['items']['taxDirectiveEntry'])) {
                $deductionItems[] = [
                    'name' => 'Directive Deduction Amount',
                    'amount' => $deductionData['items']['taxDirectiveEntry']->amount_of_tax_to_deduct
                ];
            }
            if (isset($benefitsData['items']['termination_lump_sum'])) {
                $deductionItems[] = [
                    'name' => 'Tax on Gratuities / Severance Benefits',
                    'amount' => $benefitsData['items']['termination_lump_sum']->amount_of_tax_to_deduct
                ];
            }
            // Prepare benefit items array
            $benefitItems = [];

            if (
                isset($regularIncomeData['items']['employer_loan']) &&
                (int) ($regularIncomeData['items']['employer_loan']->calculate_interest_benefit ?? 0) === 1
            ) {
                $interestBenefitAmount = (float) ($regularIncomeData['items']['employer_loan']->calculated_interest_benefit_amount ?? 0);
                if ($interestBenefitAmount > 0) {
                    $benefitItems[] = [
                        'name' => 'Interest Benefit',
                        'amount' => $interestBenefitAmount,
                    ];
                }
            }

            if (isset($benefitsData['items']['medical_aid'])) {
                $benefitItems[] = [
                    'name' => 'Medical Aid Benefit',
                    'amount' => $benefitsData['items']['medical_aid']->amount
                ];
            }
            if (isset($benefitsData['items']['bursary'])) {
                $benefitItems[] = [
                    'name' => 'Bursaries And Scholarships - taxable',
                    'amount' => $benefitsData['items']['bursary']->taxable_portion
                ];
                $benefitItems[] = [
                    'name' => 'Bursaries And Scholarships - exempt',
                    'amount' => $benefitsData['items']['bursary']->exempt_portion
                ];
            }
            if (isset($benefitsData['items']['benefit'])) {
                $benefitItems[] = [
                    'name' => 'Employees Debt Benefit',
                    'amount' => $benefitsData['items']['benefit']->amount
                ];
            }
            if (isset($benefitsData['items']['medical_cost'])) {
                $benefitItems[] = [
                    'name' => 'Medical Costs',
                    'amount' => $benefitsData['items']['medical_cost']->amount
                ];
            }
            if (isset($benefitsData['items']['covid'])) {
                $benefitItems[] = [
                    'name' => 'COVID-19 Disaster Relief',
                    'amount' => $benefitsData['items']['covid']->amount
                ];
            }
            if (isset($benefitsData['items']['long_service_award'])) {
                $benefitItems[] = [
                    'name' => 'Long Service Award - Cash',
                    'amount' => $benefitsData['items']['long_service_award']->long_cash_portion
                ];
                $benefitItems[] = [
                    'name' => 'Long Service Award - Non-Cash',
                    'amount' => $benefitsData['items']['long_service_award']->non_cash_portion
                ];
            }

            if (isset($benefitsData['items']['termination_lump_sum'])) {
                $benefitItems[] = [
                    'name' => 'Gratuities / Severance Benefits',
                    'amount' => $benefitsData['items']['termination_lump_sum']->directive_income_amount
                ];
            }
            if (isset($regularIncomeData['items']['companyCar'])) {
                $companyCar = $regularIncomeData['items']['companyCar'];
                $amount = ($companyCar->deemed_value * intval($companyCar->taxableType->percentage) / 100) ?? 0;
                $benefitItems[] = [
                    'name' => 'Company Car',
                    'amount' => $amount
                ];
            }
            // if (isset($regularIncomeData['items']['accommodation_benefit'])) {
            //     $benefitItems[] = [
            //         'name' => 'Accommodation Benefit',
            //         'amount' => $regularIncomeData['items']['accommodation_benefit']->amount
            //     ];
            // }

            // Prepare tax exemption items array
            $taxExemptionItems = [];

            // Add tax exemptions if they exist in your data
            // For example:
            // if (isset($regularIncomeData['items']['foreign_service_income'])) {
            //     $taxExemptionItems[] = [
            //         'name' => 'Foreign Service Income',
            //         'amount' => $regularIncomeData['items']['foreign_service_income']->amount
            //     ];
            // }

            // Prepare tax credit items array
            $taxCreditItems = [];

            // Add tax credits if they exist in your data
            if (isset($regularDeductionData['items']['medical_aid'])) {
                $taxCreditItems[] = [
                    'name' => 'Medical Aid Tax Credit',
                    'amount' => $regularDeductionData['items']['medical_aid']->tax_credit
                ];
            }

            // If no real tax exemption or credit data exists, you might want to remove these sections
            // or add placeholder data until you have real data

            // If no tax exemption items found, add a placeholder or leave empty
            // if (empty($taxExemptionItems)) {
            //     $taxExemptionItems[] = [
            //         'name' => 'Foreign Service Income',
            //         'amount' => 0
            //     ];
            // }

            // If no tax credit items found, add a placeholder or leave empty
            if (empty($taxCreditItems)) {
                $taxCreditItems[] = [
                    'name' => 'Medical Aid Tax Credit',
                    'amount' => 0
                ];
            }


            //settings
            $settings = getCompanyAllSetting();
            $sdl_number = isset($settings['sdl_number']) ? $settings['sdl_number'] : '';
            $tax_number = isset($settings['tax_number']) ? $settings['tax_number'] : '';
            $uif_number = isset($settings['uif_number']) ? $settings['uif_number'] : '';
            $company_name = isset($settings['company_name']) ? $settings['company_name'] : '';
            $company_address = isset($settings['company_address']) ? $settings['company_address'] : '';
            $company_city = isset($settings['company_city']) ? $settings['company_city'] : '';
            $company_state = isset($settings['company_state']) ? $settings['company_state'] : '';
            $company_country = isset($settings['company_country']) ? $settings['company_country'] : '';
            $company_zipcode = isset($settings['company_zipcode']) ? $settings['company_zipcode'] : '';
            $logoLightUrl = getLogoUrl($settings['logo_light'] ?? null, 'light');

            $data = [
                'employee' => [
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'period' => Carbon::parse($endOfMonthTerm)->startOfMonth()->format('d-m-Y') . ' to ' . Carbon::parse($term)->format('d-m-Y'),
                    'employment_date' => Carbon::parse($employee->date_of_appointment)->format('d-m-Y') ?? 'N/A',
                    'address' => [
                        'street' => '31 Looper Street Erand Gardens' ?? 'N/A',
                        'city' => 'Midrand' ?? 'N/A',
                        'state' => 'Johannesburg-1685,' ?? 'N/A',
                        'postal_code' => 'South Africa' ?? 'N/A'
                    ]
                ],
                'income' => [
                    'total' => $basicSalary + $totalIncome + $totalRegularInputIncome,
                    'basic_salary' => $basicSalary,
                    'items' => $incomeItems
                ],
                'allowance' => [
                    'total' => $totalAllowance,
                    'items' => $allowanceItems
                ],
                'deduction' => [
                    'total' => $totalDeduction,
                    'items' => $deductionItems
                ],
                'benefit' => [
                    'total' => $totalBenefit,
                    'items' => $benefitItems
                ],
                'tax_exemption' => [
                    'total' => array_sum(array_column($taxExemptionItems, 'amount')),
                    'items' => $taxExemptionItems
                ],
                'tax_credit' => [
                    'total' => array_sum(array_column($taxCreditItems, 'amount')),
                    'items' => $taxCreditItems
                ],
                'leave' => isset($leaveData) ? array_values(array_filter($leaveData, fn($item) => !empty($item['show_on_payslip']))) : [],
                'netPay' => $netPay,
                'company_name' => $company_name,
                'sdl_number' => $sdl_number,
                'tax_number' => $tax_number,
                'uif_number' => $uif_number,
                'logo_light_url' => $logoLightUrl,
                'company_address' => $company_address,
                'company_city' => $company_city,
                'company_state' => $company_state,
                'company_country' => $company_country,
                'company_zipcode' => $company_zipcode,
                'tax_year_label' => ($ty = TaxYear::resolveForTerm($term)) ? 'SARS Tax Year ' . $ty->label : null,
            ];
            $payslip->preview_payload = $data;
        }

        $pdf = Pdf::loadView('hrm.payslip.payslipPreview', compact('payslips'));
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isRemoteEnabled' => true,
        ]);


        return $pdf->stream('payslips-' . $originalTerm . '.pdf');
    }

    public function pending_pdf($term)
    {
        // Validate workspace exists
        $workspace = WorkSpace::find(Auth::user()->active_workspace);
        if (!$workspace) {
            return redirect()->back()->with('error', __('No active workspace found. Please select a workspace.'));
        }
        
        $payslips = PaySlip::where('salary_month', $term)->where('status', 0)->get();
        foreach ($payslips as $payslip) {
            $endOfMonthTerm = Carbon::parse($term . '-01')->endOfMonth()->format('Y-m-d');
            $employeeId = $payslip->employee_id;

            if (!$employeeId) {
                return redirect()->back()->with('error', 'Employee ID is missing.');
            }

            $employee = Employee::find($employeeId);
            if (!$employee) {
                return redirect()->back()->with('error', 'Employee not found.');
            }

            $payroll = Payroll::where('employee_id', $employeeId)->first();
            $basicSalaryData = BasicSalary::where('employee_id', $employeeId)->where('term', $term)->first();
            if ($basicSalaryData && $basicSalaryData->hourly_paid == 1) {
                $basicSalaryHour = BasicSalaryHour::where('employee_id', $employeeId)->where('term', $term)->first();
                if ($basicSalaryHour) {
                    $basicSalaryNormal = ($basicSalaryData->hourly_rate * $basicSalaryHour->normal_hours);
                    $taxYear = TaxYear::resolveForTerm($term);
                    $otMultiplier = $taxYear ? $taxYear->ot_multiplier : 1.5;
                    $basicSalaryOT = ($basicSalaryData->hourly_rate * $basicSalaryHour->ot_hours * $otMultiplier);
                    $basicSalary = round(($basicSalaryNormal + $basicSalaryOT), 2);
                } else {
                    $basicSalary = 0;
                }
            } else {
                $basicSalary = $basicSalaryData ? $basicSalaryData->fixed_salary : 0;
            }


            //regular inputs
            $regularIncomeData = $this->calculateRegularIncomeItems($employeeId, $term);
            $regularDeductionData = $this->calculateRegularDeductionItems($employeeId, $term);

            $totalRegularInputIncome = $regularIncomeData['totalRegularInputIncome'];
            $withoutTaxRegularInputIncome = $regularIncomeData['withoutTaxRegularInputIncome'];
            $totalRegularInputAllowance = $regularIncomeData['totalRegularInputAllowance'];
            $totalRegularInputDeduction = $regularDeductionData['totalRegularInputDeduction'];

            // payslip inputs
            $incomeData = $this->calculateIncomeItems($employeeId, $term);
            $allowanceData = $this->calculateAllowanceItems($employeeId, $term);
            $deductionData = $this->calculateDeductionItems($employeeId, $term);
            $benefitsData = $this->calculateBenefitItems($employeeId, $term);

            $totalIncome    = $incomeData['totalIncome'];
            $totalAllowance = $allowanceData['totalAllowance'] + $regularDeductionData['additionalIncome']
                + $regularIncomeData['totalRegularInputAllowance'] + $benefitsData['benefitAllowance']
                + $regularIncomeData['withoutTaxRegularInputIncome'];
            $totalDeduction = $deductionData['totalDeduction'];
            $totalBenefit   = $benefitsData['totalBenefit'];
  
            // Get employee leave data (you'll need to implement this)
            $leaveData = $this->getEmployeeLeaveData($employeeId, $endOfMonthTerm);
            $totalUnpaidLeaveDays = array_sum(array_column($leaveData, 'unpaid_leave'));
            $unpaidLeaveDeduction = 0;
            if ($totalUnpaidLeaveDays > 0) {
                $dailySalary = $basicSalary / 30;
                $unpaidLeaveDeduction = $dailySalary * $totalUnpaidLeaveDays;
            }
            // Calculate deductions
            $taxYear = TaxYear::resolveForTerm($term);
            $uifRate = $taxYear ? $taxYear->uif_rate : 0.01;
            $uifCeiling = $taxYear ? $taxYear->uif_ceiling : 177.12;
            $sdlRate = $taxYear ? $taxYear->sdl_rate : 0.01;

            $uif = ($basicSalary + $totalIncome - $totalDeduction) * $uifRate;
            if ($uif > $uifCeiling) {
                $uif = $uifCeiling;
            }

            $company_settings = getCompanyAllSetting();
            $sdl = 0;
            if (!empty($company_settings['is_sdl_calculate']) && $company_settings['is_sdl_calculate'] == 1) {
                $sdl = ($payslip->basic_salary + $payslip->allowance + $payslip->other_payment) * $sdlRate;
            }
            $taxableIncome = $basicSalary + $totalIncome + $totalAllowance + $totalBenefit + $totalRegularInputIncome - $withoutTaxRegularInputIncome;
            $payTax = $this->calculateTax($employeeId, $taxableIncome, $term);

            $totalDeduction += $uif + $sdl + $payTax + $totalRegularInputDeduction + $incomeData['additionalDeductions'] + $allowanceData['allowanceDeductions'] + $benefitsData['benefitDeduction'] - $unpaidLeaveDeduction;
            $netPay = $basicSalary + $totalIncome + $totalAllowance + $totalBenefit + $totalRegularInputIncome  + $withoutTaxRegularInputIncome + $totalRegularInputAllowance - $totalDeduction;

            $incomeItems = [
                ['name' => 'Basic Salary', 'amount' => $basicSalary]
            ];

            if (isset($regularIncomeData['items']['commission'])) {
                $incomeItems[] = [
                    'name' => 'Commission',
                    'amount' => $regularIncomeData['items']['commission']->commission_amount
                ];
            }
            if (isset($regularIncomeData['items']['income_policy'])) {
                $incomeItems[] = [
                    'name' => 'Loss of Income Policy Payout',
                    'amount' => $regularIncomeData['items']['income_policy']->payout_amount
                ];
            }
            if (isset($regularIncomeData['items']['accommodation_benefit'])) {
                $incomeItems[] = [
                    'name' => 'Accommodation Benefits',
                    'amount' => $regularIncomeData['items']['accommodation_benefit']->amount
                ];
            }
            if (isset($regularIncomeData['items']['bursaries_scholarships'])) {
                $incomeItems[] = [
                    'name' => 'Bursaries Scholarships - Taxable Portion',
                    'amount' => $regularIncomeData['items']['bursaries_scholarships']->taxable_portion
                ];
                $incomeItems[] = [
                    'name' => 'Bursaries Scholarships - Exempt Portion',
                    'amount' => $regularIncomeData['items']['bursaries_scholarships']->exempt_portion
                ];
            }
            if (isset($regularIncomeData['items']['companyCar'])) {
                $incomeItems[] = [
                    'name' => 'Company Car',
                    'amount' => $regularIncomeData['items']['companyCar']->taxable_value
                ];
            }
            if (isset($regularIncomeData['items']['companyCarUnderOperating'])) {
                $incomeItems[] = [
                    'name' => 'Company Car Under Operating Lease',
                    'amount' => $regularIncomeData['items']['companyCarUnderOperating']->taxable_value
                ];
            }

            // Add other income items
            if (isset($incomeData['items']['annual_bonus'])) {
                $incomeItems[] = [
                    'name' => 'Annual Bonus',
                    'amount' => $incomeData['items']['annual_bonus']->bonus_amount
                ];
            }

            if (isset($incomeData['items']['annual_payment'])) {
                $incomeItems[] = [
                    'name' => 'Annual Payment',
                    'amount' => $incomeData['items']['annual_payment']->annual_amount
                ];
            }

            if (isset($incomeData['items']['extra_pay'])) {
                $incomeItems[] = [
                    'name' => 'Extra Pay',
                    'amount' => $incomeData['items']['extra_pay']->amount
                ];
            }

            if (isset($incomeData['items']['once_off_commission'])) {
                $incomeItems[] = [
                    'name' => 'Once-off Commission',
                    'amount' => $incomeData['items']['once_off_commission']->commission_amount
                ];
            }

            if (isset($incomeData['items']['restraints_of_trade'])) {
                $incomeItems[] = [
                    'name' => 'Restraint of Trade',
                    'amount' => $incomeData['items']['restraints_of_trade']->amount
                ];
            }
            if (isset($incomeData['items']['arbitration_award'])) {
                $incomeItems[] = [
                    'name' => 'Arbitration Award',
                    'amount' => $incomeData['items']['arbitration_award']->directive_income_amount
                ];
            }
            if (isset($incomeData['items']['dividends_subject'])) {
                $incomeItems[] = [
                    'name' => 'Dividends Subject',
                    'amount' => $incomeData['items']['dividends_subject']->directive_income_amount
                ];
            }

            // Prepare allowance items array
            $allowanceItems = [];
            if (isset($regularIncomeData['items']['travel_allowance'])) {
                $allowanceItems[] = [
                    'name' => 'Travel Allowance',
                    'amount' => $regularIncomeData['items']['travel_allowance']->fixed_amount
                ];
            }
            if (isset($regularIncomeData['items']['employer_loan'])) {
                $allowanceItems[] = [
                    'name' => 'Employer Loan',
                    'amount' => $regularIncomeData['items']['employer_loan']->regular_repayment
                ];
            }
            if (isset($allowanceData['items']['broad_based_employee'])) {
                $allowanceItems[] = [
                    'name' => 'Broad Based Employee Share Plan',
                    'amount' => $allowanceData['items']['broad_based_employee']->amount
                ];
            }

            if (isset($allowanceData['items']['computer_allowance'])) {
                $allowanceItems[] = [
                    'name' => 'Computer Allowance',
                    'amount' => $allowanceData['items']['computer_allowance']->computer_allowance
                ];
            }
            if (isset($allowanceData['items']['expense_claim'])) {
                $allowanceItems[] = [
                    'name' => 'Expense Claim',
                    'amount' => $allowanceData['items']['expense_claim']->amount
                ];
            }

            if (isset($allowanceData['items']['phone_allowance'])) {
                $allowanceItems[] = [
                    'name' => 'Phone Allowance',
                    'amount' => $allowanceData['items']['phone_allowance']->phone_allowance_amount
                ];
            }
            if (isset($allowanceData['items']['relocation_allowance'])) {
                $allowanceItems[] = [
                    'name' => 'Relocation Allowance - Taxable',
                    'amount' => $allowanceData['items']['relocation_allowance']->taxable_allowance
                ];
                $allowanceItems[] = [
                    'name' => 'Relocation Allowance - Non-Taxable',
                    'amount' => $allowanceData['items']['relocation_allowance']->non_taxable_allowance
                ];
            }
            if (isset($allowanceData['items']['allowance_international'])) {
                $allowanceItems[] = [
                    'name' => 'Subsistence Allowance International',
                    'amount' => $allowanceData['items']['allowance_international']->paid_to_employee
                ];
            }
            if (isset($allowanceData['items']['subsistence_allowance'])) {
                $allowanceItems[] = [
                    'name' => 'Subsistence Allowance Local',
                    'amount' => $allowanceData['items']['subsistence_allowance']->full_amount_paid
                ];
            }
            if (isset($allowanceData['items']['tool_allowance'])) {
                $allowanceItems[] = [
                    'name' => 'Tool Allowance',
                    'amount' => $allowanceData['items']['tool_allowance']->amount
                ];
            }
            if (isset($allowanceData['items']['uniform_allowance'])) {
                $allowanceItems[] = [
                    'name' => 'Uniform Allowance',
                    'amount' => $allowanceData['items']['uniform_allowance']->amount
                ];
            }
            if (isset($allowanceData['items']['equity_instruments'])) {
                $allowanceItems[] = [
                    'name' => 'Gain from Vesting Equity',
                    'amount' => $allowanceData['items']['equity_instruments']->directive_income_amount
                ];
            }
            if (isset($benefitsData['items']['ters_payout'])) {
                $allowanceItems[] = [
                    'name' => 'TERS Payout',
                    'amount' => $benefitsData['items']['ters_payout']->amount
                ];
            }
            if (isset($regularDeductionData['items']['medical_aid'])) {
                $allowanceItems[] = [
                    'name' => 'Medical Aid Benefit Paid Out',
                    'amount' => $regularDeductionData['items']['medical_aid']->employer_contribution
                ];
            }
            // Prepare deduction items array
            $deductionItems = [
                ['name' => 'UIF', 'amount' => $uif],
                ['name' => 'Vat (PAYE)', 'amount' => $payTax],
                ['name' => 'SDL', 'amount' => $sdl]
            ];

            if ($unpaidLeaveDeduction > 0) {
                $deductionItems[] = [
                    'name' => 'Loss of Pay (Unpaid Leave)',
                    'amount' => $unpaidLeaveDeduction
                ];
            }
            // Add other deductions if they exist in your data
            if (isset($incomeData['items']['arbitration_award'])) {
                $deductionItems[] = [
                    'name' => 'Tax on Arbitration Award',
                    'amount' => $incomeData['items']['arbitration_award']->tax_to_deduct
                ];
            }
            if (isset($incomeData['items']['dividends_subject'])) {
                $deductionItems[] = [
                    'name' => 'Tax on Dividends Restricted Equity',
                    'amount' => $incomeData['items']['dividends_subject']->tax_to_deduct
                ];
            }
            if (isset($allowanceData['items']['equity_instruments'])) {
                $deductionItems[] = [
                    'name' => 'Tax on Gain from Vesting Equity',
                    'amount' => $allowanceData['items']['equity_instruments']->tax_deduct_amount
                ];
            }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['garnishee'])) {
                $deductionItems[] = [
                    'name' => 'Garnishee',
                    'amount' => $regularDeductionData['items']['garnishee']->installment
                ];
            }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['incomeProtection'])) {
                $deductionItems[] = [
                    'name' => 'Income Protection',
                    'amount' => $regularDeductionData['items']['incomeProtection']->amount_deducted
                ];
            }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['maintenance_order'])) {
                $deductionItems[] = [
                    'name' => 'Maintenance Order',
                    'amount' => $regularDeductionData['items']['maintenance_order']->installment
                ];
            }
            
            // if ($regularDeductionData['items'] && isset($regularDeductionData['items']['medical_aid'])) {
            //     $deductionItems[] = [
            //         'name' => 'Medical Aid',
            //         'amount' => $regularDeductionData['items']['medical_aid']->medical_aid
            //     ];
            // }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['pension_fund'])) {
                $deductionItems[] = [
                    'name' => 'Pension Fund',
                    'amount' => $regularDeductionData['items']['pension_fund']->fixed_contribution_employee
                ];
            }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['provident_fund'])) {
                $deductionItems[] = [
                    'name' => 'Provident Fund',
                    'amount' => $regularDeductionData['items']['provident_fund']->fixed_contribution_employee
                ];
            }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['retirement_annuity'])) {
                $deductionItems[] = [
                    'name' => 'Retirement Annuity',
                    'amount' => $regularDeductionData['items']['retirement_annuity']->amount
                ];
            }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['union_membership'])) {
                $deductionItems[] = [
                    'name' => 'Union Membership',
                    'amount' => $regularDeductionData['items']['union_membership']->amount_per_period
                ];
            }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['tax_over_deduction'])) {
                $deductionItems[] = [
                    'name' => 'Voluntary Tax Over Deduction',
                    'amount' => $regularDeductionData['items']['tax_over_deduction']->per_period
                ];
            }
            if ($deductionData['items'] && isset($deductionData['items']['medical_aid'])) {
                $deductionItems[] = [
                    'name' => 'Medical Aid - Employee',
                    'amount' => $deductionData['items']['medical_aid']->amount
                ];
            }
            if ($deductionData['items'] && isset($deductionData['items']['donation'])) {
                $deductionItems[] = [
                    'name' => 'Donations',
                    'amount' => $deductionData['items']['donation']->amount
                ];
            }
            if ($deductionData['items'] && isset($deductionData['items']['staff_purchase'])) {
                $deductionItems[] = [
                    'name' => 'Staff Purchases',
                    'amount' => $deductionData['items']['staff_purchase']->amount
                ];
            }
            if ($deductionData['items'] && isset($deductionData['items']['repayment'])) {
                $deductionItems[] = [
                    'name' => 'Repayment of Loan',
                    'amount' => $deductionData['items']['repayment']->amount
                ];
            }
            if (isset($benefitsData['items']['termination_lump_sum'])) {
                $deductionItems[] = [
                    'name' => 'Tax on Gratuities / Severance Benefits',
                    'amount' => $benefitsData['items']['termination_lump_sum']->amount_of_tax_to_deduct
                ];
            }
            // Prepare benefit items array
            $benefitItems = [];

            if (
                isset($regularIncomeData['items']['employer_loan']) &&
                (int) ($regularIncomeData['items']['employer_loan']->calculate_interest_benefit ?? 0) === 1
            ) {
                $interestBenefitAmount = (float) ($regularIncomeData['items']['employer_loan']->calculated_interest_benefit_amount ?? 0);
                if ($interestBenefitAmount > 0) {
                    $benefitItems[] = [
                        'name' => 'Interest Benefit',
                        'amount' => $interestBenefitAmount,
                    ];
                }
            }

            if (isset($benefitsData['items']['medical_aid'])) {
                $benefitItems[] = [
                    'name' => 'Medical Aid Benefit',
                    'amount' => $benefitsData['items']['medical_aid']->amount
                ];
            }
            if (isset($benefitsData['items']['bursary'])) {
                $benefitItems[] = [
                    'name' => 'Bursaries And Scholarships - taxable',
                    'amount' => $benefitsData['items']['bursary']->taxable_portion
                ];
                $benefitItems[] = [
                    'name' => 'Bursaries And Scholarships - exempt',
                    'amount' => $benefitsData['items']['bursary']->exempt_portion
                ];
            }
            if (isset($benefitsData['items']['benefit'])) {
                $benefitItems[] = [
                    'name' => 'Employees Debt Benefit',
                    'amount' => $benefitsData['items']['benefit']->amount
                ];
            }
            if (isset($benefitsData['items']['medical_cost'])) {
                $benefitItems[] = [
                    'name' => 'Medical Costs',
                    'amount' => $benefitsData['items']['medical_cost']->amount
                ];
            }
            if (isset($benefitsData['items']['covid'])) {
                $benefitItems[] = [
                    'name' => 'COVID-19 Disaster Relief',
                    'amount' => $benefitsData['items']['covid']->amount
                ];
            }
            if (isset($benefitsData['items']['long_service_award'])) {
                $benefitItems[] = [
                    'name' => 'Long Service Award - Cash',
                    'amount' => $benefitsData['items']['long_service_award']->long_cash_portion
                ];
                $benefitItems[] = [
                    'name' => 'Long Service Award - Non-Cash',
                    'amount' => $benefitsData['items']['long_service_award']->non_cash_portion
                ];
            }

            if (isset($benefitsData['items']['termination_lump_sum'])) {
                $benefitItems[] = [
                    'name' => 'Gratuities / Severance Benefits',
                    'amount' => $benefitsData['items']['termination_lump_sum']->directive_income_amount
                ];
            }

            if (isset($regularIncomeData['items']['accommodation_benefit'])) {
                $benefitItems[] = [
                    'name' => 'Accommodation Benefit',
                    'amount' => $regularIncomeData['items']['accommodation_benefit']->amount
                ];
            }

            // Prepare tax exemption items array
            $taxExemptionItems = [];

            // Add tax exemptions if they exist in your data
            // For example:
            // if (isset($regularIncomeData['items']['foreign_service_income'])) {
            //     $taxExemptionItems[] = [
            //         'name' => 'Foreign Service Income',
            //         'amount' => $regularIncomeData['items']['foreign_service_income']->amount
            //     ];
            // }

            // Prepare tax credit items array
            $taxCreditItems = [];

            // Add tax credits if they exist in your data
            if (isset($regularDeductionData['items']['medical_aid'])) {
                $taxCreditItems[] = [
                    'name' => 'Medical Aid Tax Credit',
                    'amount' => $regularDeductionData['items']['medical_aid']->tax_credit
                ];
            }

            // If no real tax exemption or credit data exists, you might want to remove these sections
            // or add placeholder data until you have real data

            // If no tax exemption items found, add a placeholder or leave empty
            // if (empty($taxExemptionItems)) {
            //     $taxExemptionItems[] = [
            //         'name' => 'Foreign Service Income',
            //         'amount' => 0
            //     ];
            // }

            // If no tax credit items found, add a placeholder or leave empty
            if (empty($taxCreditItems)) {
                $taxCreditItems[] = [
                    'name' => 'Medical Aid Tax Credit',
                    'amount' => 0
                ];
            }

            //settings
            $settings = getCompanyAllSetting();
            $sdl_number = isset($settings['sdl_number']) ? $settings['sdl_number'] : '';
            $tax_number = isset($settings['tax_number']) ? $settings['tax_number'] : '';
            $uif_number = isset($settings['uif_number']) ? $settings['uif_number'] : '';
            $company_name = isset($settings['company_name']) ? $settings['company_name'] : '';
            $company_address = isset($settings['company_address']) ? $settings['company_address'] : '';
            $company_city = isset($settings['company_city']) ? $settings['company_city'] : '';
            $company_state = isset($settings['company_state']) ? $settings['company_state'] : '';
            $company_country = isset($settings['company_country']) ? $settings['company_country'] : '';
            $company_zipcode = isset($settings['company_zipcode']) ? $settings['company_zipcode'] : '';
            $logoLightUrl = getLogoUrl($settings['logo_light'] ?? null, 'light');

            $data = [
                'employee' => [
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'period' => date('Y-m-01') . ' to ' . date('Y-m-t'),
                    'employment_date' => $employee->employment_date ?? 'N/A',
                    'address' => [
                        'street' => '31 Looper Street Erand Gardens' ?? 'N/A',
                        'city' => 'Midrand' ?? 'N/A',
                        'state' => 'Johannesburg-1685,' ?? 'N/A',
                        'postal_code' => 'South Africa' ?? 'N/A'
                    ]
                ],
                'income' => [
                    'total' => $basicSalary + $totalIncome + $totalRegularInputIncome  + $withoutTaxRegularInputIncome,
                    'basic_salary' => $basicSalary,
                    'items' => $incomeItems
                ],
                'allowance' => [
                    'total' => $totalAllowance,
                    'items' => $allowanceItems
                ],
                'deduction' => [
                    'total' => $totalDeduction,
                    'items' => $deductionItems
                ],
                'benefit' => [
                    'total' => $totalBenefit,
                    'items' => $benefitItems
                ],
                'tax_exemption' => [
                    'total' => array_sum(array_column($taxExemptionItems, 'amount')),
                    'items' => $taxExemptionItems
                ],
                'tax_credit' => [
                    'total' => array_sum(array_column($taxCreditItems, 'amount')),
                    'items' => $taxCreditItems
                ],
                'leave' => isset($leaveData) ? array_values(array_filter($leaveData, fn($item) => !empty($item['show_on_payslip']))) : [],
                'netPay' => $netPay,
                'company_name' => $company_name,
                'sdl_number' => $sdl_number,
                'tax_number' => $tax_number,
                'uif_number' => $uif_number,
                'logo_light_url' => $logoLightUrl,
                'company_address' => $company_address,
                'company_city' => $company_city,
                'company_state' => $company_state,
                'company_country' => $company_country,
                'company_zipcode' => $company_zipcode,
                'tax_year_label' => ($ty = TaxYear::resolveForTerm($term)) ? 'SARS Tax Year ' . $ty->label : null,
            ];
            $payslip->preview_payload = $data;
        }

        $pdf = Pdf::loadView('hrm.payslip.payslipPreview', compact('payslips'));
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isRemoteEnabled' => true,
        ]);

        return $pdf->stream('payslips-' . $term . '.pdf');
    }
    private function getEmployeeLeaveData($employeeId, $term)
    {
        $leaveData = [];
        $workspaceId = getActiveWorkspace();
        $employee = Employee::find($employeeId);

        if (!$employee) {
            return [];
        }

        try {
            $monthStartDate = Carbon::createFromFormat('Y-m-d', $term)->startOfMonth();
            $monthEndDate = Carbon::createFromFormat('Y-m-d', $term)->endOfMonth();
        } catch (\Exception $e) {
            return [];
        }
        $entitlementPolicies = EmployeeEntitlementPolicy::with(['leaveManagement', 'entitlementPolicy'])
            ->where('workspace', $workspaceId)
            ->where('employee_id', $employeeId)
            ->get();
        $accrualService = app(LeaveAccrualService::class);

        foreach ($entitlementPolicies as $policy) {
            $leaveManagement = $policy->leaveManagement;
            if (!$leaveManagement) {
                continue;
            }

            $summary = $accrualService->getLeaveSummaryForTerm($policy, $employee, $monthStartDate);
            if (!$summary['eligible']) {
                continue;
            }

            $leaveTakenThisTerm = $summary['taken_this_term'];
            $remainingBalance = $summary['remaining_after_term'];
            $paid_leave = $summary['paid_leave'];
            $unpaid_leave = $summary['unpaid_leave'];

            if ($remainingBalance > 0 || $leaveTakenThisTerm > 0) {
                $leaveData[] = [
                    'type' => $leaveManagement->leave_name,
                    'balance' => $remainingBalance > 0 ? $remainingBalance : 0,
                    'taken' => $leaveTakenThisTerm,
                    'paid_leave' => $paid_leave,
                    'unpaid_leave' => $unpaid_leave,
                    'show_on_payslip' => (bool) $leaveManagement->show_on_payslip,
                ];
            }
        }
        return $leaveData;
    }

    //regular inputs
    private function calculateRegularIncomeItems($employeeId, $term)
    {
        $totalRegularInputIncome = 0;
        $totalRegularInputAllowance = 0;
        $withoutTaxRegularInputIncome = 0;
        $items = [];

        // income policy
        $income_policy = IncomePolicy::where('employee_id', $employeeId)->where('term', $term)->first();
        if ($income_policy) {
            $totalRegularInputIncome += $income_policy->payout_amount;
            $items['income_policy'] = $income_policy;
        }

        // payslip commission
        $commission = PayslipCommission::where('employee_id', $employeeId)->where('term', $term)->first();
        if ($commission) {
            $totalRegularInputIncome += $commission->commission_amount;
            $items['commission'] = $commission;
        }

        // Travel Allowance
        $travel_allowance = TravelAllowance::where('employee_id', $employeeId)->latest('id')->where('term', $term)->first();
        if ($travel_allowance) {
            $taxablePercentage = $travel_allowance->subject_to_20_tax ? 0.20 : 0.80;
            $nonTaxablePercentage = 1 - $taxablePercentage;

            $totalRegularInputAllowance += ($travel_allowance->fixed_amount * $taxablePercentage);
            $withoutTaxRegularInputIncome += ($travel_allowance->fixed_amount * $nonTaxablePercentage);
            $items['travel_allowance'] = $travel_allowance;
        }

        // accommodation benefits
        $accommodation_benefit = AccommodationBenefit::where('employee_id', $employeeId)->where('term', $term)->first();
        if ($accommodation_benefit) {
            $totalRegularInputIncome += $accommodation_benefit->amount;
            $items['accommodation_benefit'] = $accommodation_benefit;
        }

        // bursaries_scholarships
        $bursaries_scholarships = BursariesScholarship::where('employee_id', $employeeId)->where('term', $term)->first();

        if (!empty($bursaries_scholarships) && is_object($bursaries_scholarships)) {
            $items['bursaries_scholarships'] = $bursaries_scholarships;
        }

        // Company car
        $companyCar = CompanyCar::where('employee_id', $employeeId)->where('term', $term)->first();
        if ($companyCar) {
            $items['companyCar'] = $companyCar;
        }

        // Company Car Under Operating Lease
        $companyCarUnderOperating = CompanyCarUnderOperating::where('employee_id', $employeeId)->where('term', $term)->first();
        if ($companyCarUnderOperating) {
            $totalRegularInputIncome += ($companyCarUnderOperating->amount * intval($companyCarUnderOperating->taxable_percentage) / 100);
            $companyCarUnderOperating->taxable_value = ($companyCarUnderOperating->amount * intval($companyCarUnderOperating->taxable_percentage) / 100);
            $items['companyCarUnderOperating'] =   $companyCarUnderOperating;
        }
        $savings_deduction = SavingsDeduction::where('employee_id', $employeeId)->where('term', $term)->first();

        if ($savings_deduction) {
            $totalRegularInputIncome += $savings_deduction->regular_deduction;
            $items['savings_deduction'] = $savings_deduction;
        }

        $employerLoan = EmployerLoan::where('employee_id', $employeeId)->where('term', $term)->first();
        if ($employerLoan) {
            $calculatedInterestBenefit = $this->calculateEmployerLoanInterestBenefit($employerLoan, $employeeId, $term);
            $withoutTaxRegularInputIncome += $employerLoan->regular_repayment;
            if ((int) $employerLoan->calculate_interest_benefit === 1) {
                $totalRegularInputIncome += $calculatedInterestBenefit;
                $employerLoan->calculated_interest_benefit_amount = $calculatedInterestBenefit;
            }
            $items['employer_loan'] = $employerLoan;
        }

        // Tax Directive
        $taxDirectiveEntry = TaxDirectiveEntry::where('employee_id', $employeeId)->where('term', $term)->first();

        if ($taxDirectiveEntry) {
            $taxDirectiveEntry->taxable_value = ($taxDirectiveEntry->directive_income_amount * intval($taxDirectiveEntry->percentage)) / 100;
            $totalRegularInputIncome += $taxDirectiveEntry->percentage;
            $items['taxDirectiveEntry'] = $taxDirectiveEntry;
        }

        return view('hrm.payroll.index', [
            'taxDirectiveEntry' => $taxDirectiveEntry,
            'totalRegularInputIncome' => $totalRegularInputIncome,
            'withoutTaxRegularInputIncome' => $withoutTaxRegularInputIncome,
            'totalRegularInputAllowance' => $totalRegularInputAllowance,
            'items' => $items
        ]);
    }

    private function calculateEmployerLoanInterestBenefit($employerLoan, $employeeId, $term): float
    {
        if ((int) ($employerLoan->calculate_interest_benefit ?? 0) !== 1) {
            return 0;
        }

        $loanAmount = (float) ($employerLoan->regular_repayment ?? 0);
        $termRepayment = (float) optional(
            Repayments::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first()
        )->amount;

        $netLoanExposure = max(0, $loanAmount - $termRepayment);
        $monthlyRate = ((float) ($employerLoan->interest_rate ?? 0)) / 100 / 12;

        return round($netLoanExposure * $monthlyRate, 2);
    }

    private function calculateRegularDeductionItems($employeeId, $term)
    {
        $totalRegularInputDeduction = 0;
        $additionalIncome = 0;
        $items = [];

        $garnishee = Garnishee::where('employee_id', $employeeId)->latest('id')->where('term', $term)->first();
        if ($garnishee) {
            $totalRegularInputDeduction +=  $garnishee->installment;
            $items['garnishee'] =  $garnishee;
        }
        $incomeProtection = IncomeProtection::where('employee_id', $employeeId)->where('term', $term)->first();
        if ($incomeProtection) {
            $totalRegularInputDeduction += $incomeProtection->amount_deducted;
            $items['incomeProtection'] =  $incomeProtection;
        }
        $maintenance_order = MaintenanceOrder::where('employee_id', $employeeId)->where('term', $term)->first();
        if ($maintenance_order) {
            $totalRegularInputDeduction += $maintenance_order->installment;
            $items['maintenance_order'] =  $maintenance_order;
        }

        $medical_aid = MedicalAid::where('employee_id', $employeeId)->where('term', $term)->first();
        if ($medical_aid) {
            $additionalIncome += $medical_aid->employer_contribution;
            $medical_aid->tax_credit = 0;
            if ($medical_aid->members == 0) {
                $medical_aid->tax_credit = 0;
            } elseif ($medical_aid->members == 1) {
                $medical_aid->tax_credit = 364;
            } elseif ($medical_aid->members == 2) {
                $medical_aid->tax_credit = 728;
            } elseif ($medical_aid->members == 3) {
                $medical_aid->tax_credit = 974;
            } elseif ($medical_aid->members == 4) {
                $medical_aid->tax_credit = 1220;
            } elseif ($medical_aid->members == 5) {
                $medical_aid->tax_credit = 1466;
            } elseif ($medical_aid->members == 6) {
                $medical_aid->tax_credit = 1712;
            } elseif ($medical_aid->members == 7) {
                $medical_aid->tax_credit = 1958;
            } elseif ($medical_aid->members == 8) {
                $medical_aid->tax_credit = 2204;
            } elseif ($medical_aid->members == 9) {
                $medical_aid->tax_credit = 2450;
            } elseif ($medical_aid->members == 10) {
                $medical_aid->tax_credit = 2696;
            }
            // $totalRegularInputDeduction += $medical_aid->tax_credit;
            $items['medical_aid'] =  $medical_aid;
        }

        $pension_fund = PensionFund::where('employee_id', $employeeId)->where('term', $term)->first();
        if ($pension_fund) {
            $totalRegularInputDeduction += $pension_fund->fixed_contribution_employee;
            $items['pension_fund'] =  $pension_fund;
        }

        $provident_fund = ProvidentFundPayroll::where('employee_id', $employeeId)->where('term', $term)->first();
        if ($provident_fund) {
            $totalRegularInputDeduction += $provident_fund->fixed_contribution_employee;
            $items['provident_fund'] =  $provident_fund;
        }

        $retirement_annuity = RetirementAnnuityFundPayroll::where('employee_id', $employeeId)->where('term', $term)->first();
        if ($retirement_annuity) {
            $totalRegularInputDeduction += $retirement_annuity->amount;
            $items['retirement_annuity'] =  $retirement_annuity;
        }

        $union_membership = UnionMembershipFee::where('employee_id', $employeeId)->where('term', $term)->first();
        if ($union_membership) {
            $totalRegularInputDeduction += $union_membership->amount_per_period;
            $items['union_membership'] =  $union_membership;
        }

        $tax_over_deduction = TaxOverDeduction::where('employee_id', $employeeId)->where('term', $term)->first();
        if ($tax_over_deduction) {
            $totalRegularInputDeduction += $tax_over_deduction->per_period;
            $items['tax_over_deduction'] =  $tax_over_deduction;
        }

        return [
            'totalRegularInputDeduction' => $totalRegularInputDeduction,
            'additionalIncome' => $additionalIncome,
            'items' => $items
        ];
    }

    //paylsipinputs

    private function calculateIncomeItems($employeeId, $term)
    {
        $totalIncome = 0;
        $additionalDeductions = 0;
        $items = [];

        // Annual bonus
        $annual_bonus = AnnualBonus::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($annual_bonus) {
            $totalIncome += $annual_bonus->bonus_amount;
            $items['annual_bonus'] = $annual_bonus;
        }

        // Annual payment
        $annual_payment = AnnualPayment::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($annual_payment) {
            $totalIncome += $annual_payment->annual_amount;
            $items['annual_payment'] = $annual_payment;
        }

        // Extra pay
        $extra_pay = ExtraPay::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($extra_pay) {
            $totalIncome += $extra_pay->amount;
            $items['extra_pay'] = $extra_pay;
        }

        // Once-off commission
        $once_off_commission = OnceOffCommission::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($once_off_commission) {
            $totalIncome += $once_off_commission->commission_amount;
            $items['once_off_commission'] = $once_off_commission;
        }

        // Restraints of trade
        $restraints_of_trade = RestraintOfTrade::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($restraints_of_trade) {
            $totalIncome += $restraints_of_trade->amount;
            $items['restraints_of_trade'] = $restraints_of_trade;
        }

        // Arbitration award
        $arbitration_award = ArbitrationAward::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($arbitration_award) {
            $totalIncome += $arbitration_award->directive_income_amount;
            $additionalDeductions += $arbitration_award->tax_to_deduct;
            $items['arbitration_award'] = $arbitration_award;
        }

        // Dividends subject
        $dividends_subject = DividendsSubject::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($dividends_subject) {
            $totalIncome += $dividends_subject->directive_income_amount;
            $additionalDeductions += $dividends_subject->tax_to_deduct;
            $items['dividends_subject'] = $dividends_subject;
        }

        return [
            'totalIncome' => $totalIncome,
            'additionalDeductions' => $additionalDeductions,
            'items' => $items
        ];
    }
    private function calculateAllowanceItems($employeeId, $term)
    {
        $totalAllowance = 0;
        $allowanceDeductions = 0;
        $items = [];

        // Broad Based Employee Share Plan
        $broad_based_employee = BroadBasedEmployee::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($broad_based_employee) {
            $totalAllowance += $broad_based_employee->amount;
            $items['broad_based_employee'] = $broad_based_employee;
        }

        // Computer Allowance
        $computer_allowance = ComputerAllowance::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($computer_allowance) {
            $totalAllowance += $computer_allowance->computer_allowance;
            $items['computer_allowance'] = $computer_allowance;
        }

        // Expense Claim
        $expense_claim = ExpenseClaim::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($expense_claim) {
            $totalAllowance += $expense_claim->amount;
            $items['expense_claim'] = $expense_claim;
        }

        // Gain on Vesting of Equity Instruments
        $equity_instruments = EquityInstrument::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($equity_instruments) {
            $totalAllowance += $equity_instruments->directive_income_amount;
            $allowanceDeductions += $equity_instruments->tax_deduct_amount;
            $items['equity_instruments'] = $equity_instruments;
        }

        // Phone Allowance
        $phone_allowance = PhoneAllowance::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($phone_allowance) {
            $totalAllowance += $phone_allowance->phone_allowance_amount;
            $items['phone_allowance'] = $phone_allowance;
        }

        // Relocation Allowance
        $relocation_allowance = RelocationAllowance::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($relocation_allowance) {
            $totalAllowance += $relocation_allowance->taxable_allowance;
            $totalAllowance += $relocation_allowance->non_taxable_allowance;
            $items['relocation_allowance'] = $relocation_allowance;
        }

        // Subsistence Allowance International
        $allowance_international = AllowanceInternational::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($allowance_international) {
            $totalAllowance += $allowance_international->paid_to_employee;
            $items['allowance_international'] = $allowance_international;
        }

        // Subsistence Allowance Local
        $subsistence_allowance = SubsistenceAllowance::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($subsistence_allowance) {
            $totalAllowance += $subsistence_allowance->full_amount_paid;
            $items['subsistence_allowance'] = $subsistence_allowance;
        }

        // Tool Allowance
        $tool_allowance = ToolAllowance::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($tool_allowance) {
            $totalAllowance += $tool_allowance->amount;
            $items['tool_allowance'] = $tool_allowance;
        }

        // Uniform Allowance
        $uniform_allowance = UniformAllowance::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($uniform_allowance) {
            $totalAllowance += $uniform_allowance->amount;
            $items['uniform_allowance'] = $uniform_allowance;
        }

        // bursaries_scholarships
        $bursaries_scholarships = BursariesScholarship::where('employee_id', $employeeId)->where('term', $term)->first();
        if (!empty($bursaries_scholarships) && is_object($bursaries_scholarships)) {
            if ($bursaries_scholarships->employee_handles_payment == 1) {
                $totalAllowance += $bursaries_scholarships->taxable_portion;
                $totalAllowance += $bursaries_scholarships->exempt_portion;
            }
            $items['bursaries_scholarships'] = $bursaries_scholarships;
        }

        $bursary = Bursary::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($bursary) {
            if ($bursary->employee_handles_payment == 1) {
                $totalAllowance += $bursary->taxable_portion;
                $totalAllowance += $bursary->exempt_portion;
            }
            $items['bursary'] = $bursary;
        }

        // Tax Directive
        $taxDirectiveEntry = TaxDirectiveEntry::where('employee_id', $employeeId)->where('term', $term)->first();

        if ($taxDirectiveEntry) {
            // $taxDirectiveEntry->taxable_value = ($taxDirectiveEntry->directive_income_amount * intval($taxDirectiveEntry->percentage)) / 100; // Percentage of directive income amount
            $totalAllowance += $taxDirectiveEntry->directive_income_amount;
            // $totalRegularInputIncome += $taxDirectiveEntry->percentage;
            $items['taxDirectiveEntry'] = $taxDirectiveEntry;
        }

        return [
            'totalAllowance' => $totalAllowance,
            'allowanceDeductions' => $allowanceDeductions,
            'items' => $items
        ];
    }
    private function calculateDeductionItems($employeeId, $term)
    {
        $totalDeduction = 0;
        $items = [];

        // Donations
        $donation = Donation::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($donation) {
            $totalDeduction += $donation->amount;
            $items['donation'] = $donation;
        }

        // Repayment Of Loan
        $repayment = Repayments::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($repayment) {
            $totalDeduction += $repayment->amount;
            $items['repayment'] = $repayment;
        }

        // Staff Purchases
        $staff_purchase = StaffPurchase::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($staff_purchase) {
            $totalDeduction += $staff_purchase->amount;
            $items['staff_purchase'] = $staff_purchase;
        }

        // Tax Directive
        $taxDirectiveEntry = TaxDirectiveEntry::where('employee_id', $employeeId)->where('term', $term)->first();

        if ($taxDirectiveEntry) {
            // $taxDirectiveEntry->taxable_value = ($taxDirectiveEntry->directive_income_amount * intval($taxDirectiveEntry->percentage)) / 100; // Percentage of directive income amount
            $totalDeduction += $taxDirectiveEntry->amount_of_tax_to_deduct;
            // $totalRegularInputIncome += $taxDirectiveEntry->percentage;
            $items['taxDirectiveEntry'] = $taxDirectiveEntry;
        }

        return [
            'totalDeduction' => $totalDeduction,
            'items' => $items
        ];
    }
    private function calculateBenefitItems($employeeId, $term)
    {
        $totalBenefit = 0;
        $benefitAllowance = 0;
        $benefitDeduction = 0;
        $items = [];

        // Employee's Debt Benefit
        $benefit = EmployeeBenefit::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($benefit) {
            $totalBenefit += $benefit->amount;
            $items['benefit'] = $benefit;
        }

        // Medical Costs
        $medical_cost = MedicalCost::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($medical_cost) {
            $totalBenefit += $medical_cost->amount;
            $items['medical_cost'] = $medical_cost;
        }

        // COVID-19 Disaster Relief
        $covid = Covid19Disaster::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($covid) {
            $totalBenefit += $covid->amount;
            $items['covid'] = $covid;
        }

        // Long Service Award
        $long_service_award = LongServiceAward::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($long_service_award) {
            $totalBenefit += $long_service_award->long_cash_portion;
            $totalBenefit += $long_service_award->non_cash_portion;
            $items['long_service_award'] = $long_service_award;
        }

        // TERS Payout
        $ters_payout = TersPayout::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($ters_payout) {
            $benefitAllowance += $ters_payout->amount;
            $items['ters_payout'] = $ters_payout;
        }

        // Termination Lump Sums
        $termination_lump_sum = TerminationLump::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($termination_lump_sum) {
            $totalBenefit += $termination_lump_sum->directive_income_amount;
            $benefitDeduction += $termination_lump_sum->amount_of_tax_to_deduct;
            $items['termination_lump_sum'] = $termination_lump_sum;
        }

        $companyCar = CompanyCar::where('employee_id', $employeeId)->where('term', $term)->first();
        if ($companyCar) {
            $amount = ($companyCar->deemed_value * intval($companyCar->taxableType->percentage) / 100) ?? 0;
            $totalBenefit += $amount;
        }

        return [
            'totalBenefit' => $totalBenefit,
            'benefitDeduction' => $benefitDeduction,
            'benefitAllowance' => $benefitAllowance,
            'items' => $items
        ];
    }
    private function calculateTax($employeeId, $monthlyIncome, $term)
    {
        return TaxCalculationService::calculateMonthlyPAYE($employeeId, $monthlyIncome, $term);
    }

    public function bulkFinalisation($term)
    {
        $payslips = PaySlip::where('salary_month', $term)->where('status', 0)->get();
        $endOfMonthTerm = Carbon::parse($term . '-01')->endOfMonth()->format('Y-m-d');
        foreach ($payslips as $payslip) {
            $employeeId = $payslip->employee_id;
            $employee = Employee::find($employeeId);
            if (!$employee) {
                return redirect()->back()->with('error', 'Employee not found.');
            }

            $payroll = Payroll::where('employee_id', $employeeId)->first();
            $basicSalaryData = BasicSalary::where('employee_id', $employeeId)->where('term', $term)->first();
            if ($basicSalaryData && $basicSalaryData->hourly_paid == 1) {
                $basicSalaryHour = BasicSalaryHour::where('employee_id', $employeeId)->where('term', $term)->first();
                if ($basicSalaryHour) {
                    $basicSalaryNormal = ($basicSalaryData->hourly_rate * $basicSalaryHour->normal_hours);
                    $taxYear = TaxYear::resolveForTerm($term);
                    $otMultiplier = $taxYear ? $taxYear->ot_multiplier : 1.5;
                    $basicSalaryOT = ($basicSalaryData->hourly_rate * $basicSalaryHour->ot_hours * $otMultiplier);
                    $basicSalary = round(($basicSalaryNormal + $basicSalaryOT), 2);
                } else {
                    $basicSalary = 0;
                }
            } else {
                $basicSalary = $basicSalaryData ? $basicSalaryData->fixed_salary : 0;
            }


            //regular inputs
            $regularIncomeData = $this->calculateRegularIncomeItems($employeeId, $term);
            $regularDeductionData = $this->calculateRegularDeductionItems($employeeId, $term);

            $totalRegularInputIncome = $regularIncomeData['totalRegularInputIncome'];
            $withoutTaxRegularInputIncome = $regularIncomeData['withoutTaxRegularInputIncome'];
            $totalRegularInputAllowance = $regularIncomeData['totalRegularInputAllowance'];
            $totalRegularInputDeduction = $regularDeductionData['totalRegularInputDeduction'];

            // payslip inputs
            $incomeData = $this->calculateIncomeItems($employeeId, $term);
            $allowanceData = $this->calculateAllowanceItems($employeeId, $term);
            $deductionData = $this->calculateDeductionItems($employeeId, $term);
            $benefitsData = $this->calculateBenefitItems($employeeId, $term);

            $totalIncome    = $incomeData['totalIncome'];
            $totalAllowance = $allowanceData['totalAllowance'] + $regularDeductionData['additionalIncome']
                + $regularIncomeData['totalRegularInputAllowance'] + $benefitsData['benefitAllowance']
                + $regularIncomeData['withoutTaxRegularInputIncome'];
            $totalDeduction = $deductionData['totalDeduction'];
            $totalBenefit   = $benefitsData['totalBenefit'];

            // Calculate deductions
            $taxYear = TaxYear::resolveForTerm($term);
            $uifRate = $taxYear ? $taxYear->uif_rate : 0.01;
            $uifCeiling = $taxYear ? $taxYear->uif_ceiling : 177.12;

            $uif = ($basicSalary + $totalIncome - $totalDeduction) * $uifRate;
            if ($uif > $uifCeiling) {
                $uif = $uifCeiling;
            }

            $taxableIncome = $basicSalary + $totalIncome + $totalAllowance + $totalBenefit + $totalRegularInputIncome - $withoutTaxRegularInputIncome;
            $payTax = $this->calculateTax($employeeId, $taxableIncome, $term);

            $totalDeduction += $uif + $payTax + $totalRegularInputDeduction + $incomeData['additionalDeductions'] + $allowanceData['allowanceDeductions'] + $benefitsData['benefitDeduction'];
            $netPay = $basicSalary + $totalIncome + $totalAllowance + $totalBenefit + $totalRegularInputIncome  + $withoutTaxRegularInputIncome + $totalRegularInputAllowance - $totalDeduction;

            $payslip->netPayValue = $netPay;
            $payslip->totalIncomeValue = $totalIncome + $totalRegularInputIncome;
            $payslip->basicSalaryValue = $basicSalary;
            $payslip->uifValue = $uif;
            $payslip->payTaxValue = $payTax;
            $payslip->totalDeductionValue = $totalDeduction;
            $payslip->totalBenefitValue = $totalBenefit;
            $payslip->totalAllowanceValue = $totalAllowance;
        }
        return View('hrm.payrun.bulkFinalisation', compact('payslips', 'term'));
    }


    public function bulkFinalisationStore(Request $request)
    {
        if (!$request->has('payslip_ids') || empty($request->payslip_ids)) {
            return redirect()->back()->with('error', 'Employee not selected.');
        }

        $payslip_ids = $request->payslip_ids;

        DB::beginTransaction();
        try {
            foreach ($payslip_ids as $payslip_id) {
                $payslipEmployee = Payslip::find($payslip_id);
                if (!$payslipEmployee) {
                    throw new Exception("Payslip with ID {$payslip_id} not found.");
                }
                $term = $payslipEmployee->salary_month;

                // Ensure a locked tax year exists for this term
                if (!TaxYear::resolveForTerm($term)) {
                    throw new Exception('No Tax Year configuration found for ' . Carbon::parse($term . '-01')->format('F Y') . '. Please contact customer support for assistance.');
                }

                $basicSalaryData = BasicSalary::where('employee_id', $payslipEmployee->employeeId)->where('term', $term)->first();
                if ($basicSalaryData && $basicSalaryData->hourly_paid == 1) {
                    $basicSalaryHour = BasicSalaryHour::where('employee_id', $payslipEmployee->employeeId)->where('term', $term)->first();
                    if ($basicSalaryHour) {
                        $basicSalaryNormal = ($basicSalaryData->hourly_rate * $basicSalaryHour->normal_hours);
                        $taxYear = TaxYear::resolveForTerm($term);
                        $otMultiplier = $taxYear ? $taxYear->ot_multiplier : 1.5;
                        $basicSalaryOT = ($basicSalaryData->hourly_rate * $basicSalaryHour->ot_hours * $otMultiplier);
                        $basicSalary = round(($basicSalaryNormal + $basicSalaryOT), 2);
                    } else {
                        $basicSalary = 0;
                    }
                } else {
                    $basicSalary = $basicSalaryData ? $basicSalaryData->fixed_salary : 0;
                }

                //regular inputs
                $regularIncomeData = $this->calculateRegularIncomeItems($payslipEmployee->employeeId, $term);
                $regularDeductionData = $this->calculateRegularDeductionItems($payslipEmployee->employeeId, $term);

                $totalRegularInputIncome = $regularIncomeData['totalRegularInputIncome'];
                $withoutTaxRegularInputIncome = $regularIncomeData['withoutTaxRegularInputIncome'];
                $totalRegularInputAllowance = $regularIncomeData['totalRegularInputAllowance'];
                $totalRegularInputDeduction = $regularDeductionData['totalRegularInputDeduction'];

                // payslip inputs
                $incomeData = $this->calculateIncomeItems($payslipEmployee->employeeId, $term);
                $allowanceData = $this->calculateAllowanceItems($payslipEmployee->employeeId, $term);
                $deductionData = $this->calculateDeductionItems($payslipEmployee->employeeId, $term);
                $benefitsData = $this->calculateBenefitItems($payslipEmployee->employeeId, $term);

                $totalIncome    = $incomeData['totalIncome'];
                $totalAllowance = $allowanceData['totalAllowance'] + $regularDeductionData['additionalIncome']
                    + $regularIncomeData['totalRegularInputAllowance'] + $benefitsData['benefitAllowance']
                    + $regularIncomeData['withoutTaxRegularInputIncome'];
                $totalDeduction = $deductionData['totalDeduction'];
                $totalBenefit   = $benefitsData['totalBenefit'];

                // Calculate deductions
                $taxYear = TaxYear::resolveForTerm($term);
                $uifRate = $taxYear ? $taxYear->uif_rate : 0.01;
                $uifCeiling = $taxYear ? $taxYear->uif_ceiling : 177.12;

                $uif = ($basicSalary + $totalIncome - $totalDeduction) * $uifRate;
                if ($uif > $uifCeiling) {
                    $uif = $uifCeiling;
                }

                $taxableIncome = $basicSalary + $totalIncome + $totalAllowance + $totalBenefit + $totalRegularInputIncome - $withoutTaxRegularInputIncome;
                $payTax = $this->calculateTax($payslipEmployee->employeeId, $taxableIncome, $term);

                $totalDeduction += $uif + $payTax + $totalRegularInputDeduction + $incomeData['additionalDeductions'] + $allowanceData['allowanceDeductions'] + $benefitsData['benefitDeduction'];
                $netPay = $basicSalary + $totalIncome + $totalAllowance + $totalBenefit + $totalRegularInputIncome  + $withoutTaxRegularInputIncome + $totalRegularInputAllowance - $totalDeduction;

                // Update payslip
                $payslipEmployee->net_payble = $netPay;
                $payslipEmployee->status = 1;
                $payslipEmployee->basic_salary = $basicSalary;
                $payslipEmployee->allowance = $totalAllowance;
                $payslipEmployee->commission = 0;
                $payslipEmployee->loan = 0;
                $payslipEmployee->saturation_deduction = $totalDeduction;
                $payslipEmployee->other_payment = $totalBenefit;
                $payslipEmployee->overtime = 0;
                $payslipEmployee->company_contribution = 0;
                $payslipEmployee->workspace = getActiveWorkspace();
                $payslipEmployee->created_by = creatorId();
                $payslipEmployee->tax_year_id = $taxYear?->id;
                $payslipEmployee->paye_amount = $payTax;
                $payslipEmployee->uif_amount = $uif;
                $payslipEmployee->sdl_amount = 0;
                $payslipEmployee->save();

                // Create new payslip
                $new_month_year = Carbon::parse($payslipEmployee->salary_month . '-01')->addMonth()->endOfMonth()->format('Y-m-d');
                $payslipExist = PaySlip::where('salary_month', $new_month_year)->where('employee_id', $payslipEmployee->employee_id)->first();
                if (!$payslipExist) {
                    $newPayslipEmployee = new PaySlip();
                    $newPayslipEmployee->employee_id = $payslipEmployee->employee_id;
                    $newPayslipEmployee->net_payble = 0;
                    $newPayslipEmployee->salary_month = $new_month_year;
                    $newPayslipEmployee->status = 0;
                    $newPayslipEmployee->basic_salary = 0;
                    $newPayslipEmployee->allowance = 0;
                    $newPayslipEmployee->commission = 0;
                    $newPayslipEmployee->loan = 0;
                    $newPayslipEmployee->saturation_deduction = 0;
                    $newPayslipEmployee->other_payment = 0;
                    $newPayslipEmployee->overtime = 0;
                    $newPayslipEmployee->company_contribution = 0;
                    $newPayslipEmployee->workspace = getActiveWorkspace();
                    $newPayslipEmployee->created_by = creatorId();
                    $newPayslipEmployee->save();
                }
            }

            // Commit transaction if everything is successful
            DB::commit();
            return redirect()->route('payrun.index')->with('success', 'Bulk finalisation done successfully.');
        } catch (Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            return redirect()->back()->with('error', 'Error Occurred: ' . $e->getMessage());
        }
    }
    public function payslip_pdf($term)
    {
        // Validate workspace exists
        $workspace = WorkSpace::find(Auth::user()->active_workspace);
        if (!$workspace) {
            return redirect()->back()->with('error', __('No active workspace found. Please select a workspace.'));
        }
        
        $payslips = PaySlip::where('salary_month', $term)
            ->where('status', 2)
            ->where('workspace', getActiveWorkspace())
            ->get();
        $endOfMonthTerm = Carbon::parse($term . '-01')->endOfMonth()->format('Y-m-d');
        foreach ($payslips as $payslip) {
            $employeeId = $payslip->employee_id;

            if (!$employeeId) {
                return redirect()->back()->with('error', 'Employee ID is missing.');
            }

            $employee = Employee::find($employeeId);
            if (!$employee) {
                return redirect()->back()->with('error', 'Employee not found.');
            }


            $payroll = Payroll::where('employee_id', $employeeId)
                ->where('term', $term)
                ->first();

            $basicSalaryData = BasicSalary::where('employee_id', $employeeId)->where('term', $term)->first();
            if ($basicSalaryData && $basicSalaryData->hourly_paid == 1) {
                $basicSalaryHour = BasicSalaryHour::where('employee_id', $employeeId)->where('term', $term)->first();
                if ($basicSalaryHour) {
                    $basicSalaryNormal = ($basicSalaryData->hourly_rate * $basicSalaryHour->normal_hours);
                    $taxYear = TaxYear::resolveForTerm($term);
                    $otMultiplier = $taxYear ? $taxYear->ot_multiplier : 1.5;
                    $basicSalaryOT = ($basicSalaryData->hourly_rate * $basicSalaryHour->ot_hours * $otMultiplier);
                    $basicSalary = round(($basicSalaryNormal + $basicSalaryOT), 2);
                } else {
                    $basicSalary = 0;
                }
            } else {
                $basicSalary = $basicSalaryData ? $basicSalaryData->fixed_salary : 0;
            }


            //regular inputs
            $regularIncomeData = $this->calculateRegularIncomeItems($employeeId, $term);
            $regularDeductionData = $this->calculateRegularDeductionItems($employeeId, $term);

            $totalRegularInputIncome = $regularIncomeData['totalRegularInputIncome'];
            $withoutTaxRegularInputIncome = $regularIncomeData['withoutTaxRegularInputIncome'];
            $totalRegularInputAllowance = $regularIncomeData['totalRegularInputAllowance'];
            $totalRegularInputDeduction = $regularDeductionData['totalRegularInputDeduction'];

            // payslip inputs
            $incomeData = $this->calculateIncomeItems($employeeId, $term);
            $allowanceData = $this->calculateAllowanceItems($employeeId, $term);
            $deductionData = $this->calculateDeductionItems($employeeId, $term);
            $benefitsData = $this->calculateBenefitItems($employeeId, $term);

            $totalIncome    = $incomeData['totalIncome'];
            $totalAllowance = $allowanceData['totalAllowance'] + $regularDeductionData['additionalIncome']
                + $regularIncomeData['totalRegularInputAllowance'] + $benefitsData['benefitAllowance']
                + $regularIncomeData['withoutTaxRegularInputIncome'];
            $totalDeduction = $deductionData['totalDeduction'];
            $totalBenefit   = $benefitsData['totalBenefit'];

            // Get employee leave data
            $leaveData = $this->getEmployeeLeaveData($employeeId, $endOfMonthTerm);
            $totalUnpaidLeaveDays = array_sum(array_column($leaveData, 'unpaid_leave'));
            $unpaidLeaveDeduction = 0;
            if ($totalUnpaidLeaveDays > 0) {
                $dailySalary = $basicSalary / 30;
                $unpaidLeaveDeduction = $dailySalary * $totalUnpaidLeaveDays;
            }
            // Calculate deductions
            $company_settings = getCompanyAllSetting();
            $taxableIncome = $basicSalary + $totalIncome + $totalAllowance + $totalBenefit + $totalRegularInputIncome - $withoutTaxRegularInputIncome;

            if ($payslip->tax_year_id !== null) {
                // Use frozen tax values from finalized payslip
                $uif = (float) $payslip->uif_amount;
                $sdl = (float) $payslip->sdl_amount;
                $payTax = (float) $payslip->paye_amount;
                // Use frozen unpaid leave deduction if available
                if ($payslip->unpaid_leave_deduction !== null) {
                    $unpaidLeaveDeduction = (float) $payslip->unpaid_leave_deduction;
                }
            } else {
                $taxYear = TaxYear::resolveForTerm($term);
                $uifRate = $taxYear ? $taxYear->uif_rate : 0.01;
                $uifCeiling = $taxYear ? $taxYear->uif_ceiling : 177.12;
                $sdlRate = $taxYear ? $taxYear->sdl_rate : 0.01;

                $uif = ($basicSalary + $totalIncome - $totalDeduction) * $uifRate;
                if ($uif > $uifCeiling) {
                    $uif = $uifCeiling;
                }
                $sdl = 0;
                if (!empty($company_settings['is_sdl_calculate']) && $company_settings['is_sdl_calculate'] == 1) {
                    $sdl = ($payslip->basic_salary + $payslip->allowance + $payslip->other_payment) * $sdlRate;
                }
                $payTax = $this->calculateTax($employeeId, $taxableIncome, $term);
            }

            $totalDeduction += $uif + $sdl + $payTax + $totalRegularInputDeduction + $incomeData['additionalDeductions'] + $allowanceData['allowanceDeductions'] + $benefitsData['benefitDeduction'] - $unpaidLeaveDeduction;
            $netPay = $basicSalary + $totalIncome + $totalAllowance + $totalBenefit + $totalRegularInputIncome - $totalDeduction;

            $incomeItems = [
                ['name' => 'Basic Salary', 'amount' => $basicSalary]
            ];

            if (isset($regularIncomeData['items']['commission'])) {
                $incomeItems[] = [
                    'name' => 'Commission',
                    'amount' => $regularIncomeData['items']['commission']->commission_amount
                ];
            }
            if (isset($regularIncomeData['items']['income_policy'])) {
                $incomeItems[] = [
                    'name' => 'Loss of Income Policy Payout',
                    'amount' => $regularIncomeData['items']['income_policy']->payout_amount
                ];
            }
            if (isset($regularIncomeData['items']['accommodation_benefit'])) {
                $incomeItems[] = [
                    'name' => 'Accommodation Benefits',
                    'amount' => $regularIncomeData['items']['accommodation_benefit']->amount
                ];
            }
            // if (isset($regularIncomeData['items']['bursaries_scholarships'])) {
            //     $incomeItems[] = [
            //         'name' => 'Bursaries Scholarships - Taxable Portion',
            //         'amount' => $regularIncomeData['items']['bursaries_scholarships']->taxable_portion
            //     ];
            //     $incomeItems[] = [
            //         'name' => 'Bursaries Scholarships - Exempt Portion',
            //         'amount' => $regularIncomeData['items']['bursaries_scholarships']->exempt_portion
            //     ];
            // }
            // if (isset($regularIncomeData['items']['companyCar'])) {
            //     $incomeItems[] = [
            //         'name' => 'Company Car',
            //         'amount' => $regularIncomeData['items']['companyCar']->taxable_value
            //     ];
            // }
            if (isset($regularIncomeData['items']['companyCarUnderOperating'])) {
                $incomeItems[] = [
                    'name' => 'Company Car Under Operating Lease',
                    'amount' => $regularIncomeData['items']['companyCarUnderOperating']->taxable_value
                ];
            }
            if (isset($regularIncomeData['items']['savings_deduction'])) {
                $incomeItems[] = [
                    'name' => 'Savings',
                    'amount' => $regularIncomeData['items']['savings_deduction']->regular_deduction
                ];
            }

            // Add other income items
            if (isset($incomeData['items']['annual_bonus'])) {
                $incomeItems[] = [
                    'name' => 'Annual Bonus',
                    'amount' => $incomeData['items']['annual_bonus']->bonus_amount
                ];
            }

            if (isset($incomeData['items']['annual_payment'])) {
                $incomeItems[] = [
                    'name' => 'Annual Payment',
                    'amount' => $incomeData['items']['annual_payment']->annual_amount
                ];
            }

            if (isset($incomeData['items']['extra_pay'])) {
                $incomeItems[] = [
                    'name' => 'Extra Pay',
                    'amount' => $incomeData['items']['extra_pay']->amount
                ];
            }

            if (isset($incomeData['items']['once_off_commission'])) {
                $incomeItems[] = [
                    'name' => 'Once-off Commission',
                    'amount' => $incomeData['items']['once_off_commission']->commission_amount
                ];
            }

            if (isset($incomeData['items']['restraints_of_trade'])) {
                $incomeItems[] = [
                    'name' => 'Restraint of Trade',
                    'amount' => $incomeData['items']['restraints_of_trade']->amount
                ];
            }
            if (isset($incomeData['items']['arbitration_award'])) {
                $incomeItems[] = [
                    'name' => 'Arbitration Award',
                    'amount' => $incomeData['items']['arbitration_award']->directive_income_amount
                ];
            }
            if (isset($incomeData['items']['dividends_subject'])) {
                $incomeItems[] = [
                    'name' => 'Dividends Subject',
                    'amount' => $incomeData['items']['dividends_subject']->directive_income_amount
                ];
            }

            // Prepare allowance items array
            $allowanceItems = [];
            if (isset($regularIncomeData['items']['travel_allowance'])) {
                $allowanceItems[] = [
                    'name' => 'Travel Allowance',
                    'amount' => $regularIncomeData['items']['travel_allowance']->fixed_amount
                ];
            }
            if (isset($regularIncomeData['items']['employer_loan'])) {
                $allowanceItems[] = [
                    'name' => 'Employer Loan',
                    'amount' => $regularIncomeData['items']['employer_loan']->regular_repayment
                ];
            }
            if (isset($allowanceData['items']['broad_based_employee'])) {
                $allowanceItems[] = [
                    'name' => 'Broad Based Employee Share Plan',
                    'amount' => $allowanceData['items']['broad_based_employee']->amount
                ];
            }

            if (isset($allowanceData['items']['computer_allowance'])) {
                $allowanceItems[] = [
                    'name' => 'Computer Allowance',
                    'amount' => $allowanceData['items']['computer_allowance']->computer_allowance
                ];
            }
            if (isset($allowanceData['items']['expense_claim'])) {
                $allowanceItems[] = [
                    'name' => 'Expense Claim',
                    'amount' => $allowanceData['items']['expense_claim']->amount
                ];
            }

            if (isset($allowanceData['items']['phone_allowance'])) {
                $allowanceItems[] = [
                    'name' => 'Phone Allowance',
                    'amount' => $allowanceData['items']['phone_allowance']->phone_allowance_amount
                ];
            }
            if (isset($allowanceData['items']['relocation_allowance'])) {
                $allowanceItems[] = [
                    'name' => 'Relocation Allowance - Taxable',
                    'amount' => $allowanceData['items']['relocation_allowance']->taxable_allowance
                ];
                $allowanceItems[] = [
                    'name' => 'Relocation Allowance - Non-Taxable',
                    'amount' => $allowanceData['items']['relocation_allowance']->non_taxable_allowance
                ];
            }
            if (isset($allowanceData['items']['allowance_international'])) {
                $allowanceItems[] = [
                    'name' => 'Subsistence Allowance International',
                    'amount' => $allowanceData['items']['allowance_international']->paid_to_employee
                ];
            }
            if (isset($allowanceData['items']['subsistence_allowance'])) {
                $allowanceItems[] = [
                    'name' => 'Subsistence Allowance Local',
                    'amount' => $allowanceData['items']['subsistence_allowance']->full_amount_paid
                ];
            }
            if (isset($allowanceData['items']['tool_allowance'])) {
                $allowanceItems[] = [
                    'name' => 'Tool Allowance',
                    'amount' => $allowanceData['items']['tool_allowance']->amount
                ];
            }
            if (isset($allowanceData['items']['uniform_allowance'])) {
                $allowanceItems[] = [
                    'name' => 'Uniform Allowance',
                    'amount' => $allowanceData['items']['uniform_allowance']->amount
                ];
            }
            if (isset($allowanceData['items']['equity_instruments'])) {
                $allowanceItems[] = [
                    'name' => 'Gain from Vesting Equity',
                    'amount' => $allowanceData['items']['equity_instruments']->directive_income_amount
                ];
            }
            if (isset($allowanceData['items']['taxDirectiveEntry'])) {
                $allowanceItems[] = [
                    'name' => 'Directive Income Amount',
                    'amount' => $allowanceData['items']['taxDirectiveEntry']->directive_income_amount
                ];
            }
            if (isset($benefitsData['items']['ters_payout'])) {
                $allowanceItems[] = [
                    'name' => 'TERS Payout',
                    'amount' => $benefitsData['items']['ters_payout']->amount
                ];
            }
            if (isset($regularDeductionData['items']['medical_aid'])) {
                $allowanceItems[] = [
                    'name' => 'Medical Aid Benefit Paid Out',
                    'amount' => $regularDeductionData['items']['medical_aid']->employer_contribution
                ];
            }
            if (isset($allowanceData['items']['bursaries_scholarships'])) {
                if ($allowanceData['items']['bursaries_scholarships']->employee_handles_payment == 1) {
                    $allowanceItems[] = [
                        'name' => 'Bursaries And Scholarships - taxable (Regular)',
                        'amount' => $allowanceData['items']['bursaries_scholarships']->taxable_portion
                    ];
                    $allowanceItems[] = [
                        'name' => 'Bursaries And Scholarships - exempt (Regular)',
                        'amount' => $allowanceData['items']['bursaries_scholarships']->exempt_portion
                    ];
                }
            }
            if (isset($allowanceData['items']['bursary'])) {
                if ($allowanceData['items']['bursary']->employee_handles_payment == 1) {
                    $allowanceItems[] = [
                        'name' => 'Bursaries And Scholarships - taxable',
                        'amount' => $allowanceData['items']['bursary']->taxable_portion
                    ];
                    $allowanceItems[] = [
                        'name' => 'Bursaries And Scholarships - exempt',
                        'amount' => $allowanceData['items']['bursary']->exempt_portion
                    ];
                }
            }
            // Prepare deduction items array
            $deductionItems = [
                ['name' => 'UIF', 'amount' => $uif],
                ['name' => 'Vat (PAYE)', 'amount' => $payTax],
                ['name' => 'SDL', 'amount' => $sdl]
            ];

            if ($unpaidLeaveDeduction > 0) {
                $deductionItems[] = [
                    'name' => 'Loss of Pay (Unpaid Leave)',
                    'amount' => $unpaidLeaveDeduction
                ];
            }
            // Add other deductions if they exist in your data
            if (isset($incomeData['items']['arbitration_award'])) {
                $deductionItems[] = [
                    'name' => 'Tax on Arbitration Award',
                    'amount' => $incomeData['items']['arbitration_award']->tax_to_deduct
                ];
            }
            if (isset($incomeData['items']['dividends_subject'])) {
                $deductionItems[] = [
                    'name' => 'Tax on Dividends Restricted Equity',
                    'amount' => $incomeData['items']['dividends_subject']->tax_to_deduct
                ];
            }
            if (isset($allowanceData['items']['equity_instruments'])) {
                $deductionItems[] = [
                    'name' => 'Tax on Gain from Vesting Equity',
                    'amount' => $allowanceData['items']['equity_instruments']->tax_deduct_amount
                ];
            }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['garnishee'])) {
                $deductionItems[] = [
                    'name' => 'Garnishee',
                    'amount' => $regularDeductionData['items']['garnishee']->installment
                ];
            }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['incomeProtection'])) {
                $deductionItems[] = [
                    'name' => 'Income Protection',
                    'amount' => $regularDeductionData['items']['incomeProtection']->amount_deducted
                ];
            }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['maintenance_order'])) {
                $deductionItems[] = [
                    'name' => 'Maintenance Order',
                    'amount' => $regularDeductionData['items']['maintenance_order']->installment
                ];
            }
            
            // if ($regularDeductionData['items'] && isset($regularDeductionData['items']['medical_aid'])) {
            //     $deductionItems[] = [
            //         'name' => 'Medical Aid',
            //         'amount' => $regularDeductionData['items']['medical_aid']->medical_aid
            //     ];
            // }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['pension_fund'])) {
                $deductionItems[] = [
                    'name' => 'Pension Fund',
                    'amount' => $regularDeductionData['items']['pension_fund']->fixed_contribution_employee
                ];
            }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['provident_fund'])) {
                $deductionItems[] = [
                    'name' => 'Provident Fund',
                    'amount' => $regularDeductionData['items']['provident_fund']->fixed_contribution_employee
                ];
            }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['retirement_annuity'])) {
                $deductionItems[] = [
                    'name' => 'Retirement Annuity',
                    'amount' => $regularDeductionData['items']['retirement_annuity']->amount
                ];
            }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['union_membership'])) {
                $deductionItems[] = [
                    'name' => 'Union Membership',
                    'amount' => $regularDeductionData['items']['union_membership']->amount_per_period
                ];
            }
            if ($regularDeductionData['items'] && isset($regularDeductionData['items']['tax_over_deduction'])) {
                $deductionItems[] = [
                    'name' => 'Voluntary Tax Over Deduction',
                    'amount' => $regularDeductionData['items']['tax_over_deduction']->per_period
                ];
            }
            if ($deductionData['items'] && isset($deductionData['items']['medical_aid'])) {
                $deductionItems[] = [
                    'name' => 'Medical Aid - Employee',
                    'amount' => $deductionData['items']['medical_aid']->amount
                ];
            }
            if ($deductionData['items'] && isset($deductionData['items']['donation'])) {
                $deductionItems[] = [
                    'name' => 'Donations',
                    'amount' => $deductionData['items']['donation']->amount
                ];
            }
            if ($deductionData['items'] && isset($deductionData['items']['staff_purchase'])) {
                $deductionItems[] = [
                    'name' => 'Staff Purchases',
                    'amount' => $deductionData['items']['staff_purchase']->amount
                ];
            }
            if ($deductionData['items'] && isset($deductionData['items']['repayment'])) {
                $deductionItems[] = [
                    'name' => 'Repayment of Loan',
                    'amount' => $deductionData['items']['repayment']->amount
                ];
            }
            if ($deductionData && isset($deductionData['items']['taxDirectiveEntry'])) {
                $deductionItems[] = [
                    'name' => 'Directive Deduction Amount',
                    'amount' => $deductionData['items']['taxDirectiveEntry']->amount_of_tax_to_deduct
                ];
            }
            if (isset($benefitsData['items']['termination_lump_sum'])) {
                $deductionItems[] = [
                    'name' => 'Tax on Gratuities / Severance Benefits',
                    'amount' => $benefitsData['items']['termination_lump_sum']->amount_of_tax_to_deduct
                ];
            }
            // Prepare benefit items array
            $benefitItems = [];

            if (
                isset($regularIncomeData['items']['employer_loan']) &&
                (int) ($regularIncomeData['items']['employer_loan']->calculate_interest_benefit ?? 0) === 1
            ) {
                $interestBenefitAmount = (float) ($regularIncomeData['items']['employer_loan']->calculated_interest_benefit_amount ?? 0);
                if ($interestBenefitAmount > 0) {
                    $benefitItems[] = [
                        'name' => 'Interest Benefit',
                        'amount' => $interestBenefitAmount,
                    ];
                }
            }

            if (isset($benefitsData['items']['medical_aid'])) {
                $benefitItems[] = [
                    'name' => 'Medical Aid Benefit',
                    'amount' => $benefitsData['items']['medical_aid']->amount
                ];
            }
            if (isset($benefitsData['items']['bursary'])) {
                $benefitItems[] = [
                    'name' => 'Bursaries And Scholarships - taxable',
                    'amount' => $benefitsData['items']['bursary']->taxable_portion
                ];
                $benefitItems[] = [
                    'name' => 'Bursaries And Scholarships - exempt',
                    'amount' => $benefitsData['items']['bursary']->exempt_portion
                ];
            }
            if (isset($benefitsData['items']['benefit'])) {
                $benefitItems[] = [
                    'name' => 'Employees Debt Benefit',
                    'amount' => $benefitsData['items']['benefit']->amount
                ];
            }
            if (isset($benefitsData['items']['medical_cost'])) {
                $benefitItems[] = [
                    'name' => 'Medical Costs',
                    'amount' => $benefitsData['items']['medical_cost']->amount
                ];
            }
            if (isset($benefitsData['items']['covid'])) {
                $benefitItems[] = [
                    'name' => 'COVID-19 Disaster Relief',
                    'amount' => $benefitsData['items']['covid']->amount
                ];
            }
            if (isset($benefitsData['items']['long_service_award'])) {
                $benefitItems[] = [
                    'name' => 'Long Service Award - Cash',
                    'amount' => $benefitsData['items']['long_service_award']->long_cash_portion
                ];
                $benefitItems[] = [
                    'name' => 'Long Service Award - Non-Cash',
                    'amount' => $benefitsData['items']['long_service_award']->non_cash_portion
                ];
            }

            if (isset($benefitsData['items']['termination_lump_sum'])) {
                $benefitItems[] = [
                    'name' => 'Gratuities / Severance Benefits',
                    'amount' => $benefitsData['items']['termination_lump_sum']->directive_income_amount
                ];
            }
            if (isset($regularIncomeData['items']['companyCar'])) {
                $companyCar = $regularIncomeData['items']['companyCar'];
                $amount = ($companyCar->deemed_value * intval($companyCar->taxableType->percentage) / 100) ?? 0;
                $benefitItems[] = [
                    'name' => 'Company Car',
                    'amount' => $amount
                ];
            }
            // if (isset($regularIncomeData['items']['accommodation_benefit'])) {
            //     $benefitItems[] = [
            //         'name' => 'Accommodation Benefit',
            //         'amount' => $regularIncomeData['items']['accommodation_benefit']->amount
            //     ];
            // }

            // Prepare tax exemption items array
            $taxExemptionItems = [];

            // Add tax exemptions if they exist in your data
            // For example:
            // if (isset($regularIncomeData['items']['foreign_service_income'])) {
            //     $taxExemptionItems[] = [
            //         'name' => 'Foreign Service Income',
            //         'amount' => $regularIncomeData['items']['foreign_service_income']->amount
            //     ];
            // }

            // Prepare tax credit items array
            $taxCreditItems = [];

            // Add tax credits if they exist in your data
            if (isset($regularDeductionData['items']['medical_aid'])) {
                $taxCreditItems[] = [
                    'name' => 'Medical Aid Tax Credit',
                    'amount' => $regularDeductionData['items']['medical_aid']->tax_credit
                ];
            }

            // If no real tax exemption or credit data exists, you might want to remove these sections
            // or add placeholder data until you have real data

            // If no tax exemption items found, add a placeholder or leave empty
            // if (empty($taxExemptionItems)) {
            //     $taxExemptionItems[] = [
            //         'name' => 'Foreign Service Income',
            //         'amount' => 0
            //     ];
            // }

            // If no tax credit items found, add a placeholder or leave empty
            if (empty($taxCreditItems)) {
                $taxCreditItems[] = [
                    'name' => 'Medical Aid Tax Credit',
                    'amount' => 0
                ];
            }

            // Get employee leave data (you'll need to implement this)

            //settings
            $settings = getCompanyAllSetting();
            $sdl_number = isset($settings['sdl_number']) ? $settings['sdl_number'] : '';
            $tax_number = isset($settings['tax_number']) ? $settings['tax_number'] : '';
            $uif_number = isset($settings['uif_number']) ? $settings['uif_number'] : '';
            $company_name = isset($settings['company_name']) ? $settings['company_name'] : '';
            $company_address = isset($settings['company_address']) ? $settings['company_address'] : '';
            $company_city = isset($settings['company_city']) ? $settings['company_city'] : '';
            $company_state = isset($settings['company_state']) ? $settings['company_state'] : '';
            $company_country = isset($settings['company_country']) ? $settings['company_country'] : '';
            $company_zipcode = isset($settings['company_zipcode']) ? $settings['company_zipcode'] : '';
            $logoLightUrl = getLogoUrl($settings['logo_light'] ?? null, 'light');

            $data = [
                'employee' => [
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'period' => date('Y-m-01') . ' to ' . date('Y-m-t'),
                    'employment_date' => $employee->employment_date ?? 'N/A',
                    'address' => [
                        'street' => '31 Looper Street Erand Gardens' ?? 'N/A',
                        'city' => 'Midrand' ?? 'N/A',
                        'state' => 'Johannesburg-1685,' ?? 'N/A',
                        'postal_code' => 'South Africa' ?? 'N/A'
                    ]
                ],
                'income' => [
                    'total' => $basicSalary + $totalIncome + $totalRegularInputIncome  + $withoutTaxRegularInputIncome,
                    'basic_salary' => $basicSalary,
                    'items' => $incomeItems
                ],
                'allowance' => [
                    'total' => $totalAllowance,
                    'items' => $allowanceItems
                ],
                'deduction' => [
                    'total' => $totalDeduction,
                    'items' => $deductionItems
                ],
                'benefit' => [
                    'total' => $totalBenefit,
                    'items' => $benefitItems
                ],
                'tax_exemption' => [
                    'total' => array_sum(array_column($taxExemptionItems, 'amount')),
                    'items' => $taxExemptionItems
                ],
                'tax_credit' => [
                    'total' => array_sum(array_column($taxCreditItems, 'amount')),
                    'items' => $taxCreditItems
                ],
                'leave' => isset($leaveData) ? array_values(array_filter($leaveData, fn($item) => !empty($item['show_on_payslip']))) : [],
                'netPay' => $netPay,
                'company_name' => $company_name,
                'sdl_number' => $sdl_number,
                'tax_number' => $tax_number,
                'uif_number' => $uif_number,
                'logo_light_url' => $logoLightUrl,
                'company_address' => $company_address,
                'company_city' => $company_city,
                'company_state' => $company_state,
                'company_country' => $company_country,
                'company_zipcode' => $company_zipcode,
                'tax_year_label' => ($ty = TaxYear::resolveForTerm($term)) ? 'SARS Tax Year ' . $ty->label : null,
            ];
            $payslip->preview_payload = $data;
        }
        $pdf = Pdf::loadView('hrm.payslip.payslipPreview', compact('payslips'));
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isRemoteEnabled' => true,
        ]);

        return $pdf->stream('payslips-' . $term . '.pdf');
    }
    public function payrun_pdf($term, $type)
    {

        $payslips = Payslip::where('workspace', getActiveWorkspace())->pluck('id')->toArray();

        $payruns = Payrun::whereIn('payslip_id', $payslips)
            ->where('term', $term)
            ->when($type !== 'all', function ($query) use ($type) {
                return $query->where('payment_method', $type);
            })
            ->get();
        foreach ($payruns as $payrun) {
            $payslip = PaySlip::find($payrun->payslip_id);
            $payrun->employee_details = Employee::find($payslip->employee_id);
            $payrun->nett_pay = $payslip->net_payble;
        }
        $settings = getCompanyAllSetting();

        // Get logo URL from your function
        $logoUrl = getFile(sidebarLogo());
        // $localLogoPath = null;

        // if (filter_var($logoUrl, FILTER_VALIDATE_URL)) {
        //     try {
        //         // Download the image to local storage
        //         $logoFileName = 'temp_logo.png';
        //         $localPath = storage_path('app/public/temp/' . $logoFileName);
        //         file_put_contents($localPath, file_get_contents($logoUrl));

        //         $localLogoPath = asset('storage/temp/' . $logoFileName);
        //     } catch (\Exception $e) {
        //         $localLogoPath = null;
        //     }
        // }
        // return $localLogoPath;
        $settings['company_name'] = isset($settings['company_name']) ? $settings['company_name'] : '';
        $logo = isset($settings['logo_dark']) ? (checkFile($settings['logo_dark']) ? $settings['logo_dark'] : 'uploads/logo/logo_dark.png') : 'uploads/logo/logo_dark.png';
        $pdf = Pdf::loadView('hrm.payrun.payrunPdf', compact('payruns', 'term', 'type', 'settings', 'logoUrl', 'logo'));
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isRemoteEnabled' => true,
        ]);
        return $pdf->stream("payrun_{$term}_{$type}.pdf");
    }
    public function bulkUnFinalisation($term)
    {
        DB::beginTransaction();
        try {
            $payslips = PaySlip::where('salary_month', $term)->where('status', 1)->get();
            foreach ($payslips as $payslip) {
                $payslip->status = 0;
                $payslip->save();
            }
            DB::commit();
            return redirect()->route('payrun.index')->with('success', 'Unfinalised successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Check if billing allows payslip generation
     * 
     * @return bool|string True if allowed, error message string if blocked
     */
    protected function checkBillingAccess()
    {
        // Get the company user (creator/owner)
        $user = \App\Models\User::find(creatorId());
        
        if (!$user) {
            return true; // No user found, allow access
        }

        $billingService = app(\App\Services\BillingService::class);
        $billingStatus = $billingService->getBillingStatus($user);

        // If billing is not enabled, allow
        if (!$billingStatus['billing_enabled']) {
            return true;
        }

        // If user can generate payslips, allow
        if ($billingStatus['can_generate_payslips']) {
            return true;
        }

        // Return appropriate error message based on block reason
        $blockReason = $billingStatus['payslip_block_reason'] ?? 'unknown';
        
        switch ($blockReason) {
            case 'trial_expired':
                return __('Your free trial has expired. Please upgrade to a paid plan to continue generating payslips.');
            
            case 'overdue_invoices':
                return __('You have overdue invoices. Please settle your outstanding balance to continue generating payslips.');
            
            case 'account_suspended':
                return __('Your account has been suspended. Please contact support for assistance.');
            
            default:
                return __('Payslip generation is currently unavailable. Please check your billing status.');
        }
    }
}
