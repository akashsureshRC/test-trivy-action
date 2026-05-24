<?php

namespace App\Services;

use App\Models\Hrm\Allowance;
use App\Models\Hrm\Commission;
use App\Models\Hrm\CompanyContribution;
use App\Models\Hrm\Employee;
use App\Models\Hrm\OtherPayment;
use App\Models\Hrm\Overtime;
use App\Models\Hrm\PaySlip;
use App\Models\Hrm\SaturationDeduction;
use App\Models\User;

/**
 * PayrollHelperService
 * 
 * This service contains helper methods for payroll calculations.
 * These methods were migrated from the legacy Employee model.
 */
class PayrollHelperService
{
    /**
     * Location types for employee addresses
     */
    public static array $location_type = [
        '' => 'Select Location Type',
        'residential'  => 'Residential',
        'postal'       => 'Postal',
        'work_address' => 'Work Address',
    ];

    /**
     * Format employee ID with company prefix
     */
    public static function employeeIdFormat($number): string
    {
        $company_settings = getCompanyAllSetting();
        $employee_prefix = !empty($company_settings['employee_prefix']) ? $company_settings['employee_prefix'] : '#EMP000';
        return $employee_prefix . sprintf("%05d", $number);
    }

    /**
     * Get employee profile by employee_id field
     * 
     * @param int|string $employeeId The employee_id field value
     * @return Employee|null
     */
    public static function GetEmployeeByEmp($employeeId): ?Employee
    {
        return Employee::where('employee_id', $employeeId)->first();
    }

    /**
     * Get employee profile by id
     * 
     * @param int $id The employee profile id
     * @return Employee|null
     */
    public static function getEmployee($id): ?Employee
    {
        return Employee::find($id);
    }

    /**
     * Get allowances for an employee as JSON
     */
    public static function allowance($employeeId): string
    {
        $allowances = Allowance::where('employee_id', '=', $employeeId)->get();
        return json_encode($allowances);
    }

    /**
     * Get commissions for an employee as JSON
     */
    public static function commission($employeeId): string
    {
        $commissions = Commission::where('employee_id', '=', $employeeId)->get();
        return json_encode($commissions);
    }

    /**
     * Get loans for an employee as JSON (feature removed)
     */
    public static function loan($employeeId): string
    {
        return json_encode([]);
    }

    /**
     * Get saturation deductions for an employee as JSON
     */
    public static function saturation_deduction($employeeId): string
    {
        $saturation_deductions = SaturationDeduction::where('employee_id', '=', $employeeId)->get();
        return json_encode($saturation_deductions);
    }

    /**
     * Get other payments for an employee as JSON
     */
    public static function other_payment($employeeId): string
    {
        $other_payments = OtherPayment::where('employee_id', '=', $employeeId)->get();
        return json_encode($other_payments);
    }

    /**
     * Get overtime records for an employee as JSON
     */
    public static function overtime($employeeId): string
    {
        $over_times = Overtime::where('employee_id', '=', $employeeId)->get();
        return json_encode($over_times);
    }

    /**
     * Get company contributions for an employee as JSON
     */
    public static function companycontribution($employeeId): string
    {
        $company_contributions = CompanyContribution::where('employee_id', '=', $employeeId)->get();
        return json_encode($company_contributions);
    }

    /**
     * Get detailed payslip information for an employee
     */
    public static function employeePayslipDetail($employeeId, $month): array
    {
        $payslip_data = PaySlip::where('employee_id', $employeeId)->where('salary_month', $month)->first();
        $totalAllowance = 0;
        $totalCommission = 0;
        $totalotherpayment = 0;
        $ot = 0;
        $totalCompanyContribution = 0;
        $totalloan = 0;
        $totaldeduction = 0;

        if (!empty($payslip_data)) {
            // allowance
            $allowances = json_decode($payslip_data->allowance);
            if (is_array($allowances) || is_object($allowances)) {
                foreach ($allowances as $allowance) {
                    if ($allowance->type == 'percentage') {
                        $empall = $allowance->amount * $payslip_data->basic_salary / 100;
                    } else {
                        $empall = $allowance->amount;
                    }
                    $totalAllowance += $empall;
                }
            }

            // commission
            $commissions = json_decode($payslip_data->commission);
            if (is_array($commissions) || is_object($commissions)) {
                foreach ($commissions as $commission) {
                    if ($commission->type == 'percentage') {
                        $empcom = $commission->amount * $payslip_data->basic_salary / 100;
                    } else {
                        $empcom = $commission->amount;
                    }
                    $totalCommission += $empcom;
                }
            }

            // otherpayment
            $otherpayments = json_decode($payslip_data->other_payment);
            if (is_array($otherpayments) || is_object($otherpayments)) {
                foreach ($otherpayments as $otherpayment) {
                    if ($otherpayment->type == 'percentage') {
                        $empotherpay = $otherpayment->amount * $payslip_data->basic_salary / 100;
                    } else {
                        $empotherpay = $otherpayment->amount;
                    }
                    $totalotherpayment += $empotherpay;
                }
            }

            // overtime
            $overtimes = json_decode($payslip_data->overtime);
            if (is_array($overtimes) || is_object($overtimes)) {
                foreach ($overtimes as $overtime) {
                    $OverTime = $overtime->number_of_days * $overtime->hours * $overtime->rate;
                    $ot += $OverTime;
                }
            }

            // companycontribution
            $company_contributions = json_decode($payslip_data->company_contribution);
            if (is_array($company_contributions) || is_object($company_contributions)) {
                foreach ($company_contributions as $company_contribution) {
                    if ($company_contribution->type == 'percentage') {
                        $empall = $company_contribution->amount * $payslip_data->basic_salary / 100;
                    } else {
                        $empall = $company_contribution->amount;
                    }
                    $totalCompanyContribution += $empall;
                }
            }

            // loan
            $loans = json_decode($payslip_data->loan);
            if (is_array($loans) || is_object($loans)) {
                foreach ($loans as $loan) {
                    if ($loan->type == 'percentage') {
                        $emploan = $loan->amount * $payslip_data->basic_salary / 100;
                    } else {
                        $emploan = $loan->amount;
                    }
                    $totalloan += $emploan;
                }
            }

            // saturation_deduction
            $deductions = json_decode($payslip_data->saturation_deduction);
            if (is_array($deductions) || is_object($deductions)) {
                foreach ($deductions as $deduction) {
                    if ($deduction->type == 'percentage') {
                        $empdeduction = $deduction->amount * $payslip_data->basic_salary / 100;
                    } else {
                        $empdeduction = $deduction->amount;
                    }
                    $totaldeduction += $empdeduction;
                }
            }

            $TotalEarning = $totalAllowance + $totalCommission + $totalCompanyContribution + $totalotherpayment + $ot + (!empty($payslip_data->basic_salary) ? $payslip_data->basic_salary : 0);
            // Use frozen PAYE if available, otherwise fall back to legacy tax_bracket calculation
            $taxAmount = ($payslip_data->paye_amount !== null)
                ? (float) $payslip_data->paye_amount
                : ($TotalEarning * $payslip_data->tax_bracket) / 100;

            $taxable_earning = $totalAllowance + $totalCommission + $totalCompanyContribution + $totalotherpayment + $ot + (!empty($payslip_data->basic_salary) ? $payslip_data->basic_salary : 0) - $totalloan - $totaldeduction;

            $payslip['payslip'] = $payslip_data;
            $payslip['totalEarning'] = $totalAllowance + $totalCommission + $totalotherpayment + $ot + $totalCompanyContribution + (!empty($payslip_data->basic_salary) ? $payslip_data->basic_salary : 0);
            $payslip['taxable_earning'] = $taxable_earning;
            $payslip['tax_rate'] = $payslip_data->tax_bracket;
            $payslip['tax_amount'] = $taxAmount;
            $payslip['totalDeduction'] = $totalloan + $totaldeduction;

            $payslip['allowance'] = $totalAllowance;
            $payslip['commission'] = $totalCommission;
            $payslip['other_payment'] = $totalotherpayment;
            $payslip['overtime'] = $ot;
            $payslip['company_contribution'] = $totalCompanyContribution;
            $payslip['loan'] = $totalloan;
            $payslip['saturation_deduction'] = $totaldeduction;
        } else {
            $payslip['payslip'] = null;
            $payslip['totalEarning'] = 0;
            $payslip['taxable_earning'] = 0;
            $payslip['tax_rate'] = 0;
            $payslip['tax_amount'] = 0;
            $payslip['totalDeduction'] = 0;

            $payslip['allowance'] = 0;
            $payslip['commission'] = 0;
            $payslip['other_payment'] = 0;
            $payslip['overtime'] = 0;
            $payslip['company_contribution'] = 0;
            $payslip['loan'] = 0;
            $payslip['saturation_deduction'] = 0;
        }

        return $payslip;
    }

    /**
     * Calculate payroll data for multiple months
     */
    public static function PayrollCalculation($EmpID = null, $months = null, $type = null): array
    {
        if (!empty($EmpID) && !empty($type) && count($months) > 0) {
            $data = [];
            foreach ($months as $key => $month) {
                $payslip_data = self::employeePayslipDetail($EmpID, $month);
                $data[] = $payslip_data[$type];
            }
            $data[] = array_sum($data);
            return $data;
        } else {
            return [];
        }
    }
}
