<?php

namespace App\Http\Controllers\Hrm\Api\Ess;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use App\Models\Hrm\PaySlip;
use App\Models\Hrm\Payrun;
use App\Services\TaxCalculationService;
use App\Models\TaxYear;
use Carbon\Carbon;

class EssTaxCertificateApiController extends Controller
{
    /**
     * Get list of tax certificates (bi-annual filings) grouped by season
     * 
     * @response 200 {
     *   "status": 1,
     *   "message": "Tax certificates retrieved successfully",
     *   "data": {
     *     "seasons": [...]
     *   }
     * }
     */
    public function index(Request $request)
    {
        try {
            $employee = $request->ess_employee;
            $biFilingData = $this->getEmployeeBiFilingData($employee->id);

            return response()->json([
                'status' => 1,
                'message' => 'Tax certificates retrieved successfully',
                'data' => [
                    'seasons' => $biFilingData,
                    'current_tax_year' => $this->getCurrentTaxYear(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to retrieve tax certificates',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get detailed filing for a specific season
     * 
     * @urlParam season string required The season date (YYYY-MM-DD). Example: 2025-02-28
     */
    public function show(Request $request, $season)
    {
        try {
            $employee = $request->ess_employee;
            
            try {
                $seasonDate = Carbon::parse($season);
                $year = $seasonDate->year;
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Invalid filing season format',
                    'error_code' => 'INVALID_SEASON'
                ], 422);
            }

            $seasonData = $this->getEmployeeSeasonData($employee->id, $season);
            
            if (!$seasonData) {
                return response()->json([
                    'status' => 0,
                    'message' => 'No filing data available for this season',
                    'error_code' => 'NO_DATA'
                ], 404);
            }

            return response()->json([
                'status' => 1,
                'message' => 'Tax certificate details retrieved successfully',
                'data' => $seasonData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to retrieve tax certificate details',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Download tax certificate PDF
     * 
     * @urlParam id string required Encrypted payslip ID
     */
    public function download(Request $request, $id)
    {
        try {
            $employee = $request->ess_employee;

            $payslip = PaySlip::with('employee_profile')
                ->where('id', $id)
                ->where('employee_id', $employee->id)
                ->first();

            if (!$payslip || !$payslip->employee_profile) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Tax certificate not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }

            $payrun = Payrun::where('payslip_id', $payslip->id)->first();
            
            if (!$payrun) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Payrun data not found',
                    'error_code' => 'PAYRUN_NOT_FOUND'
                ], 404);
            }

            $employeeData = $this->calculateEmployeeTaxData($payslip);

            $pdf = \PDF::loadView('hrm.filing.employee-bi-filing-pdf', compact('payslip', 'payrun', 'employeeData'));
            return $pdf->download('TaxCertificate_' . $employee->first_name . '_' . $payrun->term . '.pdf');

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to download tax certificate',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get bi-filing data for a specific employee grouped by filing seasons
     */
    private function getEmployeeBiFilingData($employeeId): array
    {
        $payslips = PaySlip::where('employee_id', $employeeId)
            ->where('status', 2) // Only finalized payslips
            ->pluck('id');
            
        if ($payslips->isEmpty()) {
            return [];
        }

        $payrunTerms = Payrun::whereIn('payslip_id', $payslips)
            ->distinct('term')
            ->orderBy('term', 'DESC')
            ->pluck('term');

        $biFilingData = [];
        $currentYear = date('Y');

        // Get last 5 years of filing seasons
        for ($year = $currentYear; $year >= $currentYear - 4; $year--) {
            $seasonStart = ($year) . '-02-28';
            $seasonEnd = $year . '-08-31';

            $seasonPayruns = $payrunTerms->filter(function ($term) use ($seasonStart, $seasonEnd) {
                return $term >= $seasonStart && $term <= $seasonEnd;
            });

            if ($seasonPayruns->count() > 0) {
                $seasonDetails = $this->getPayrunDetailsForEmployee($seasonPayruns, $employeeId);
                
                $biFilingData[] = [
                    'season' => $year . '-02-28',
                    'season_label' => 'Filing Season ' . ($year - 1) . '/' . $year,
                    'tax_year' => ($year - 1) . '/' . $year,
                    'payrun_count' => $seasonDetails['totals']['payrun_count'],
                    'totals' => [
                        'gross_salary' => round($seasonDetails['totals']['gross_salary'], 2),
                        'paye' => round($seasonDetails['totals']['paye'], 2),
                        'uif' => round($seasonDetails['totals']['uif'], 2),
                        'net_salary' => round($seasonDetails['totals']['net_salary'], 2),
                    ],
                ];
            }
        }

        return $biFilingData;
    }

    /**
     * Get detailed payrun information for a specific employee
     */
    private function getPayrunDetailsForEmployee($payrunTerms, $employeeId): array
    {
        $payrunDetails = [];
        $totals = [
            'gross_salary' => 0,
            'paye' => 0,
            'uif' => 0,
            'net_salary' => 0,
            'payrun_count' => 0
        ];

        foreach ($payrunTerms as $term) {
            $payslips = PaySlip::where('employee_id', $employeeId)
                ->where('status', 2)
                ->whereHas('payrun', function($query) use ($term) {
                    $query->where('term', $term);
                })
                ->with('employee_profile')
                ->get();

            foreach ($payslips as $payslip) {
                if ($payslip->employee_profile) {
                    $taxData = $this->calculateEmployeeTaxData($payslip);
                    
                    $payrunDetails[] = [
                        'id' => $payslip->id,
                        'date' => $term,
                        'month_name' => Carbon::parse($term)->format('F Y'),
                        'payslip_id' => $payslip->id,
                        'gross_salary' => (float) $taxData['gross_salary'],
                        'paye' => (float) $taxData['paye'],
                        'uif' => (float) $taxData['uif'],
                        'net_salary' => (float) $taxData['net_salary'],
                    ];

                    $totals['gross_salary'] += $taxData['gross_salary'];
                    $totals['paye'] += $taxData['paye'];
                    $totals['uif'] += $taxData['uif'];
                    $totals['net_salary'] += $taxData['net_salary'];
                    $totals['payrun_count']++;
                }
            }
        }

        return [
            'payruns' => $payrunDetails,
            'totals' => $totals
        ];
    }

    /**
     * Get employee season data for detailed view
     */
    private function getEmployeeSeasonData($employeeId, $season): ?array
    {
        try {
            $year = Carbon::parse($season)->year;
        } catch (\Exception $e) {
            return null;
        }

        $seasonStart = $year . '-02-28';
        $seasonEnd = $year . '-08-31';

        $payslips = PaySlip::where('employee_id', $employeeId)
            ->where('status', 2)
            ->pluck('id');

        if ($payslips->isEmpty()) {
            return null;
        }

        $payrunTerms = Payrun::whereIn('payslip_id', $payslips)
            ->whereBetween('term', [$seasonStart, $seasonEnd])
            ->distinct('term')
            ->orderBy('term', 'DESC')
            ->pluck('term');

        if ($payrunTerms->isEmpty()) {
            return null;
        }

        $seasonDetails = $this->getPayrunDetailsForEmployee($payrunTerms, $employeeId);

        return [
            'season' => $season,
            'season_label' => 'Filing Season ' . ($year - 1) . '/' . $year,
            'tax_year' => ($year - 1) . '/' . $year,
            'period' => [
                'start' => Carbon::parse($seasonStart)->format('d M Y'),
                'end' => Carbon::parse($seasonEnd)->format('d M Y'),
            ],
            'payruns' => $seasonDetails['payruns'],
            'totals' => [
                'gross_salary' => round($seasonDetails['totals']['gross_salary'], 2),
                'paye' => round($seasonDetails['totals']['paye'], 2),
                'uif' => round($seasonDetails['totals']['uif'], 2),
                'net_salary' => round($seasonDetails['totals']['net_salary'], 2),
                'payrun_count' => $seasonDetails['totals']['payrun_count'],
            ],
        ];
    }

    /**
     * Calculate tax data from payslip (same logic as ESS web portal)
     */
    private function calculateEmployeeTaxData(PaySlip $payslip): array
    {
        // Calculate gross salary including allowances and other payments (same as web portal)
        $grossSalary = (float) ($payslip->basic_salary ?? 0) 
                     + (float) ($payslip->allowance ?? 0) 
                     + (float) ($payslip->other_payment ?? 0);
        
        // Calculate PAYE using the tax calculation service (same as web portal)
        $paye = TaxCalculationService::calculateMonthlyPAYE(
            $payslip->employee_id,
            $grossSalary,
            $payslip->salary_month
        );
        
        // Calculate UIF from locked tax year rates
        $taxYear = TaxYear::resolveForTerm($payslip->salary_month);
        $uifRate = $taxYear ? $taxYear->uif_rate : 0.01;
        $uifCeiling = $taxYear ? $taxYear->uif_ceiling : 177.12;
        $uif = min($grossSalary * $uifRate, $uifCeiling);
        
        return [
            'gross_salary' => round($grossSalary, 2),
            'paye' => round($paye, 2),
            'uif' => round($uif, 2),
            'sdl' => 0, // Required for PDF view, not shown in API responses
            'total_deductions' => round($paye + $uif, 2),
            'net_salary' => round($grossSalary - ($paye + $uif), 2),
        ];
    }

    /**
     * Get current tax year
     */
    private function getCurrentTaxYear(): string
    {
        $now = Carbon::now();
        $year = $now->year;
        
        // South African tax year runs from 1 March to 28 February
        if ($now->month >= 3) {
            return $year . '/' . ($year + 1);
        } else {
            return ($year - 1) . '/' . $year;
        }
    }
}
