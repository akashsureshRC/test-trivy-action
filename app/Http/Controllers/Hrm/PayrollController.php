<?php

namespace App\Http\Controllers\Hrm;

use Carbon\Carbon;
use App\Models\Hrm\CompanyCar;
use App\Models\Hrm\Bursary;
use App\Models\Hrm\Donation;
use App\Models\Hrm\EquityInstrument;
use App\Models\Hrm\ExpenseClaim;
use App\Models\Hrm\ExtraPay;
use App\Models\Hrm\PaySlip;
use App\Models\Hrm\PhoneAllowance;
use App\Models\Hrm\ProvidentFund;
use App\Models\Hrm\RelocationAllowance;
use App\Models\Hrm\Repayments;
use App\Models\Hrm\RetirementAnnuitie;
use App\Models\Hrm\StaffPurchase;
use App\Models\Hrm\SubsistenceAllowance;
use App\Models\Hrm\TaxOverDeduction;
use App\Models\Hrm\ToolAllowance;
use App\Models\Hrm\TravelAllowance;
use App\Models\Hrm\UnionMembershipFee;
use App\Models\Hrm\Employee;
use App\Models\Hrm\IncomePolicy;
use App\Models\Hrm\AccommodationBenefit;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Hrm\Payroll;
use App\Models\Hrm\EmployerLoan;
use App\Models\Hrm\SavingsDeduction;
use App\Models\Hrm\TaxDirectiveEntry;
use App\Models\Hrm\BasicSalary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Hrm\AllowanceInternational;
use App\Models\Hrm\AnnualBonus;
use App\Models\Hrm\Garnishee;
use App\Models\Hrm\AnnualPayment;
use App\Models\Hrm\ArbitrationAward;
use App\Models\Hrm\BasicSalaryHour;
use App\Models\Hrm\BroadBasedEmployee;
use App\Models\Hrm\ComputerAllowance;
use App\Models\Hrm\Covid19Disaster;
use App\Models\Hrm\DividendsSubject;
use App\Models\Hrm\EmployeeBenefit;
use App\Models\Hrm\LongServiceAward;
use App\Models\Hrm\MedicalCost;
use App\Models\Hrm\OnceOffCommission;
use App\Models\Hrm\RestraintOfTrade;
use App\Models\Hrm\TerminationLump;
use App\Models\Hrm\TersPayout;
use App\Models\Hrm\UniformAllowance;
use App\Models\Hrm\BursariesScholarship;
use App\Models\Hrm\CompanyCarUnderOperating;
use App\Models\Hrm\IncomeProtection;
use App\Models\Hrm\MaintenanceOrder;
use App\Models\Hrm\MedicalAid;
use App\Models\Hrm\PayslipCommission;
use App\Models\Hrm\PensionFund;
use App\Models\Hrm\PayFrequency;
use App\Models\Hrm\ProvidentFundPayroll;
use App\Services\LeaveAccrualService;
use App\Services\TaxCalculationService;
use App\Models\TaxYear;

class PayrollController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    public function index(Request $request)
    {
        if (!Auth::user()->isAbleTo('setsalary pay slip manage') && in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $employeeId = $request->employee_id;
        if (!$employeeId) {
            return redirect()->back()->with('error', 'Employee ID is missing.');
        }

        // Workspace-scoped lookup
        $employee = Employee::where('id', $employeeId)
            ->where('workspace_id', getActiveWorkspace())
            ->first();
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found.');
        }

        // Non-admin users can only view their own payroll
        if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
            $currentEmployee = Employee::where('user_id', Auth::user()->id)
                ->where('workspace_id', getActiveWorkspace())
                ->first();
            if (!$currentEmployee || $currentEmployee->id != $employee->id) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }


        $payroll = Payroll::where('employee_id', $employeeId)->latest('id')->first();
        if ($request->term) {
            $term = $request->term;
            $payslip = PaySlip::where('employee_id', $employeeId)->latest('id')->first();

            $all_terms = PaySlip::where('employee_id', $employeeId)->orderBy('id', 'DESC')->get();
            $current_payslip = PaySlip::where('employee_id', $employeeId)->where('salary_month', Carbon::parse($term)->format('Y-m-d'))->first();
        } else {
            $payslip = PaySlip::where('employee_id', $employeeId)->latest('id')->first();

            if ($payslip && $payslip->salary_month) {
                if ($payslip->status == 0) {
                    $term = Carbon::parse($payslip->salary_month . '-01')->endOfMonth()->format('Y-m-d');
                } else {
                    $term = Carbon::parse($payslip->salary_month . '-01')->addMonth()->endOfMonth()->format('Y-m-d');
                }
            } else {
                $term = Carbon::parse($employee->date_of_appointment)->endOfMonth()->format('Y-m-d');
            }

            $all_terms = PaySlip::where('employee_id', $employeeId)->orderBy('id', 'DESC')->get();
            $current_payslip = PaySlip::where('employee_id', $employeeId)->where('salary_month', Carbon::parse($term)->format('Y-m-d'))->first();
        }

        // Validate tax year exists for this term (unless payslip has frozen values)
        if (!$current_payslip || $current_payslip->tax_year_id === null) {
            if (!TaxYear::resolveForTerm($term)) {
                return redirect()->back()->with('error', 'No Tax Year configuration found for ' . Carbon::parse($term)->format('F Y') . '. Please contact customer support for assistance.');
            }
        }

        $basicSalaryData = BasicSalary::where('employee_id', $employeeId)->where('term', $term)->first();
        if ($basicSalaryData && $basicSalaryData->hourly_paid == 1) {
            $basicSalaryHour = BasicSalaryHour::where('employee_id', $employeeId)->where('term', $term)->first();
            if ($basicSalaryHour) {
                $basicSalaryNormal = ($basicSalaryData->hourly_rate * $basicSalaryHour->normal_hours);
                $taxYear = TaxYear::resolveForTerm($term);
                $otMultiplier = $taxYear ? $taxYear->ot_multiplier : 1.5;
                $basicSalaryOT = ($basicSalaryData->hourly_rate * $basicSalaryHour->ot_hours * $otMultiplier);
                $basicSalary = round(($basicSalaryNormal + $basicSalaryOT), 2);
                $basicSalaryData->normal_hour_value = $basicSalaryHour->normal_hours;
                $basicSalaryData->normal_hour_amount = $basicSalaryNormal;
                $basicSalaryData->ot_hour_value = $basicSalaryHour->ot_hours;
                $basicSalaryData->ot_hour_amount = $basicSalaryOT;
            } else {
                $basicSalary = 0;
                $basicSalaryData->normal_hour_value = 0;
                $basicSalaryData->normal_hour_amount = 0.00;
                $basicSalaryData->ot_hour_value = 0;
                $basicSalaryData->ot_hour_amount = 0.00;
            }
        } else {
            $basicSalary = $basicSalaryData ? $basicSalaryData->fixed_salary : 0;
        }


        //regular inputs
        $regularIncomeData = $this->calculateRegularIncomeItems($employeeId, $term);
        $regularDeductionData = $this->calculateRegularDeductionItems($employeeId, $term);
        $regularInputs = array_unique(array_merge(
            array_keys($regularIncomeData['items']),
            array_keys($regularDeductionData['items'])
        ));


        $totalRegularInputIncome = $regularIncomeData['totalRegularInputIncome'];
        $withoutTaxRegularInputIncome = $regularIncomeData['withoutTaxRegularInputIncome'];
        $totalRegularInputAllowance = $regularIncomeData['totalRegularInputAllowance'];
        $totalRegularInputDeduction = $regularDeductionData['totalRegularInputDeduction'];

        // payslip inputs
        $incomeData = $this->calculateIncomeItems($employeeId, $term);
        $allowanceData = $this->calculateAllowanceItems($employeeId, $term);
        $deductionData = $this->calculateDeductionItems($employeeId, $term);
        $benefitsData = $this->calculateBenefitItems($employeeId, $term);

        $leaveInfo = $this->getEmployeeLeaveData($employeeId, $term);
        $totalUnpaidLeave = $leaveInfo['totalUnpaidLeave'];
        $daysInMonth = Carbon::parse($term)->daysInMonth;
        $lossOfPay = 0;
        if ($totalUnpaidLeave > 0 && $basicSalary > 0) {
            $perDaySalary = $basicSalary / 30;
            $lossOfPay = $perDaySalary * $totalUnpaidLeave;
        }

        $totalIncome    = $incomeData['totalIncome'];
        $totalAllowance = $allowanceData['totalAllowance'] + $regularDeductionData['additionalIncome']
            + $regularIncomeData['totalRegularInputAllowance'] + $benefitsData['benefitAllowance']
            + $regularIncomeData['withoutTaxRegularInputIncome'];
        $totalDeduction = $deductionData['totalDeduction'];
        $totalBenefit   = $benefitsData['totalBenefit'];

        $company_settings = getCompanyAllSetting();
        $taxableIncome = $basicSalary + $totalIncome + $totalAllowance + $totalBenefit + $totalRegularInputIncome - $withoutTaxRegularInputIncome;

        if ($current_payslip && $current_payslip->tax_year_id !== null) {
            // Use frozen tax values from finalized payslip
            $uif = (float) $current_payslip->uif_amount;
            $sdl = (float) $current_payslip->sdl_amount;
            $payTax = (float) $current_payslip->paye_amount;
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
            $payTax = TaxCalculationService::calculateMonthlyPAYE($employeeId, $taxableIncome, $term);
        }

        $totalDeduction += $uif + $sdl + $payTax + $totalRegularInputDeduction + $incomeData['additionalDeductions'] + $allowanceData['allowanceDeductions'] + $benefitsData['benefitDeduction'] + $lossOfPay;
        $netPay = $basicSalary + $totalIncome + $totalAllowance + $totalBenefit + $totalRegularInputIncome - $totalDeduction;
        $all_terms_full = Payroll::where('employee_id', $employeeId)
            ->pluck('term')->unique(); //new

        $payrolls = Payroll::with('employee')
            ->where('employee_id', $employeeId)
            ->when($term, function ($query) use ($term) {
                $query->where('term', $term);
            })
            ->get(); //new

        Log::info("Payroll Calculation for Employee ID: {$employeeId}", [
            'employee_id' => $employee->id,
            'employee_number' => $employee->employee_id,
            'term' => $term,
            'all_terms' => $all_terms,
            'basicSalary' => $basicSalary,
            'payroll' => $payroll,
            'uif' => $uif,
            'payTax' => $payTax,
            'netPay' => $netPay,
            'totalIncome' => $totalIncome,
            'totalAllowance' => $totalAllowance,
            'totalDeduction' => $totalDeduction,
            'totalBenefit' => $totalBenefit,
            'totalRegularInputIncome' => $totalRegularInputIncome
        ]);

        return view('hrm.payroll.index', array_merge(
            [
                'employee' => $employee,
                'term' => $term,
                'all_terms' => $all_terms,
                'basicSalary' => $basicSalary,
                'basicSalaryData' => $basicSalaryData,
                'payroll' => $payroll,
                'uif' => $uif,
                'sdl' => $sdl,
                'payTax' => $payTax,
                'netPay' => $netPay,
                'totalIncome' => $totalIncome,
                'totalAllowance' => $totalAllowance,
                'totalDeduction' => $totalDeduction,
                'totalBenefit' => $totalBenefit,
                'totalRegularInputIncome' => $totalRegularInputIncome,
                'withoutTaxRegularInputIncome' => $withoutTaxRegularInputIncome,
                'current_payslip' => $current_payslip,
                'payrolls' => $payrolls, // new
                'all_terms_full' => $all_terms_full, //new
                'regularInputs' => $regularInputs,  //new
                'lossOfPay' => $lossOfPay,
            ],
            $incomeData['items'],
            $allowanceData['items'],
            $deductionData['items'],
            $benefitsData['items'],
            $regularIncomeData['items'],
            $regularDeductionData['items']
        ));
    }

    public function createPayslip($id)
    {
        $employeeId = $id;
        if (!$employeeId) {
            return redirect()->back()->with('error', 'Employee ID is missing.');
        }

        $employee = Employee::find($employeeId);
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found.');
        }
        
        // Get employee's pay frequency
        $payFrequency = $employee->pay_frequency ? PayFrequency::find($employee->pay_frequency) : null;
        $lastPayslip = PaySlip::where('employee_id', $employeeId)->latest('id')->first();
        
        // Calculate next payslip date based on pay frequency
        $nextPayslipDate = $this->calculateNextPayslipDate($payFrequency, $lastPayslip);

        return view('hrm.payslip.create-payslip', [
            'employee' => $employee,
            'next_payslip' => $nextPayslipDate,
        ]);
    }

    /**
     * Calculate the next payslip date based on employee's pay frequency
     *
     * @param PayFrequency|null $payFrequency
     * @param PaySlip|null $lastPayslip
     * @return string Date in Y-m-d format
     */
    private function calculateNextPayslipDate($payFrequency, $lastPayslip)
    {
        $today = Carbon::now();
        $lastPayDate = $lastPayslip ? Carbon::parse($lastPayslip->salary_month) : null;

        // Default to monthly if no pay frequency set
        if (!$payFrequency) {
            return $lastPayDate 
                ? $lastPayDate->addMonthsNoOverflow()->lastOfMonth()->format('Y-m-d')
                : $today->lastOfMonth()->format('Y-m-d');
        }

        $frequencyType = strtolower($payFrequency->pay_frequency);

        // DAILY
        if (str_contains($frequencyType, 'daily')) {
            if ($lastPayDate) {
                return $lastPayDate->addDay()->format('Y-m-d');
            }
            return $today->format('Y-m-d');
        }

        // WEEKLY
        if (str_contains($frequencyType, 'weekly') && !str_contains($frequencyType, 'fortnightly')) {
            $weekEndDay = $payFrequency->last_day_of_period ?? 'Sunday';
            $dayOfWeek = $this->getDayOfWeekNumber($weekEndDay);

            if ($lastPayDate) {
                // Next week ending on the specified day (exactly 7 days after last)
                $nextDate = $lastPayDate->copy();
                do {
                    $nextDate->addDay();
                } while ($nextDate->dayOfWeek !== $dayOfWeek);
                return $nextDate->format('Y-m-d');
            }

            // No previous payslip - find next occurrence of week-end day from today
            $nextDate = $today->copy();
            do {
                $nextDate->addDay();
            } while ($nextDate->dayOfWeek !== $dayOfWeek);
            return $nextDate->format('Y-m-d');
        }

        // FORTNIGHTLY (Bi-Weekly)
        if (str_contains($frequencyType, 'fortnightly') || str_contains($frequencyType, 'two weeks')) {
            if ($lastPayDate) {
                // Add 14 days from last payslip
                return $lastPayDate->addDays(14)->format('Y-m-d');
            }

            // Use anchor date (biweekly_date) to calculate next fortnightly pay date
            if ($payFrequency->biweekly_date) {
                $anchorDate = Carbon::parse($payFrequency->biweekly_date);
                $nextDate = $anchorDate->copy();
                
                // Move forward in 14-day increments until we pass today
                while ($nextDate->lte($today)) {
                    $nextDate->addDays(14);
                }
                return $nextDate->format('Y-m-d');
            }

            // Fallback: next occurrence of last_day_of_period + 14 days cycle
            $weekEndDay = $payFrequency->last_day_of_period ?? 'Friday';
            $dayOfWeek = $this->getDayOfWeekNumber($weekEndDay);
            $nextDate = $today->copy();
            while ($nextDate->dayOfWeek !== $dayOfWeek) {
                $nextDate->addDay();
            }
            return $nextDate->format('Y-m-d');
        }

        // MONTHLY (default)
        $payDay = $payFrequency->last_day_of_month ?? null;
        
        if ($lastPayDate) {
            $nextMonth = $lastPayDate->addMonthsNoOverflow();
            if ($payDay) {
                // Use specific day of month, handling months with fewer days
                $maxDay = $nextMonth->daysInMonth;
                $actualPayDay = min($payDay, $maxDay);
                return $nextMonth->setDay($actualPayDay)->format('Y-m-d');
            }
            return $nextMonth->lastOfMonth()->format('Y-m-d');
        }

        // No previous payslip - calculate for current month
        if ($payDay) {
            $payDateThisMonth = $today->copy()->setDay(min($payDay, $today->daysInMonth));
            // If payday has passed this month, use next month
            if ($payDateThisMonth->lt($today)) {
                $nextMonth = $today->copy()->addMonthsNoOverflow();
                return $nextMonth->setDay(min($payDay, $nextMonth->daysInMonth))->format('Y-m-d');
            }
            return $payDateThisMonth->format('Y-m-d');
        }
        
        return $today->lastOfMonth()->format('Y-m-d');
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

    public function onceOffPayslip(Request $request, string $id)
    {
        $employeeId = $id;
        if (!$employeeId) {
            return redirect()->back()->with('error', 'Employee ID is missing.');
        }

        $employee = Employee::find($employeeId);
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found.');
        }

        $term = Carbon::parse($request->date)->format('Y-m-d');

        $payslip = PaySlip::where('employee_id', $employeeId)->where('salary_month', Carbon::parse($request->date)->format('Y-m-d'))->first();
        if ($payslip) {
            return redirect()->back()->with('error', 'Payslip for this month already exists.');
        }

        $basicSalaryData = BasicSalary::where('employee_id', $employeeId)->first();
        if ($basicSalaryData && $basicSalaryData->hourly_paid == 1) {
            $basicSalaryHour = BasicSalaryHour::where('employee_id', $employeeId)->where('term', $term)->first();
            if ($basicSalaryHour) {
                $basicSalaryNormal = ($basicSalaryData->hourly_rate * $basicSalaryHour->normal_hours);
                $taxYear = TaxYear::resolveForTerm($term);
                $otMultiplier = $taxYear ? $taxYear->ot_multiplier : 1.5;
                $basicSalaryOT = ($basicSalaryData->hourly_rate * $basicSalaryHour->ot_hours * $otMultiplier);
                $basicSalary = round(($basicSalaryNormal + $basicSalaryOT), 2);
                $basicSalaryData->normal_hour_value = $basicSalaryHour->normal_hours;
                $basicSalaryData->normal_hour_amount = $basicSalaryNormal;
                $basicSalaryData->ot_hour_value = $basicSalaryHour->ot_hours;
                $basicSalaryData->ot_hour_amount = $basicSalaryOT;
            } else {
                $basicSalary = 0;
                $basicSalaryData->normal_hour_value = 0;
                $basicSalaryData->normal_hour_amount = 0.00;
                $basicSalaryData->ot_hour_value = 0;
                $basicSalaryData->ot_hour_amount = 0.00;
            }
        } else {
            $basicSalary = $basicSalaryData ? $basicSalaryData->fixed_salary : 0;
        }
        $regularIncomeData = $this->calculateRegularIncomeItems($employeeId, $term);
        $regularDeductionData = $this->calculateRegularDeductionItems($employeeId, $term);
        $incomeData = $this->calculateIncomeItems($employeeId, $term);
        $allowanceData = $this->calculateAllowanceItems($employeeId, $term);
        $deductionData = $this->calculateDeductionItems($employeeId, $term);
        $benefitsData = $this->calculateBenefitItems($employeeId, $term);

        $totalIncome = $incomeData['totalIncome'];
        $totalAllowance = $allowanceData['totalAllowance'] + $regularDeductionData['additionalIncome']
            + $regularIncomeData['totalRegularInputAllowance'] + $benefitsData['benefitAllowance']
            + $regularIncomeData['withoutTaxRegularInputIncome'];
        $totalDeduction = $deductionData['totalDeduction'];
        $totalBenefit   = $benefitsData['totalBenefit'];
        $totalRegularInputIncome = $regularIncomeData['totalRegularInputIncome'];
        $withoutTaxRegularInputIncome = $regularIncomeData['withoutTaxRegularInputIncome'];
        $totalRegularInputAllowance = $regularIncomeData['totalRegularInputAllowance'];
        $formatted_month_year = Carbon::parse($request->date)->format('Y-m-d');

        $netPay = $basicSalary + $totalIncome + $totalAllowance + $totalBenefit + $totalRegularInputIncome  + $withoutTaxRegularInputIncome + $totalRegularInputAllowance - $totalDeduction;

        $payslipEmployee = new PaySlip();
        $payslipEmployee->employee_id          = $employee->id;
        $payslipEmployee->net_payble           = $netPay;
        $payslipEmployee->salary_month         = $formatted_month_year;
        $payslipEmployee->status               = 1;
        $payslipEmployee->basic_salary         = $basicSalary;
        $payslipEmployee->allowance            = $totalAllowance;
        $payslipEmployee->commission           = 0;
        $payslipEmployee->loan                 = 0;
        $payslipEmployee->saturation_deduction = $totalDeduction;
        $payslipEmployee->other_payment        = $totalBenefit;
        $payslipEmployee->overtime             = 0;
        $payslipEmployee->company_contribution = 0;
        $payslipEmployee->workspace            = getActiveWorkspace();
        $payslipEmployee->created_by           = creatorId();
        $status = $payslipEmployee->save();

        return redirect()->route('payroll.index', ['employee_id' => $employeeId, 'term' => $term])->with('success', 'Once-off Payslip Saved Successfully!');
    }

    public function nextPayslip(Request $request, string $id)
    {
        $employeeId = $id;
        if (!$employeeId) {
            return redirect()->back()->with('error', 'Employee ID is missing.');
        }

        $employee = Employee::find($employeeId);
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found.');
        }

        $term = Carbon::parse($request->date)->format('Y-m-d');

        $payslip = PaySlip::where('employee_id', $employeeId)->where('salary_month', Carbon::parse($term)->format('Y-m-d'))->first();
        if ($payslip) {
            return redirect()->back()->with('error', 'Payslip for this month already exists.');
        }

        $basicSalaryData = BasicSalary::where('employee_id', $employeeId)->first();
        if ($basicSalaryData && $basicSalaryData->hourly_paid == 1) {
            $basicSalaryHour = BasicSalaryHour::where('employee_id', $employeeId)->where('term', $term)->first();
            if ($basicSalaryHour) {
                $basicSalaryNormal = ($basicSalaryData->hourly_rate * $basicSalaryHour->normal_hours);
                $taxYear = TaxYear::resolveForTerm($term);
                $otMultiplier = $taxYear ? $taxYear->ot_multiplier : 1.5;
                $basicSalaryOT = ($basicSalaryData->hourly_rate * $basicSalaryHour->ot_hours * $otMultiplier);
                $basicSalary = round(($basicSalaryNormal + $basicSalaryOT), 2);
                $basicSalaryData->normal_hour_value = $basicSalaryHour->normal_hours;
                $basicSalaryData->normal_hour_amount = $basicSalaryNormal;
                $basicSalaryData->ot_hour_value = $basicSalaryHour->ot_hours;
                $basicSalaryData->ot_hour_amount = $basicSalaryOT;
            } else {
                $basicSalary = 0;
                $basicSalaryData->normal_hour_value = 0;
                $basicSalaryData->normal_hour_amount = 0.00;
                $basicSalaryData->ot_hour_value = 0;
                $basicSalaryData->ot_hour_amount = 0.00;
            }
        } else {
            $basicSalary = $basicSalaryData ? $basicSalaryData->fixed_salary : 0;
        }
        $regularIncomeData = $this->calculateRegularIncomeItems($employeeId, $term);
        $regularDeductionData = $this->calculateRegularDeductionItems($employeeId, $term);
        $incomeData = $this->calculateIncomeItems($employeeId, $term);
        $allowanceData = $this->calculateAllowanceItems($employeeId, $term);
        $deductionData = $this->calculateDeductionItems($employeeId, $term);
        $benefitsData = $this->calculateBenefitItems($employeeId, $term);

        $totalIncome = $incomeData['totalIncome'];
        $totalAllowance = $allowanceData['totalAllowance'] + $regularDeductionData['additionalIncome']
            + $regularIncomeData['totalRegularInputAllowance'] + $benefitsData['benefitAllowance']
            + $regularIncomeData['withoutTaxRegularInputIncome'];
        $totalDeduction = $deductionData['totalDeduction'];
        $totalBenefit   = $benefitsData['totalBenefit'];
        $totalRegularInputIncome = $regularIncomeData['totalRegularInputIncome'];
        $withoutTaxRegularInputIncome = $regularIncomeData['withoutTaxRegularInputIncome'];
        $totalRegularInputAllowance = $regularIncomeData['totalRegularInputAllowance'];
        $formatted_month_year = Carbon::parse($request->date)->format('Y-m-d');

        $netPay = $basicSalary + $totalIncome + $totalAllowance + $totalBenefit + $totalRegularInputIncome  + $withoutTaxRegularInputIncome + $totalRegularInputAllowance - $totalDeduction;

        $payslipEmployee = new PaySlip();
        $payslipEmployee->employee_id          = $employee->id;
        $payslipEmployee->net_payble           = $netPay;
        $payslipEmployee->salary_month         = $formatted_month_year;
        $payslipEmployee->status               = 1;
        $payslipEmployee->basic_salary         = $basicSalary;
        $payslipEmployee->allowance            = $totalAllowance;
        $payslipEmployee->commission           = 0;
        $payslipEmployee->loan                 = 0;
        $payslipEmployee->saturation_deduction = $totalDeduction;
        $payslipEmployee->other_payment        = $totalBenefit;
        $payslipEmployee->overtime             = 0;
        $payslipEmployee->company_contribution = 0;
        $payslipEmployee->workspace            = getActiveWorkspace();
        $payslipEmployee->created_by           = creatorId();
        $status = $payslipEmployee->save();

        return redirect()->route('payroll.index', ['employee_id' => $employeeId, 'term' => $term])->with('success', 'Once-off Payslip Saved Successfully!');
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
        $travel_allowance = TravelAllowance::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
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
            $totalRegularInputIncome += ($companyCar->deemed_value * intval($companyCar->taxableType->percentage) / 100);
            $companyCar->taxable_value = ($companyCar->deemed_value * intval($companyCar->taxableType->percentage) / 100);
            $items['companyCar'] =   $companyCar;
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
        } else {
            Log::warning("No Savings Deduction found for Employee ID: {$employeeId}");
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
            // $taxDirectiveEntry->taxable_value = ($taxDirectiveEntry->directive_income_amount * intval($taxDirectiveEntry->percentage)) / 100; // Percentage of directive income amount
            // $totalRegularInputIncome += $taxDirectiveEntry->directive_income_amount;
            // $totalRegularInputIncome += $taxDirectiveEntry->percentage;
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

        $garnishee = Garnishee::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
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

        $retirement_annuity = RetirementAnnuitie::where('employee_id', $employeeId)->where('term', $term)->first();
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

        $bursaries_scholarships = BursariesScholarship::where('employee_id', $employeeId)->where('term', $term)->first();
        if (!empty($bursaries_scholarships) && is_object($bursaries_scholarships)) {
            if ($bursaries_scholarships->employee_handles_payment == 1) {
                $totalAllowance += $bursaries_scholarships->taxable_portion;
                $totalAllowance += $bursaries_scholarships->exempt_portion;
            }
        }

        $bursary = Bursary::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($bursary) {
            if ($bursary->employee_handles_payment == 1) {
                $totalAllowance += $bursary->taxable_portion;
                $totalAllowance += $bursary->exempt_portion;
            }
        }

        // Tax Directive
        $taxDirectiveEntry = TaxDirectiveEntry::where('employee_id', $employeeId)->where('term', $term)->first();

        if ($taxDirectiveEntry) {
            // $taxDirectiveEntry->taxable_value = ($taxDirectiveEntry->directive_income_amount * intval($taxDirectiveEntry->percentage)) / 100; // Percentage of directive income amount
            $totalAllowance += $taxDirectiveEntry->directive_income_amount;
            // $totalRegularInputIncome += $taxDirectiveEntry->percentage;
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

        // Bursaries And Scholarships
        $bursary = Bursary::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($bursary) {
            $items['bursary'] = $bursary;
        }

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

        // Tax Directive
        $taxDirectiveEntry = TaxDirectiveEntry::where('employee_id', $employeeId)->where('term', $term)->first();

        if ($taxDirectiveEntry) {
            // $taxDirectiveEntry->taxable_value = ($taxDirectiveEntry->directive_income_amount * intval($taxDirectiveEntry->percentage)) / 100; // Percentage of directive income amount
            // $totalBenefit += $taxDirectiveEntry->directive_income_amount;
            // $totalRegularInputIncome += $taxDirectiveEntry->percentage;
            $items['taxDirectiveEntry'] = $taxDirectiveEntry;
        }

        return [
            'totalBenefit' => $totalBenefit,
            'benefitDeduction' => $benefitDeduction,
            'benefitAllowance' => $benefitAllowance,
            'items' => $items
        ];
    }
    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
    {
        $employeeId = $request->employee_id;


        if (!$employeeId) {
            return redirect()->route('employee-salary.create')->with('error', 'Employee ID is required.');
        }


        $fixedSalary = $request->fixed_salary;
        if (!$fixedSalary) {
            return redirect()->route('employee-salary.create')->with('error', 'Fixed salary is required.');
        }


        $basicSalary = new BasicSalary();
        $basicSalary->employee_id = $employeeId;
        $basicSalary->fixed_salary = $fixedSalary;
        $basicSalary->save();


        $payroll = new Payroll();
        $payroll->employee_id = $employeeId;
        $payroll->basic_salary = $basicSalary->fixed_salary;

        $uiF = $basicSalary->fixed_salary * 0.01;
        $payTax = $basicSalary->fixed_salary * 0.15;
        $netPay = $basicSalary->fixed_salary - ($uiF + $payTax);

        $payroll->uif_amount = $uiF;
        $payroll->tax_pay = $payTax;
        $payroll->net_pay = $netPay;


        $payroll->save();


        return redirect()->route('employee-salary.create')->with('success', 'Basic Salary added and payroll updated.');
    }




    public function calculatePayroll($employeeId)
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found.');
        }


        $basicSalaryData = BasicSalary::where('employee_id', $employeeId)->first();
        $basicSalary = $basicSalaryData ? $basicSalaryData->amount : 0;


        $incomePolicyPayout = IncomePolicy::where('employee_id', $employeeId)->sum('payout_amount');


        $uif = $basicSalary * 0.01;


        $payTax = $basicSalary > 5000 ? $basicSalary * 0.10 : 0;


        $totalIncome = $basicSalary + $incomePolicyPayout;


        $totalDeductions = $uif + $payTax;


        $netPay = $totalIncome - $totalDeductions;


        Log::info("Payroll Calculation for Employee ID: {$employeeId}", [
            'basicSalary' => $basicSalary,
            'incomePolicyPayout' => $incomePolicyPayout,
            'uif' => $uif,
            'payTax' => $payTax,
            'totalIncome' => $totalIncome,
            'totalDeductions' => $totalDeductions,
            'netPay' => $netPay
        ]);


        Payroll::updateOrCreate(
            ['employee_id' => $employeeId],
            [
                'basic_salary' => $basicSalary,
                'loss_of_income_policy_payout' => $incomePolicyPayout,
                'uif_amount' => $uif,
                'tax_pay' => $payTax,
                'total_income' => $totalIncome,
                'deductions' => $totalDeductions,
                'net_pay' => $netPay,
            ]
        );

        return redirect()->route('payroll.index')->with('success', 'Payroll calculated successfully.');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    /**public function regularInputs(Request $request)
    {
        $employeeId = $request->employee_id;


        $employee = \App\Models\Hrm\Employee::find($employeeId);

        return view('hrm.payroll.regularinputs', compact('employee'));
    }*/
    public function store(Request $request)
    {

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'fixed_salary' => 'required|numeric|min:0',
            'travel_allowance' => 'required|numeric|min:0',
        ]);


        $employee = Employee::find($validated['employee_id']);
        $basicSalary = $validated['fixed_salary'];
        $travelAllowance = $validated['travel_allowance'];

        $uif = $basicSalary * 0.01;
        $payTax = $basicSalary > 5000 ? $basicSalary * 0.10 : 0;
        $netPay = $basicSalary + $travelAllowance - $uif - $payTax;

        // Create payroll record
        $payroll = Payroll::create([
            'employee_id' => $validated['employee_id'],
            'basic_salary' => $basicSalary,
            'travel_allowance' => $travelAllowance,
            'uif' => $uif,
            'pay_tax' => $payTax,
            'net_pay' => $netPay,
        ]);

        return redirect()->route('payroll.index')->with('success', 'Payroll added successfully');
    }
    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */

    public function showRegularInputs(Request $request)
    {
        $employee_id = $request->input('employee_id') ?? session('employee_id');

        if (!$employee_id) {
            return redirect()->route('payroll.index')->with('error', 'Employee ID is missing.');
        }

        // Fetch input_type from the payrolls table where employee_id matches
        $payrollRegularInputs = Payroll::where('employee_id', $employee_id)
            ->pluck('input_type')
            ->toArray();

        return view('hrm.payroll.regularinputs', compact('payrollRegularInputs', 'employee_id'));
    }

    //Income policy


    /**
     * Calculate UIF (Unemployment Insurance Fund) based on the employee's salary.
     * @param Employee $employee
     * @return float
     */

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


    public function fetchPayrollDetails($employee_id)
    {
        $payroll = Payroll::where('employee_id', $employee_id)->first();

        if (!$payroll) {
            return response()->json(['error' => 'No payroll data found'], 404);
        }


        $netPay = $payroll->calculateNetPay();

        return response()->json([
            'basic_salary' => $payroll->basic_salary,
            'income_policy' => $payroll->income_policy,
            'travel_allowance' => $payroll->travel_allowance,
            'uif' => $payroll->uif,
            'pay_tax' => $payroll->pay_tax,
            'net_pay' => $netPay
        ]);
    }



    public function updateBasicSalary(Request $request, $employeeId)
    {

        $employee = \App\Models\Hrm\Employee::findOrFail($employeeId);


        $request->validate([
            'basic_salary' => 'required|numeric|min:0',
        ]);


        $employee->basic_salary = $request->input('basic_salary');
        $employee->save();


        return redirect()->route('payroll.index', ['employee_id' => $employeeId])
            ->with('success', 'Basic salary updated successfully!');
    }

    /**
     *
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $payroll = Payroll::findOrFail($id);
        $payroll->delete();

        return redirect()->route('payroll.index')->with('success', 'Payroll record deleted successfully.');
    }
    //payslip inputs
    public function payslipInputs(Request $request)
    {
        $employeeId = $request->employee_id;
        $term = $request->term;

        $paySlipInputs = [];
        //incomes
        $annual_bonus = AnnualBonus::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($annual_bonus) {
            $paySlipInputs[] = 'annual_bonus';
        }
        $annual_payment = AnnualPayment::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($annual_payment) {
            $paySlipInputs[] = 'annual_payment';
        }
        $extra_pay = ExtraPay::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($extra_pay) {
            $paySlipInputs[] = 'extra_pay';
        }
        $once_off_commission = OnceOffCommission::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($once_off_commission) {
            $paySlipInputs[] = 'once_off_commission';
        }
        $restraints_of_trade = RestraintOfTrade::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($restraints_of_trade) {
            $paySlipInputs[] = 'restraints_of_trade';
        }
        $arbitration_award = ArbitrationAward::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($arbitration_award) {
            $paySlipInputs[] = 'arbitration_award';
        }
        $dividends_subject = DividendsSubject::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($dividends_subject) {
            $paySlipInputs[] = 'dividends_subject';
        }
        //allowances
        $broad_based_employee = BroadBasedEmployee::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($broad_based_employee) {
            $paySlipInputs[] = 'broad_based_employee';
        }
        $computer_allowance = ComputerAllowance::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($computer_allowance) {
            $paySlipInputs[] = 'computer_allowance';
        }
        $expense_claim = ExpenseClaim::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($expense_claim) {
            $paySlipInputs[] = 'expense_claim';
        }
        $equity_instruments = EquityInstrument::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($equity_instruments) {
            $paySlipInputs[] = 'equity_instruments';
        }
        $phone_allowance = PhoneAllowance::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($phone_allowance) {
            $paySlipInputs[] = 'phone_allowance';
        }
        $relocation_allowance = RelocationAllowance::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($relocation_allowance) {
            $paySlipInputs[] = 'relocation_allowance';
        }
        $allowance_international = AllowanceInternational::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($allowance_international) {
            $paySlipInputs[] = 'allowance_international';
        }
        $subsistence_allowance = SubsistenceAllowance::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($subsistence_allowance) {
            $paySlipInputs[] = 'subsistence_allowance';
        }
        $tool_allowance = ToolAllowance::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($tool_allowance) {
            $paySlipInputs[] = 'tool_allowance';
        }
        $uniform_allowance = UniformAllowance::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($uniform_allowance) {
            $paySlipInputs[] = 'uniform_allowance';
        }
        //deductions
        $donation = Donation::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($donation) {
            $paySlipInputs[] = 'donation';
        }
        $repayment = Repayments::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($repayment) {
            $paySlipInputs[] = 'repayment';
        }
        $staff_purchase = StaffPurchase::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($staff_purchase) {
            $paySlipInputs[] = 'staff_purchase';
        }
        //benefits
        $bursary = Bursary::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($bursary) {
            $paySlipInputs[] = 'bursary';
        }
        $benefit = EmployeeBenefit::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($benefit) {
            $paySlipInputs[] = 'benefit';
        }
        $medical_cost = MedicalCost::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($medical_cost) {
            $paySlipInputs[] = 'medical_cost';
        }
        $covid = Covid19Disaster::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($covid) {
            $paySlipInputs[] = 'covid';
        }
        $long_service_award = LongServiceAward::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($long_service_award) {
            $paySlipInputs[] = 'long_service_award';
        }
        $ters_payout = TersPayout::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($ters_payout) {
            $paySlipInputs[] = 'ters_payout';
        }
        $termination_lump_sum = TerminationLump::where('employee_id', $employeeId)->where('term', $term)->latest('id')->first();
        if ($termination_lump_sum) {
            $paySlipInputs[] = 'termination_lump_sum';
        }

        $employee = Employee::find($employeeId);
        return view('hrm.payroll.payslipInputs', compact('employee', 'paySlipInputs', 'term'));
    }


    public function regularInputs(Request $request)
    {
        $employeeId = $request->employee_id;
        $term = $request->term;

        $regularInputs = [];
        //incomes
        $commission = PayslipCommission::where('employee_id', $employeeId)->where('term', $term)->first();
        if ($commission) {
            $regularInputs[] = 'commission';
        }
        $basic_salary = BasicSalary::where('employee_id', $employeeId)->latest('id')->where('term', $term)->first();
        if ($basic_salary) {
            $regularInputs[] = 'basic_salary';
        }
        $income_policy = IncomePolicy::where('employee_id', $employeeId)->latest('id')->where('term', $term)->first();
        if ($income_policy) {
            $regularInputs[] = 'income_policy';
        }
        $travel_allowance = TravelAllowance::where('employee_id', $employeeId)->latest('id')->where('term', $term)->first();
        if ($travel_allowance) {
            $regularInputs[] = 'travel_allowance';
        }
        $accommodation_benefit = AccommodationBenefit::where('employee_id', $employeeId)->latest('id')->where('term', $term)->first();
        if ($accommodation_benefit) {
            $regularInputs[] = 'accommodation_benefit';
        }
        $bursaries_scholarships = BursariesScholarship::where('employee_id', $employeeId)->latest('id')->where('term', $term)->first();
        if ($bursaries_scholarships) {
            $regularInputs[] = 'bursaries_scholarships';
        }
        $companyCar = CompanyCar::where('employee_id', $employeeId)->latest('id')->where('term', $term)->first();
        if ($companyCar) {
            $regularInputs[] = 'companyCar';
        }
        $companyCarUnderOperating = CompanyCarUnderOperating::where('employee_id', $employeeId)->latest('id')->where('term', $term)->first();
        if ($companyCarUnderOperating) {
            $regularInputs[] = 'companyCarUnderOperating';
        }
        $garnishee = Garnishee::where('employee_id', $employeeId)->latest('id')->where('term', $term)->first();
        if ($garnishee) {
            $regularInputs[] = 'garnishee';
        }
        $incomeProtection = IncomeProtection::where('employee_id', $employeeId)->latest('id')->where('term', $term)->first();
        if ($incomeProtection) {
            $regularInputs[] = 'incomeProtection';
        }
        $maintenance_order = MaintenanceOrder::where('employee_id', $employeeId)->latest('id')->where('term', $term)->first();
        if ($maintenance_order) {
            $regularInputs[] = 'maintenance_order';
        }
        $medical_aid = MedicalAid::where('employee_id', $employeeId)->latest('id')->where('term', $term)->first();
        if ($medical_aid) {
            $regularInputs[] = 'medical_aid';
        }
        $pension_fund = PensionFund::where('employee_id', $employeeId)->latest('id')->where('term', $term)->first();
        if ($pension_fund) {
            $regularInputs[] = 'pension_fund';
        }
        $provident_fund = ProvidentFund::where('employee_id', $employeeId)->latest('id')->where('term', $term)->first();
        if ($provident_fund) {
            $regularInputs[] = 'provident_fund';
        }
        $retirement_annuity = RetirementAnnuitie::where('employee_id', $employeeId)->latest('id')->where('term', $term)->first();
        if ($retirement_annuity) {
            $regularInputs[] = 'retirement_annuity';
        }
        $union_membership = UnionMembershipFee::where('employee_id', $employeeId)->latest('id')->where('term', $term)->first();
        if ($union_membership) {
            $regularInputs[] = 'union_membership';
        }
        $tax_over_deduction = TaxOverDeduction::where('employee_id', $employeeId)->latest('id')->where('term', $term)->first();
        if ($tax_over_deduction) {
            $regularInputs[] = 'tax_over_deduction';
        }
        $loan = EmployerLoan::where('employee_id', $employeeId)->latest('id')->where('term', $term)->first();
        if ($loan) {
            $regularInputs[] = 'loan';
        }
        $savings_deduction = SavingsDeduction::where('employee_id', $employeeId)->latest('id')->where('term', $term)->first();
        if ($savings_deduction) {
            $regularInputs[] = 'savings_deduction';
        }
        $taxDirectiveEntry = TaxDirectiveEntry::where('employee_id', $employeeId)->latest('id')->where('term', $term)->first();
        if ($taxDirectiveEntry) {
            $regularInputs[] = 'taxDirectiveEntry';
        }
        $employee = Employee::find($employeeId);
        return view('hrm.payroll.regularinputs', compact('employee', 'regularInputs', 'term'));
    }

    private function getEmployeeLeaveData($employeeId, $term)
    {
        $leaveData = [];
        $totalUnpaidLeave = 0;
        $workspaceId = getActiveWorkspace();
        $employee = Employee::find($employeeId);

        if (!$employee) {
            return ['leaveData' => [], 'totalUnpaidLeave' => 0];
        }

        try {
            $monthStartDate = Carbon::createFromFormat('Y-m-d', $term)->startOfMonth();
            $monthEndDate = Carbon::createFromFormat('Y-m-d', $term)->endOfMonth();
        } catch (\Exception $e) {
            return ['leaveData' => [], 'totalUnpaidLeave' => 0];
        }
        $entitlementPolicies = \App\Models\Hrm\EmployeeEntitlementPolicy::with(['leaveManagement', 'entitlementPolicy'])
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
            $paid_leave_taken = $summary['paid_leave'];
            $unpaid_leave_taken = $summary['unpaid_leave'];
            $totalUnpaidLeave += $unpaid_leave_taken;

            if ($remainingBalance > 0 || $leaveTakenThisTerm > 0) {
                $leaveData[] = [
                    'type' => $leaveManagement->leave_name,
                    'balance' => $remainingBalance > 0 ? $remainingBalance : 0,
                    'adjustment' => 0,
                    'taken' => $leaveTakenThisTerm,
                    'scheduled' => 0,
                    'paid_leave_taken' => $paid_leave_taken,
                    'unpaid_leave_taken' => $unpaid_leave_taken,
                ];
            }
        }
        return ['leaveData' => $leaveData, 'totalUnpaidLeave' => $totalUnpaidLeave];
    }
}
