<?php

namespace App\Http\Controllers\Hrm\Ess;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\Hrm\PaySlip;
use App\Models\Hrm\Payrun;
use Carbon\Carbon;
use App\Services\TaxCalculationService;

class EssFilingController extends Controller
{
    /**
     * Display a listing of the employee's bi-annual filings.
     */
    public function index()
    {
        $employee = Auth::guard('employee')->user();
        $biFilingData = $this->getEmployeeBiFilingData($employee->id);
        
        return view('hrm.ess.filing.index', compact('biFilingData'));
    }

    /**
     * Show detailed filing for a specific season.
     */
    public function show($season)
    {
        $employee = Auth::guard('employee')->user();
        
        try {
            $seasonDate = Carbon::parse($season);
            $year = $seasonDate->year;
        } catch (\Exception $e) {
            return redirect()->route('ess.filing')
                ->with('error', 'Invalid filing season.');
        }

        $seasonData = $this->getEmployeeSeasonData($employee->id, $season);
        
        if (!$seasonData) {
            return redirect()->route('ess.filing')
                ->with('error', 'No filing data available for this season.');
        }

        return view('hrm.ess.filing.show', compact('seasonData'));
    }

    /**
     * Download the employee's bi-filing PDF for a specific payslip.
     */
    public function downloadPdf($payslipId)
    {
        $employee = Auth::guard('employee')->user();
        
        try {
            $decryptedId = Crypt::decrypt($payslipId);
        } catch (\Exception $e) {
            return redirect()->route('ess.filing')
                ->with('error', 'Invalid reference.');
        }

        $payslip = PaySlip::with('employee_profile')
            ->where('id', $decryptedId)
            ->where('employee_id', $employee->id) // Ensure employee owns this payslip
            ->first();

        if (!$payslip || !$payslip->employee_profile) {
            return redirect()->route('ess.filing')
                ->with('error', 'Filing data not found.');
        }

        $payrun = Payrun::where('payslip_id', $payslip->id)->first();
        
        if (!$payrun) {
            return redirect()->route('ess.filing')
                ->with('error', 'Payrun data not found.');
        }

        $employeeData = $this->calculateEmployeeTaxData($payslip);

        $pdf = \PDF::loadView('hrm.filing.employee-bi-filing-pdf', compact('payslip', 'payrun', 'employeeData'));
        return $pdf->download('BiAnnualFiling_' . $employee->first_name . '_' . $payrun->term . '.pdf');
    }

    /**
     * Get bi-filing data for a specific employee grouped by filing seasons.
     */
    private function getEmployeeBiFilingData($employeeId)
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
                    'payruns' => $seasonDetails['payruns'],
                    'totals' => $seasonDetails['totals']
                ];
            }
        }

        return $biFilingData;
    }

    /**
     * Get detailed payrun information for a specific employee.
     */
    private function getPayrunDetailsForEmployee($payrunTerms, $employeeId)
    {
        $payrunDetails = [];
        $totals = [
            'gross_salary' => 0,
            'paye' => 0,
            'uif' => 0,
            'sdl' => 0,
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
                        'date' => $term,
                        'month_name' => Carbon::parse($term)->format('F Y'),
                        'payslip_id' => $payslip->id,
                        'payslip_id_encrypted' => Crypt::encrypt($payslip->id),
                        'gross_salary' => $taxData['gross_salary'],
                        'paye' => $taxData['paye'],
                        'uif' => $taxData['uif'],
                        'sdl' => $taxData['sdl'],
                        'net_salary' => $taxData['net_salary']
                    ];

                    $totals['gross_salary'] += $taxData['gross_salary'];
                    $totals['paye'] += $taxData['paye'];
                    $totals['uif'] += $taxData['uif'];
                    $totals['sdl'] += $taxData['sdl'];
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
     * Get employee season data for detailed view.
     */
    private function getEmployeeSeasonData($employeeId, $season)
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
            ->distinct('term')
            ->orderBy('term', 'ASC')
            ->pluck('term');

        $seasonPayruns = $payrunTerms->filter(function ($term) use ($seasonStart, $seasonEnd) {
            return $term >= $seasonStart && $term <= $seasonEnd;
        });

        if ($seasonPayruns->isEmpty()) {
            return null;
        }

        $seasonDetails = $this->getPayrunDetailsForEmployee($seasonPayruns, $employeeId);

        return [
            'season' => $season,
            'season_label' => 'Filing Season ' . ($year - 1) . '/' . $year,
            'period_start' => Carbon::create($year, 3, 1)->format('d M Y'),
            'period_end' => Carbon::create($year, 8, 31)->format('d M Y'),
            'payruns' => $seasonDetails['payruns'],
            'totals' => $seasonDetails['totals']
        ];
    }

    /**
     * Calculate tax data for individual employee payslip.
     */
    private function calculateEmployeeTaxData($payslip)
    {
        $grossSalary = $payslip->basic_salary + $payslip->allowance + $payslip->other_payment;
        $paye = $this->extractPAYEFromPayslip($payslip);
        $uif = $this->extractUIFFromPayslip($payslip);
        $sdl = $this->extractSDLFromPayslip($payslip);

        return [
            'gross_salary' => $grossSalary,
            'paye' => $paye,
            'uif' => $uif,
            'sdl' => $sdl,
            'total_deductions' => $paye + $uif + $sdl,
            'net_salary' => $grossSalary - ($paye + $uif + $sdl)
        ];
    }

    /**
     * Extract PAYE amount from payslip.
     * Uses shared TaxCalculationService for consistent tax calculations
     */
    private function extractPAYEFromPayslip($payslip)
    {
        // Calculate taxable income from payslip components
        $taxableIncome = $payslip->basic_salary + $payslip->allowance + $payslip->other_payment;
        
        // Use shared tax calculation service
        return TaxCalculationService::calculateMonthlyPAYE(
            $payslip->employee_id,
            $taxableIncome,
            $payslip->salary_month
        );
    }

    /**
     * Extract UIF amount from payslip.
     * Calculates UIF as 1% of gross salary, capped at maximum
     */
    private function extractUIFFromPayslip($payslip)
    {
        $grossSalary = $payslip->basic_salary + $payslip->allowance + $payslip->other_payment;
        $uif = $grossSalary * 0.01;
        return min($uif, 177.12);
    }

    /**
     * Extract SDL amount from payslip (usually employer contribution, but may appear on payslip).
     * SDL is typically not deducted from employee salary, so returns 0 for ESS
     */
    private function extractSDLFromPayslip($payslip)
    {
        // SDL is an employer contribution, not employee deduction
        // Return 0 for employee self-service context
        return 0;
    }
}