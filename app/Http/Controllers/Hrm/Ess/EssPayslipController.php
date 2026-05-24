<?php

namespace App\Http\Controllers\Hrm\Ess;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\Hrm\PaySlip;
use App\Http\Controllers\Hrm\PaySlipController;
use App\Models\WorkSpace;
use Carbon\Carbon;

class EssPayslipController extends Controller
{
    /**
     * Display a listing of the employee's payslips.
     */
    public function index(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        
        // Get filter parameters
        $year = $request->get('year', date('Y'));
        
        // Generate year options (last 5 years)
        $years = [];
        for ($i = 0; $i <= 5; $i++) {
            $y = date('Y') - $i;
            $years[$y] = $y;
        }
        
        // Get only finalized payslips (status = 2)
        $payslips = PaySlip::where('employee_id', $employee->id)
            ->whereYear('salary_month', $year)
            ->where('status', 2) // Only finalized payslips
            ->orderBy('salary_month', 'desc')
            ->get();
        
        // Calculate totals for the year
        $yearlyStats = [
            'total_gross' => 0,
            'total_deductions' => 0,
            'total_net' => 0,
        ];
        
        foreach ($payslips as $payslip) {
            $yearlyStats['total_net'] += $payslip->net_payble ?? 0;
            $yearlyStats['total_gross'] += $payslip->basic_salary ?? 0;
            
            // Calculate deductions from JSON data - ensure we have arrays
            $deductions = json_decode($payslip->saturation_deduction, true);
            $loans = json_decode($payslip->loan, true);
            
            if (is_array($deductions)) {
                foreach ($deductions as $deduction) {
                    if (is_array($deduction)) {
                        $yearlyStats['total_deductions'] += $deduction['amount'] ?? 0;
                    }
                }
            }
            if (is_array($loans)) {
                foreach ($loans as $loan) {
                    if (is_array($loan)) {
                        $yearlyStats['total_deductions'] += $loan['amount'] ?? 0;
                    }
                }
            }
        }

        return view('hrm.ess.payslips.index', compact('payslips', 'years', 'year', 'yearlyStats'));
    }

    /**
     * Display the specified payslip.
     */
    public function show($id)
    {
        $employee = Auth::guard('employee')->user();
        
        try {
            $payslipId = Crypt::decrypt($id);
        } catch (\Exception $e) {
            return redirect()->route('ess.payslips')
                ->with('error', 'Invalid payslip reference.');
        }
        
        $payslip = PaySlip::where('id', $payslipId)
            ->where('employee_id', $employee->id) // Ensure employee owns this payslip
            ->first();
        
        if (!$payslip) {
            return redirect()->route('ess.payslips')
                ->with('error', 'Payslip not found.');
        }
        
        // Get payslip details
        $payslipDetail = $this->getPayslipDetails($payslip);
        
        return view('hrm.ess.payslips.show', compact('payslip', 'payslipDetail', 'employee'));
    }

    /**
     * Download the payslip as PDF - uses the same calculation methods as admin PaySlipController.
     * This ensures the ESS payslip matches exactly what the admin sees.
     */
    public function download($id)
    {
        $employee = Auth::guard('employee')->user();
        
        try {
            $payslipId = Crypt::decrypt($id);
        } catch (\Exception $e) {
            return redirect()->route('ess.payslips')
                ->with('error', 'Invalid payslip reference.');
        }
        
        $payslip = PaySlip::where('id', $payslipId)
            ->where('employee_id', $employee->id) // Ensure employee owns this payslip
            ->where('status', 2) // Only finalized payslips
            ->first();
        
        if (!$payslip) {
            return redirect()->route('ess.payslips')
                ->with('error', 'Payslip not found.');
        }
        
        // Get workspace and company user for ESS context
        $workspaceId = $employee->workspace_id;
        $workspace = WorkSpace::find($workspaceId);
        $companyUserId = $workspace ? $workspace->created_by : null;
        
        // Pass salary_month directly as term - must match the exact format stored in
        // basic_salaries.term (which mirrors pay_slips.salary_month value)
        $term = $payslip->salary_month;
        
        // Use the PaySlipController's preview method with ESS context
        // This ensures we use the same calculations and PDF template as admin
        $paySlipController = app(PaySlipController::class);
        return $paySlipController->preview($employee->id, $term, $workspaceId, $companyUserId);
    }

    /**
     * Get detailed payslip breakdown.
     */
    private function getPayslipDetails(PaySlip $payslip): array
    {
        $details = [
            'basic_salary' => $payslip->basic_salary ?? 0,
            'net_pay' => $payslip->net_payble ?? 0,
            'allowances' => [],
            'commissions' => [],
            'other_payments' => [],
            'overtimes' => [],
            'deductions' => [],
            'loans' => [],
            'company_contributions' => [],
            'total_earnings' => 0,
            'total_deductions' => 0,
        ];
        
        // Parse allowances
        $allowances = json_decode($payslip->allowance, true);
        if (is_array($allowances)) {
            foreach ($allowances as $allowance) {
                if (!is_array($allowance)) continue;
                $amount = ($allowance['type'] ?? '') == 'percentage' 
                    ? (($allowance['amount'] ?? 0) * $details['basic_salary'] / 100) 
                    : ($allowance['amount'] ?? 0);
                
                $details['allowances'][] = [
                    'title' => $allowance['title'] ?? 'Allowance',
                    'amount' => $amount,
                ];
                $details['total_earnings'] += $amount;
            }
        }
        
        // Parse commissions
        $commissions = json_decode($payslip->commission, true);
        if (is_array($commissions)) {
            foreach ($commissions as $commission) {
                if (!is_array($commission)) continue;
                $amount = ($commission['type'] ?? '') == 'percentage' 
                    ? (($commission['amount'] ?? 0) * $details['basic_salary'] / 100) 
                    : ($commission['amount'] ?? 0);
                
                $details['commissions'][] = [
                    'title' => $commission['title'] ?? 'Commission',
                    'amount' => $amount,
                ];
                $details['total_earnings'] += $amount;
            }
        }
        
        // Parse other payments
        $otherPayments = json_decode($payslip->other_payment, true);
        if (is_array($otherPayments)) {
            foreach ($otherPayments as $payment) {
                if (!is_array($payment)) continue;
                $amount = ($payment['type'] ?? '') == 'percentage' 
                    ? (($payment['amount'] ?? 0) * $details['basic_salary'] / 100) 
                    : ($payment['amount'] ?? 0);
                
                $details['other_payments'][] = [
                    'title' => $payment['title'] ?? 'Other Payment',
                    'amount' => $amount,
                ];
                $details['total_earnings'] += $amount;
            }
        }
        
        // Parse overtime
        $overtimes = json_decode($payslip->overtime, true);
        if (is_array($overtimes)) {
            foreach ($overtimes as $overtime) {
                if (!is_array($overtime)) continue;
                $amount = ($overtime['number_of_days'] ?? 0) * ($overtime['hours'] ?? 0) * ($overtime['rate'] ?? 0);
                
                $details['overtimes'][] = [
                    'title' => $overtime['title'] ?? 'Overtime',
                    'hours' => ($overtime['number_of_days'] ?? 0) * ($overtime['hours'] ?? 0),
                    'rate' => $overtime['rate'] ?? 0,
                    'amount' => $amount,
                ];
                $details['total_earnings'] += $amount;
            }
        }
        
        // Parse deductions
        $deductions = json_decode($payslip->saturation_deduction, true);
        if (is_array($deductions)) {
            foreach ($deductions as $deduction) {
                if (!is_array($deduction)) continue;
                $amount = ($deduction['type'] ?? '') == 'percentage' 
                    ? (($deduction['amount'] ?? 0) * $details['basic_salary'] / 100) 
                    : ($deduction['amount'] ?? 0);
                
                $details['deductions'][] = [
                    'title' => $deduction['title'] ?? 'Deduction',
                    'amount' => $amount,
                ];
                $details['total_deductions'] += $amount;
            }
        }
        
        // Parse loans
        $loans = json_decode($payslip->loan, true);
        if (is_array($loans)) {
            foreach ($loans as $loan) {
                if (!is_array($loan)) continue;
                $amount = ($loan['type'] ?? '') == 'percentage' 
                    ? (($loan['amount'] ?? 0) * $details['basic_salary'] / 100) 
                    : ($loan['amount'] ?? 0);
                
                $details['loans'][] = [
                    'title' => $loan['title'] ?? 'Loan Repayment',
                    'amount' => $amount,
                ];
                $details['total_deductions'] += $amount;
            }
        }
        
        // Add basic salary to total earnings
        $details['total_earnings'] += $details['basic_salary'];
        
        return $details;
    }
}
