<?php

namespace App\Http\Controllers\Hrm\Api\Ess;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Hrm\PaySlip;
use App\Models\WorkSpace;
use App\Http\Controllers\Hrm\PaySlipController;
use Carbon\Carbon;

class EssPayslipApiController extends Controller
{
    /**
     * Get list of employee's payslips
     * 
     * @queryParam year integer Filter by year. Example: 2025
     * @queryParam page integer Page number for pagination. Example: 1
     * @queryParam per_page integer Items per page. Example: 12
     * 
     * @response 200 {
     *   "status": 1,
     *   "message": "Payslips retrieved successfully",
     *   "data": {
     *     "payslips": [...],
     *     "pagination": {...},
     *     "available_years": [2025, 2024, 2023]
     *   }
     * }
     */
    public function index(Request $request)
    {
        try {
            $employee = $request->ess_employee;
            
            // Get filter parameters
            $year = $request->get('year', date('Y'));
            $perPage = min($request->get('per_page', 12), 50); // Max 50 items per page
            
            // Get only finalized payslips (status = 2)
            $query = PaySlip::where('employee_id', $employee->id)
                ->where('status', 2);
            
            if ($year) {
                $query->whereYear('salary_month', $year);
            }
            
            $payslips = $query->orderBy('salary_month', 'desc')
                ->paginate($perPage);
            
            // Format payslips
            $formattedPayslips = $payslips->map(function ($payslip) {
                return $this->formatPayslipSummary($payslip);
            });
            
            // Get available years for filter
            $availableYears = PaySlip::where('employee_id', $employee->id)
                ->where('status', 2)
                ->selectRaw('YEAR(salary_month) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            return response()->json([
                'status' => 1,
                'message' => 'Payslips retrieved successfully',
                'data' => [
                    'payslips' => $formattedPayslips,
                    'pagination' => [
                        'current_page' => $payslips->currentPage(),
                        'last_page' => $payslips->lastPage(),
                        'per_page' => $payslips->perPage(),
                        'total' => $payslips->total(),
                        'has_more' => $payslips->hasMorePages(),
                    ],
                    'filter' => [
                        'year' => (int) $year,
                        'available_years' => $availableYears,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to retrieve payslips',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Download payslip as PDF
     * 
     * @urlParam id string required Encrypted payslip ID
     */
    public function download(Request $request, $id)
    {
        try {
            $employee = $request->ess_employee;
            
            $payslip = PaySlip::where('id', $id)
                ->where('employee_id', $employee->id)
                ->where('status', 2)
                ->first();
            
            if (!$payslip) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Payslip not found',
                    'error_code' => 'PAYSLIP_NOT_FOUND'
                ], 404);
            }
            
            // Get workspace and company user for ESS context
            $workspaceId = $employee->workspace_id;
            $workspace = WorkSpace::find($workspaceId);
            $companyUserId = $workspace ? $workspace->created_by : null;
            
            // Pass salary_month directly as term - must match the exact format stored in
            // basic_salaries.term (which mirrors pay_slips.salary_month value)
            $term = $payslip->salary_month;
            
            // Use the PaySlipController's preview method
            $paySlipController = app(PaySlipController::class);
            return $paySlipController->preview($employee->id, $term, $workspaceId, $companyUserId);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to download payslip',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Format payslip summary for list view
     */
    private function formatPayslipSummary(PaySlip $payslip): array
    {
        return [
            'id' => $payslip->id,
            'salary_month' => $payslip->salary_month,
            'month_name' => Carbon::parse($payslip->salary_month)->format('F Y'),
            'created_at' => $payslip->created_at ? $payslip->created_at->toIso8601String() : null,
        ];
    }

    /**
     * Format detailed payslip breakdown
     */
    private function formatPayslipDetails(PaySlip $payslip): array
    {
        $basicSalary = (float) ($payslip->basic_salary ?? 0);
        
        $details = [
            'id' => $payslip->id,
            'salary_month' => $payslip->salary_month,
            'month_name' => Carbon::parse($payslip->salary_month)->format('F Y'),
            'status' => 'Finalized',
            
            // Summary
            'basic_salary' => $basicSalary,
            'net_payable' => (float) ($payslip->net_payble ?? 0),
            
            // Earnings breakdown
            'earnings' => [
                'basic_salary' => $basicSalary,
                'allowances' => [],
                'commissions' => [],
                'other_payments' => [],
                'overtime' => [],
            ],
            
            // Deductions breakdown
            'deductions' => [
                'statutory' => [],
                'loans' => [],
                'other' => [],
            ],
            
            // Company contributions (informational)
            'company_contributions' => [],
            
            // Totals
            'totals' => [
                'total_earnings' => $basicSalary,
                'total_deductions' => 0,
                'net_pay' => (float) ($payslip->net_payble ?? 0),
            ],
        ];
        
        // Parse allowances
        $allowances = json_decode($payslip->allowance, true);
        if (is_array($allowances)) {
            foreach ($allowances as $allowance) {
                if (!is_array($allowance)) continue;
                $amount = ($allowance['type'] ?? '') == 'percentage' 
                    ? (($allowance['amount'] ?? 0) * $basicSalary / 100) 
                    : ($allowance['amount'] ?? 0);
                
                $details['earnings']['allowances'][] = [
                    'title' => $allowance['title'] ?? 'Allowance',
                    'amount' => (float) $amount,
                    'type' => $allowance['type'] ?? 'fixed',
                ];
                $details['totals']['total_earnings'] += $amount;
            }
        }
        
        // Parse commissions
        $commissions = json_decode($payslip->commission, true);
        if (is_array($commissions)) {
            foreach ($commissions as $commission) {
                if (!is_array($commission)) continue;
                $amount = ($commission['type'] ?? '') == 'percentage' 
                    ? (($commission['amount'] ?? 0) * $basicSalary / 100) 
                    : ($commission['amount'] ?? 0);
                
                $details['earnings']['commissions'][] = [
                    'title' => $commission['title'] ?? 'Commission',
                    'amount' => (float) $amount,
                ];
                $details['totals']['total_earnings'] += $amount;
            }
        }
        
        // Parse other payments
        $otherPayments = json_decode($payslip->other_payment, true);
        if (is_array($otherPayments)) {
            foreach ($otherPayments as $payment) {
                if (!is_array($payment)) continue;
                $amount = ($payment['type'] ?? '') == 'percentage' 
                    ? (($payment['amount'] ?? 0) * $basicSalary / 100) 
                    : ($payment['amount'] ?? 0);
                
                $details['earnings']['other_payments'][] = [
                    'title' => $payment['title'] ?? 'Other Payment',
                    'amount' => (float) $amount,
                ];
                $details['totals']['total_earnings'] += $amount;
            }
        }
        
        // Parse overtime
        $overtimes = json_decode($payslip->overtime, true);
        if (is_array($overtimes)) {
            foreach ($overtimes as $overtime) {
                if (!is_array($overtime)) continue;
                $amount = ($overtime['number_of_days'] ?? 0) * ($overtime['hours'] ?? 0) * ($overtime['rate'] ?? 0);
                
                $details['earnings']['overtime'][] = [
                    'title' => $overtime['title'] ?? 'Overtime',
                    'hours' => (float) (($overtime['number_of_days'] ?? 0) * ($overtime['hours'] ?? 0)),
                    'rate' => (float) ($overtime['rate'] ?? 0),
                    'amount' => (float) $amount,
                ];
                $details['totals']['total_earnings'] += $amount;
            }
        }
        
        // Parse statutory deductions (PAYE, UIF, SDL etc.)
        // These would typically be in payrun or calculated fields
        if ($payslip->paye ?? false) {
            $details['deductions']['statutory'][] = [
                'title' => 'PAYE',
                'amount' => (float) $payslip->paye,
            ];
            $details['totals']['total_deductions'] += (float) $payslip->paye;
        }
        if ($payslip->uif ?? false) {
            $details['deductions']['statutory'][] = [
                'title' => 'UIF',
                'amount' => (float) $payslip->uif,
            ];
            $details['totals']['total_deductions'] += (float) $payslip->uif;
        }
        
        // Parse other deductions
        $saturationDeductions = json_decode($payslip->saturation_deduction, true);
        if (is_array($saturationDeductions)) {
            foreach ($saturationDeductions as $deduction) {
                if (!is_array($deduction)) continue;
                $amount = ($deduction['type'] ?? '') == 'percentage' 
                    ? (($deduction['amount'] ?? 0) * $basicSalary / 100) 
                    : ($deduction['amount'] ?? 0);
                
                $details['deductions']['other'][] = [
                    'title' => $deduction['title'] ?? 'Deduction',
                    'amount' => (float) $amount,
                ];
                $details['totals']['total_deductions'] += $amount;
            }
        }
        
        // Parse loans
        $loans = json_decode($payslip->loan, true);
        if (is_array($loans)) {
            foreach ($loans as $loan) {
                if (!is_array($loan)) continue;
                $amount = ($loan['type'] ?? '') == 'percentage' 
                    ? (($loan['amount'] ?? 0) * $basicSalary / 100) 
                    : ($loan['amount'] ?? 0);
                
                $details['deductions']['loans'][] = [
                    'title' => $loan['title'] ?? 'Loan Repayment',
                    'amount' => (float) $amount,
                ];
                $details['totals']['total_deductions'] += $amount;
            }
        }
        
        // Parse company contributions
        $contributions = json_decode($payslip->company_contribution ?? '[]', true);
        if (is_array($contributions)) {
            foreach ($contributions as $contribution) {
                if (!is_array($contribution)) continue;
                $details['company_contributions'][] = [
                    'title' => $contribution['title'] ?? 'Contribution',
                    'amount' => (float) ($contribution['amount'] ?? 0),
                ];
            }
        }
        
        return $details;
    }
}
