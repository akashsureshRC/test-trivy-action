<?php

namespace App\Http\Controllers\Hrm;

use App\Models\EmailTemplate;
use App\Models\Hrm\TravelAllowance;
use App\Models\User;
use App\Models\WorkSpace;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
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
use App\Models\Hrm\Employee;
use App\Services\PayrollHelperService;
use App\Services\LeaveAccrualService;
use App\Services\TaxCalculationService;
use App\Models\Hrm\EmployerLoan;
use App\Models\Hrm\EquityInstrument;
use App\Models\Hrm\ExpenseClaim;
use App\Models\Hrm\ExtraPay;
use App\Models\Hrm\Garnishee;
use App\Models\Hrm\IncomePolicy;
use App\Models\Hrm\IncomeProtection;
use App\Models\Hrm\LongServiceAward;
use App\Models\Hrm\MaintenanceOrder;
use App\Models\Hrm\MedicalAid;
use App\Models\Hrm\MedicalCost;
use App\Models\Hrm\OnceOffCommission;
use App\Models\Hrm\OtherPayment;
use App\Models\Hrm\Overtime;
use App\Models\Hrm\PaySlip;
use App\Models\Hrm\Payroll;
use App\Models\Hrm\PayslipCommission;
use App\Models\Hrm\PensionFund;
use App\Models\Hrm\PhoneAllowance;
use App\Models\Hrm\ProvidentFund;
use App\Models\Hrm\ProvidentFundPayroll;
use App\Models\Hrm\PayFrequency;
use App\Models\Hrm\RetirementAnnuityFundPayroll;
use App\Models\Hrm\RelocationAllowance;
use App\Models\Hrm\Repayments;
use App\Models\Hrm\RestraintOfTrade;
use App\Models\Hrm\RetirementAnnuitie;
use App\Models\Hrm\SaturationDeduction;
use App\Models\Hrm\SavingsDeduction;
use App\Models\Hrm\StaffPurchase;
use App\Models\Hrm\SubsistenceAllowance;
use App\Models\Hrm\TaxDirectiveEntry;
use App\Models\Hrm\TaxOverDeduction;
use App\Models\Hrm\TerminationLump;
use App\Models\Hrm\TersPayout;
use App\Models\Hrm\ToolAllowance;
use App\Models\Hrm\UniformAllowance;
use App\Models\Hrm\UnionMembershipFee;
use App\Events\Hrm\CreateMonthlyPayslip;
use App\Events\Hrm\CreatePaymentMonthlyPayslip;
use App\Events\Hrm\DestroyMonthlyPayslip;
use App\Events\Hrm\PayslipSend;
use App\Events\Hrm\UpdateMonthlyPayslip;
use App\Models\Hrm\LeaveRecord;
use App\Models\Hrm\EmployeeEntitlementPolicy;
use App\Models\TaxYear;

class PaySlipController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (Auth::user()->isAbleTo('setsalary pay slip manage') || !in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
            for ($i = 0; $i <= 10; $i++) {
                $data = date("Y", strtotime('-1 years' . " +$i years"));
                $year[$data] = $data;
            }


            for ($i = 0; $i <= 15; $i++) {
                $data = date('Y', strtotime('-5 years' . " +$i years"));
                $years[$data] = $data;
            }

            $month = [
                '01' => 'JAN',
                '02' => 'FEB',
                '03' => 'MAR',
                '04' => 'APR',
                '05' => 'MAY',
                '06' => 'JUN',
                '07' => 'JUL',
                '08' => 'AUG',
                '09' => 'SEP',
                '10' => 'OCT',
                '11' => 'NOV',
                '12' => 'DEC',
            ];
            return view('hrm.payslip.index', compact('month', 'year', 'years'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
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
        $validator = \Validator::make(
            $request->all(),
            [
                'month' => 'required',
                'year' => 'required',

            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        // Check if billing allows payslip generation
        $billingCheck = $this->checkBillingAccess();
        if ($billingCheck !== true) {
            return redirect()->back()->with('error', $billingCheck);
        }

        $month = $request->month;
        $year  = $request->year;

        // Ensure a locked tax year exists for this period
        if (!TaxYear::resolveForTerm($year . '-' . $month)) {
            return redirect()->route('payslip.index')->with('error', 'No Tax Year configuration found for ' . Carbon::parse($year . '-' . $month . '-01')->format('F Y') . '. Please contact customer support for assistance.');
        }

        $formate_month_year = $year . '-' . $month;
        $validatePaysilp    = PaySlip::where('salary_month', '=', $formate_month_year)->where('workspace', getActiveWorkspace())->pluck('employee_id');
        $payslip_employee   = Employee::where('workspace_id', getActiveWorkspace())->where('date_of_appointment', '<=', date($year . '-' . $month . '-t'))->count();

        if ($payslip_employee > count($validatePaysilp)) {

            $employees = Employee::where('workspace_id', getActiveWorkspace())->where('date_of_appointment', '<=', date($year . '-' . $month . '-t'))->whereNotIn('id', $validatePaysilp)->get();
            // Check if any employees don't have salary set via BasicSalary
            $employeesSalary = Employee::where('workspace_id', getActiveWorkspace())
                ->whereDoesntHave('basicSalary')
                ->first();
            if (!empty($employeesSalary)) {
                return redirect()->route('payslip.index')->with('error', __('Please set employee salary.'));
            }

            foreach ($employees as $employee) {

                $payslipEmployee                       = new PaySlip();
                $payslipEmployee->employee_id          = $employee->id;
                $basicSalary = $employee->basicSalary;
                $payslipEmployee->net_payble           = $basicSalary ? $basicSalary->fixed_salary : 0;
                $payslipEmployee->salary_month         = $formate_month_year;
                $payslipEmployee->status               = 0;
                $payslipEmployee->basic_salary         = $basicSalary ? $basicSalary->fixed_salary : 0;
                $payslipEmployee->allowance            = PayrollHelperService::allowance($employee->id);
                $payslipEmployee->commission           = PayrollHelperService::commission($employee->id);
                $payslipEmployee->loan                 = 0;
                $payslipEmployee->saturation_deduction = PayrollHelperService::saturation_deduction($employee->id);
                $payslipEmployee->other_payment        = PayrollHelperService::other_payment($employee->id);
                $payslipEmployee->overtime             = PayrollHelperService::overtime($employee->id);
                $payslipEmployee->company_contribution = PayrollHelperService::companycontribution($employee->id);
                $payslipEmployee->workspace            = getActiveWorkspace();
                $payslipEmployee->created_by           = creatorId();
                $payslip = PaySlip::where('employee_id', $payslipEmployee->employee_id)->where('salary_month', $formate_month_year)->where('workspace', getActiveWorkspace())->first();
                if (empty($payslip)) {
                    $payslipEmployee->save();
                }

                event(new CreateMonthlyPayslip($request, $payslipEmployee));
            }

            return redirect()->route('payslip.index')->with('success', __('Payslip successfully created.'));
        } else {
            return redirect()->route('payslip.index')->with('error', __('Payslip Already created.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return redirect()->back();
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
        if (!Auth::user()->isAbleTo('setsalary pay slip manage')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $payslip = PaySlip::where('id', $id)->where('workspace', getActiveWorkspace())->first();
        if (!$payslip) {
            return response()->json(['error' => __('Payslip not found.')], 404);
        }

        event(new DestroyMonthlyPayslip($payslip));
        $payslip->delete();

        return true;
    }

    public function search_json(Request $request)
    {
        $formate_month_year = $request->datePicker;
        $validatePaysilp    = PaySlip::where('salary_month', '=', $formate_month_year)->where('workspace', getActiveWorkspace())->get()->toarray();

        $data = [];
        if (empty($validatePaysilp)) {
            return $data;
        } else {
            $paylip_employee = PaySlip::select(
                [
                    'employees.id',
                    'employees.employee_id',
                    DB::raw("CONCAT(employees.first_name, ' ', employees.last_name) as name"),
                    DB::raw("'Monthly' as payroll_type"),
                    'pay_slips.basic_salary',
                    'pay_slips.net_payble',
                    'pay_slips.id as pay_slip_id',
                    'pay_slips.status',
                    DB::raw("employees.id as user_id"),
                ]
            )->leftjoin(
                'employees',
                function ($join) use ($formate_month_year) {
                    $join->on('employees.id', '=', 'pay_slips.employee_id');
                    $join->on('pay_slips.salary_month', '=', \DB::raw("'" . $formate_month_year . "'"));
                }
            )->where('employees.workspace_id', getActiveWorkspace())->get();

            foreach ($paylip_employee as $employee) {
                if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
                    if (Auth::user()->id == $employee->user_id) {
                        $tmp   = [];
                        $tmp[] = $employee->id;
                        $tmp[] = PayrollHelperService::employeeIdFormat($employee->employee_id);
                        $tmp[] = $employee->name;
                        $tmp[] = $employee->payroll_type;
                        $tmp[] = !empty($employee->basic_salary) ? currencyFormat($employee->basic_salary) : '-';
                        $tmp[] = !empty($employee->net_payble) ? currencyFormat($employee->net_payble) : '-';
                        if ($employee->status == 1) {
                            $tmp[] = 'paid';
                        } else {
                            $tmp[] = 'unpaid';
                        }
                        $tmp[]  = !empty($employee->pay_slip_id) ? $employee->pay_slip_id : 0;
                        $tmp['url']  = route('employee.show', Crypt::encrypt($employee->user_id));
                        $data[] = $tmp;
                        return $data;
                    }
                } else {
                    $tmp   = [];
                    $tmp[] = $employee->id;
                    $tmp[] = PayrollHelperService::employeeIdFormat($employee->employee_id);
                    $tmp[] = $employee->name;
                    $tmp[] = $employee->payroll_type;
                    $tmp[] = !empty($employee->basic_salary) ? currencyFormat($employee->basic_salary) : '-';
                    $tmp[] = !empty($employee->net_payble) ? currencyFormat($employee->net_payble) : '-';
                    if ($employee->status == 1) {
                        $tmp[] = 'Paid';
                    } else {
                        $tmp[] = 'UnPaid';
                    }
                    $tmp[]  = !empty($employee->pay_slip_id) ? $employee->pay_slip_id : 0;
                    $tmp['url']  = route('employee.show', Crypt::encrypt($employee->user_id));
                    $data[] = $tmp;
                }
            }
            return $data;
        }
    }

    public function paysalary($id, $date)
    {
        if (!Auth::user()->isAbleTo('setsalary pay slip manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        // Verify employee belongs to current workspace
        $get_employee = Employee::where('id', $id)->where('workspace_id', getActiveWorkspace())->first();
        if (!$get_employee) {
            return redirect()->route('payslip.index')->with('error', __('Employee not found.'));
        }

        $employeePayslip = PaySlip::where('employee_id', '=', $id)->where('workspace', getActiveWorkspace())->where('salary_month', '=', $date)->first();
        if (moduleIsActive('Account')) {
            // Account module integration - account_type not used in Employee
            $get_account = null;
            if (isset($get_account) && $get_account->opening_balance <= 0) {
                return redirect()->route('payslip.index')->with('error', __('Account balance is low.'));
            }
            $opening_balance = !empty($get_account->opening_balance) ? $get_account->opening_balance : 0;
            $net_salary = !empty($employeePayslip->net_payble) ? $employeePayslip->net_payble : 0;
        }
        if (!empty($employeePayslip)) {
            $employeePayslip->status = 1;
            $employeePayslip->save();

            if (moduleIsActive('Account')) {
                $total_balance = $opening_balance - $net_salary;
                $get_account->opening_balance = $total_balance;
                $get_account->save();
            }

            event(new CreatePaymentMonthlyPayslip($employeePayslip));
            return redirect()->route('payslip.index')->with('success', __('Payslip Payment successfully.'));
        } else {
            return redirect()->route('payslip.index')->with('error', __('Payslip Payment failed.'));
        }
    }

    public function pdf($id, $month)
    {
        // Authorization: admin or the employee themselves
        if (!Auth::user()->isAbleTo('setsalary pay slip manage') && in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $payslip  = PaySlip::where('employee_id', $id)->where('salary_month', $month)->where('workspace', getActiveWorkspace())->first();
        if (!$payslip) {
            return redirect()->back()->with('error', __('Payslip not found.'));
        }

        // Non-admin users can only view their own payslip
        if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
            $currentEmployee = Employee::where('user_id', Auth::user()->id)->where('workspace_id', getActiveWorkspace())->first();
            if (!$currentEmployee || $currentEmployee->id != $payslip->employee_id) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }

        return $this->preview($payslip->employee_id, $payslip->salary_month);
    }

    public function payslipPdf($id)
    {
        $payslipId = Crypt::decrypt($id);

        $payslip  = PaySlip::where('id', $payslipId)->where('workspace', getActiveWorkspace())->first();
        if (!empty($payslip)) {
            // Non-admin users can only view their own payslip
            if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
                $currentEmployee = Employee::where('user_id', Auth::user()->id)->where('workspace_id', getActiveWorkspace())->first();
                if (!$currentEmployee || $currentEmployee->id != $payslip->employee_id) {
                    return redirect()->back()->with('error', __('Permission denied.'));
                }
            }

            return $this->preview($payslip->employee_id, $payslip->salary_month);
        } else {
            return redirect()->route('payslip.index')->with('error', __('Payslip not found!.'));
        }
    }

    public function send($id, $month)
    {
        if (!Auth::user()->isAbleTo('setsalary pay slip manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $payslip  = PaySlip::where('employee_id', $id)->where('salary_month', $month)->where('workspace', getActiveWorkspace())->first();
        if (!$payslip) {
            return redirect()->back()->with('error', __('Payslip not found.'));
        }
        $employee = Employee::find($payslip->employee_id);
        $User     = User::find($employee->id); // Employee is the user for ESS

        $payslip->name  = $employee->first_name . ' ' . $employee->last_name;
        $payslip->email = $employee->email;

        $payslipId    = Crypt::encrypt($payslip->id);
        $payslip->url = route('payslip.payslipPdf', $payslipId);

        event(new PayslipSend($id, $month, $payslip));
        $company_settings = getCompanyAllSetting();
        if (!empty($company_settings['New Payroll']) && $company_settings['New Payroll']  == true) {
            $uArr = [
                'payslip_email' => $payslip->email,
                'name'  => $payslip->name,
                'url' => $payslip->url,
                'salary_month' => $payslip->salary_month,
            ];
            try {
                $resp = EmailTemplate::sendEmailTemplate('New Payroll', [$payslip->email], $uArr, creatorId(), getActiveWorkspace());
            } catch (\Exception $e) {
                $resp['error'] = $e->getMessage();
            }
            return redirect()->back()->with('success', __('Payslip successfully sent.')  . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        }

        return redirect()->back()->with('error', __('Please enable email notification! "New Payroll"'));
    }

    public function editEmployee($paySlip)
    {
        if (!Auth::user()->isAbleTo('setsalary pay slip manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $payslip = PaySlip::where('id', $paySlip)->where('workspace', getActiveWorkspace())->first();
        if (!$payslip) {
            return redirect()->back()->with('error', __('Payslip not found.'));
        }

        return view('hrm.payslip.salaryEdit', compact('payslip'));
    }

    public function updateEmployee(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('setsalary pay slip manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $payslipEmployee                       = PaySlip::where('id', $request->payslip_id)->where('workspace', getActiveWorkspace())->first();
        if (!$payslipEmployee) {
            return redirect()->back()->with('error', __('Payslip not found.'));
        }

        if (isset($request->allowance) && !empty($request->allowance)) {
            $allowances   = $request->allowance;
            $allowanceIds = $request->allowance_id;
            foreach ($allowances as $k => $allownace) {
                $allowanceData         = Allowance::find($allowanceIds[$k]);
                $allowanceData->amount = $allownace;
                $allowanceData->save();
            }
        }


        if (isset($request->commission) && !empty($request->commission)) {
            $commissions   = $request->commission;
            $commissionIds = $request->commission_id;
            foreach ($commissions as $k => $commission) {
                $commissionData         = Commission::find($commissionIds[$k]);
                $commissionData->amount = $commission;
                $commissionData->save();
            }
        }


        if (isset($request->saturation_deductions) && !empty($request->saturation_deductions)) {
            $saturation_deductionss   = $request->saturation_deductions;
            $saturation_deductionsIds = $request->saturation_deductions_id;
            foreach ($saturation_deductionss as $k => $saturation_deductions) {

                $saturation_deductionsData         = SaturationDeduction::find($saturation_deductionsIds[$k]);
                $saturation_deductionsData->amount = $saturation_deductions;
                $saturation_deductionsData->save();
            }
        }


        if (isset($request->other_payment) && !empty($request->other_payment)) {
            $other_payments   = $request->other_payment;
            $other_paymentIds = $request->other_payment_id;
            foreach ($other_payments as $k => $other_payment) {
                $other_paymentData         = OtherPayment::find($other_paymentIds[$k]);
                $other_paymentData->amount = $other_payment;
                $other_paymentData->save();
            }
        }


        if (isset($request->rate) && !empty($request->rate)) {
            $rates   = $request->rate;
            $rateIds = $request->rate_id;
            $hourses = $request->hours;

            foreach ($rates as $k => $rate) {
                $overtime        = Overtime::find($rateIds[$k]);
                $overtime->rate  = $rate;
                $overtime->hours = $hourses[$k];
                $overtime->save();
            }
        }


        $payslipEmployee                       = PaySlip::find($request->payslip_id);
        $payslipEmployee->allowance            = PayrollHelperService::allowance($payslipEmployee->employee_id);
        $payslipEmployee->commission           = PayrollHelperService::commission($payslipEmployee->employee_id);
        $payslipEmployee->loan                 = 0;
        $payslipEmployee->saturation_deduction = PayrollHelperService::saturation_deduction($payslipEmployee->employee_id);
        $payslipEmployee->other_payment        = PayrollHelperService::other_payment($payslipEmployee->employee_id);
        $payslipEmployee->overtime             = PayrollHelperService::overtime($payslipEmployee->employee_id);
        $employeeProfile = Employee::find($payslipEmployee->employee_id);
        $basicSalary = $employeeProfile ? $employeeProfile->basicSalary : null;
        $payslipEmployee->net_payble           = $basicSalary ? $basicSalary->fixed_salary : 0;
        $payslipEmployee->save();
        event(new UpdateMonthlyPayslip($request, $payslipEmployee));
        return redirect()->route('payslip.index')->with('success', __('Employee payroll successfully updated.'));
    }
    //preview
    public function preview($id, $term, $essWorkspaceId = null, $essCompanyUserId = null)
    {
        $employeeId = $id;

        if (!$employeeId) {
            return redirect()->back()->with('error', 'Employee ID is missing.');
        }

        // Workspace-scoped lookup (use ESS workspace if provided, otherwise active workspace)
        $workspaceId = $essWorkspaceId ?? getActiveWorkspace();
        $employee = Employee::where('id', $employeeId)->where('workspace_id', $workspaceId)->first();
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found.');
        }

        // Non-admin users can only preview their own payroll
        if (!$essWorkspaceId && !in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
            $currentEmployee = Employee::where('user_id', Auth::user()->id)->where('workspace_id', getActiveWorkspace())->first();
            if (!$currentEmployee || $currentEmployee->id != $employee->id) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }

        $payroll = Payroll::where('employee_id', $employeeId)->latest('id')->first();
        $payslip = PaySlip::where('employee_id', $employeeId)->latest('id')->first();

        // if ($payslip && $payslip->salary_month) {
        //     if($payslip->status == 0){
        //         $term = Carbon::parse($payslip->salary_month.'-01')->endOfMonth()->format('Y-m-d');
        //     }else{
        //         $term = Carbon::parse($payslip->salary_month.'-01')->addMonthsNoOverflow()->endOfMonth()->format('Y-m-d');
        //     }
        // }else{
        //     $term = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        // }


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
        $leaveData = $this->getEmployeeLeaveData($employeeId, $term);
        $totalUnpaidLeaveDays = array_sum(array_column($leaveData, 'unpaid_leave'));
        $unpaidLeaveDeduction = 0;
        if ($totalUnpaidLeaveDays > 0) {
            $dailySalary = $basicSalary / 30;
            $unpaidLeaveDeduction = $dailySalary * $totalUnpaidLeaveDays;
        }

        // Use ESS context if provided, otherwise use authenticated user's context
        $company_settings = ($essCompanyUserId && $essWorkspaceId)
            ? getCompanyAllSetting($essCompanyUserId, $essWorkspaceId)
            : getCompanyAllSetting();
        $taxableIncome = $basicSalary + $totalIncome + $totalAllowance + $totalBenefit + $totalRegularInputIncome - $withoutTaxRegularInputIncome;

        // Check for frozen tax values on finalized payslip
        $termPayslip = PaySlip::where('employee_id', $employeeId)
            ->where('salary_month', 'LIKE', Carbon::parse($term)->format('Y-m') . '%')
            ->first();

        if ($termPayslip && $termPayslip->tax_year_id !== null) {
            // Use frozen tax values from finalized payslip
            $uif = (float) $termPayslip->uif_amount;
            $sdl = (float) $termPayslip->sdl_amount;
            $payTax = (float) $termPayslip->paye_amount;
            // Use frozen unpaid leave deduction if available
            if ($termPayslip->unpaid_leave_deduction !== null) {
                $unpaidLeaveDeduction = (float) $termPayslip->unpaid_leave_deduction;
            }
        } else {
            // Calculate deductions (rates from locked tax year)
            $taxYear = $taxYear ?? TaxYear::resolveForTerm($term);
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

        $totalDeduction += $uif + $sdl + $payTax + $totalRegularInputDeduction + $incomeData['additionalDeductions'] + $allowanceData['allowanceDeductions'] + $benefitsData['benefitDeduction'] + $unpaidLeaveDeduction;
        $netPay = $basicSalary + $totalIncome + $totalAllowance + $totalBenefit + $totalRegularInputIncome  - $totalDeduction;

        // Use ESS workspace if provided, otherwise use authenticated user's workspace
        $workspace = $essWorkspaceId
            ? WorkSpace::find($essWorkspaceId)
            : WorkSpace::find(Auth::user()->active_workspace);

        if (!$workspace) {
            return redirect()->back()->with('error', __('No active workspace found. Please select a workspace.'));
        }

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
        /*if (isset($regularIncomeData['items']['bursaries_scholarships'])) {
            $incomeItems[] = [
                'name' => 'Bursaries Scholarships - Taxable Portion',
                'amount' => $regularIncomeData['items']['bursaries_scholarships']->taxable_portion
            ];
            $incomeItems[] = [
                'name' => 'Bursaries Scholarships - Exempt Portion',
                'amount' => $regularIncomeData['items']['bursaries_scholarships']->exempt_portion
            ];
        }*/
        /*if (isset($regularIncomeData['items']['companyCar'])) {
            $incomeItems[] = [
                'name' => 'Company Car',
                'amount' => $regularIncomeData['items']['companyCar']->taxable_value
            ];
        }*/
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

        /*if (isset($regularIncomeData['items']['accommodation_benefit'])) {
            $benefitItems[] = [
                'name' => 'Accommodation Benefit',
                'amount' => $regularIncomeData['items']['accommodation_benefit']->amount
            ];
        }*/

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

        //settings - Use ESS context if provided
        $settings = ($essCompanyUserId && $essWorkspaceId)
            ? getCompanyAllSetting($essCompanyUserId, $essWorkspaceId)
            : getCompanyAllSetting();
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
                'period' => Carbon::parse($term)->startOfMonth()->format('d-m-Y') . ' to ' . Carbon::parse($term)->format('d-m-Y'),
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
            // 'company_name' => $workspace->name,
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
        $pdf = Pdf::loadView('hrm.payslip.payslipPreview', $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isRemoteEnabled' => true,
        ]);

        return $pdf->stream('payslip-' . $data['employee']['period'] . '.pdf');
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

    public function finalize(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('setsalary pay slip manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $employeeId = $id;

        if (!$employeeId) {
            return redirect()->back()->with('error', 'Employee ID is missing.');
        }
        if (!$request->term) {
            return redirect()->back()->with('error', 'Term is missing.');
        }

        // Workspace-scoped lookup to prevent cross-tenant access
        $employee = Employee::where('id', $employeeId)->where('workspace_id', getActiveWorkspace())->first();
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found.');
        }

        $formate_month_year = Carbon::parse($request->term)->format('Y-m-d');

        $existingPayslip = PaySlip::where('salary_month', '<', $formate_month_year)->where('employee_id', $employee->id)->where('status', 0)->count();

        if ($existingPayslip > 0) {
            return redirect()->back()->with('error', 'Please finalize the previous payslip first.');
        }

        $payslip = PaySlip::where('salary_month', $formate_month_year)->where('employee_id', $employee->id)->first();
        if ($payslip) {
            $payslipEmployee = $payslip;
        } else {
            $payslipEmployee = new PaySlip();
        }

        $payslipEmployee->employee_id          = $employee->id;
        $payslipEmployee->net_payble           = $request->net_pay;
        $payslipEmployee->salary_month         = $formate_month_year;
        $payslipEmployee->status               = 1;
        $payslipEmployee->basic_salary         = $request->basic_salary;
        $payslipEmployee->allowance            = $request->allowance;
        $payslipEmployee->commission           = 0;
        $payslipEmployee->loan                 = 0;
        $payslipEmployee->saturation_deduction = $request->deduction;
        $payslipEmployee->other_payment        = $request->benefits;
        $payslipEmployee->overtime             = 0;
        $payslipEmployee->company_contribution = 0;
        $payslipEmployee->workspace            = getActiveWorkspace();
        $payslipEmployee->created_by           = creatorId();

        // Freeze tax snapshot
        $taxYear = TaxYear::resolveForTerm($formate_month_year);
        $payslipEmployee->tax_year_id = $taxYear?->id;
        if ($taxYear) {
            $grossSalary = (float) $request->basic_salary + (float) $request->allowance + (float) $request->benefits;
            $uifRate = $taxYear->uif_rate;
            $uifCeiling = $taxYear->uif_ceiling;
            $sdlRate = $taxYear->sdl_rate;
            $payslipEmployee->uif_amount = min($grossSalary * $uifRate, $uifCeiling);
            $company_settings = getCompanyAllSetting();
            $payslipEmployee->sdl_amount = (!empty($company_settings['is_sdl_calculate']) && $company_settings['is_sdl_calculate'] == 1)
                ? $grossSalary * $sdlRate : 0;
            $taxableIncome = $grossSalary;
            $payslipEmployee->paye_amount = TaxCalculationService::calculateMonthlyPAYE($employee->id, $taxableIncome, $formate_month_year);
        }

        $status = $payslipEmployee->save();

        // Calculate next payslip date based on employee's pay frequency
        $nextPayslipDate = $this->calculateNextPayslipDate($employee, $payslipEmployee->salary_month);

        $nextPayslip = PaySlip::where('salary_month', $nextPayslipDate)->where('employee_id', $employee->id)->first();
        if (!$nextPayslip) {
            $nextPayslip = new PaySlip();
            $nextPayslip->employee_id          = $employee->id;
            $nextPayslip->net_payble           = $request->net_pay;
            $nextPayslip->salary_month         = $nextPayslipDate;
            $nextPayslip->status               = 0;
            $nextPayslip->basic_salary         = $request->basic_salary;
            $nextPayslip->allowance            = $request->allowance;
            $nextPayslip->commission           = 0;
            $nextPayslip->loan                 = 0;
            $nextPayslip->saturation_deduction = $request->deduction;
            $nextPayslip->other_payment        = $request->benefits;
            $nextPayslip->overtime             = 0;
            $nextPayslip->company_contribution = 0;
            $nextPayslip->workspace            = getActiveWorkspace();
            $nextPayslip->created_by           = creatorId();
            $nextPayslip->save();
        }

        if ($status) {
            return redirect()->route('payroll.index', ['employee_id' => $id, 'term' => $payslipEmployee->salary_month])
                ->with('success', 'Payslip Finalized successfully.');
        } else {

            return redirect()->route('payroll.index', ['employee_id' => $id])->with('error', 'Something occurred')->withInput();
        }
    }
    public function unFinalize($id)
    {
        if (!Auth::user()->isAbleTo('setsalary pay slip manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        DB::beginTransaction();
        try {
            $payslip = PaySlip::where('id', $id)->where('workspace', getActiveWorkspace())->firstOrFail();
            $payslip->status = 0;
            $payslip->save();
            DB::commit();
            $term = Carbon::parse($payslip->salary_month . '-01')->endOfMonth()->format('Y-m-d');
            return redirect()->route('payroll.index', ['employee_id' => $payslip->employee_id, 'term' => $term])
                ->with('success', 'Payslip Un-Finalized successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
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
        $user = User::find(creatorId());

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

    /**
     * Calculate the next payslip date based on employee's pay frequency
     *
     * @param Employee $employee
     * @param string $currentPayslipDate
     * @return string Date in Y-m-d format
     */
    private function calculateNextPayslipDate($employee, $currentPayslipDate)
    {
        $currentDate = Carbon::parse($currentPayslipDate);

        // Get employee's pay frequency
        $payFrequency = $employee->pay_frequency ? PayFrequency::find($employee->pay_frequency) : null;

        // Default to monthly if no pay frequency set
        if (!$payFrequency) {
            return $currentDate->addMonthsNoOverflow()->lastOfMonth()->format('Y-m-d');
        }

        $frequencyType = strtolower($payFrequency->pay_frequency);

        // DAILY
        if (str_contains($frequencyType, 'daily')) {
            return $currentDate->addDay()->format('Y-m-d');
        }

        // WEEKLY
        if (str_contains($frequencyType, 'weekly') && !str_contains($frequencyType, 'fortnightly')) {
            $weekEndDay = $payFrequency->last_day_of_period ?? 'Sunday';
            $dayOfWeek = $this->getDayOfWeekNumber($weekEndDay);

            // Move to next week's end day
            $nextDate = $currentDate->copy();
            do {
                $nextDate->addDay();
            } while ($nextDate->dayOfWeek !== $dayOfWeek);
            return $nextDate->format('Y-m-d');
        }

        // FORTNIGHTLY (Bi-Weekly)
        if (str_contains($frequencyType, 'fortnightly') || str_contains($frequencyType, 'two weeks')) {
            return $currentDate->addDays(14)->format('Y-m-d');
        }

        // MONTHLY (default)
        $payDay = $payFrequency->last_day_of_month ?? null;

        $nextMonth = $currentDate->copy()->addMonthsNoOverflow();
        if ($payDay) {
            // Use specific day of month, handling months with fewer days
            $maxDay = $nextMonth->daysInMonth;
            $actualPayDay = min($payDay, $maxDay);
            return $nextMonth->setDay($actualPayDay)->format('Y-m-d');
        }

        return $nextMonth->lastOfMonth()->format('Y-m-d');
    }

    /**
     * Convert day name to Carbon day of week number
     *
     * @param string $dayName
     * @return int (0=Sunday, 1=Monday, ... 6=Saturday)
     */
    private function getDayOfWeekNumber($dayName)
    {
        $days = [
            'sunday' => Carbon::SUNDAY,
            'monday' => Carbon::MONDAY,
            'tuesday' => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'thursday' => Carbon::THURSDAY,
            'friday' => Carbon::FRIDAY,
            'saturday' => Carbon::SATURDAY,
        ];
        return $days[strtolower($dayName)] ?? Carbon::SUNDAY;
    }
}
