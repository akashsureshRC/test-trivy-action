<?php

namespace App\Http\Controllers\Hrm;

use App\Models\WorkSpace;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Hrm\PaySlip;
use App\Models\Hrm\Payrun;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;
use App\Models\Hrm\AccommodationBenefit;
use App\Models\Hrm\MedicalCost;
use App\Models\Hrm\PensionFund;
use App\Models\Hrm\CompanyCar;
use App\Models\Hrm\Bursary;
use App\Models\Hrm\AnnualBonus;
use App\Models\Hrm\PayslipCommission;
use App\Models\Hrm\ExtraPay;
use App\Models\Hrm\OnceOffCommission;
use App\Models\Hrm\TravelAllowance;
use App\Models\Hrm\PhoneAllowance;
use App\Models\Hrm\ComputerAllowance;
use App\Models\Hrm\Country;
use App\Models\Hrm\ToolAllowance;
use App\Models\Hrm\UniformAllowance;
use App\Models\Hrm\Employee;
use App\Models\Hrm\Province;
use App\Models\Hrm\Sic7Code;
use App\Services\TaxCalculationService;
use App\Models\TaxYear;
use Illuminate\Support\Facades\DB;

class FilingController extends Controller
{
    /**
     * Display a listing of monthly submissions.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $availableYears = $this->getAvailablePayrunYears();
        $selectedYear = $request->get('year');

        if (empty($selectedYear)) {
            $selectedYear = $availableYears[0] ?? date('Y');
        }

        $finalizedPayruns = $this->getFinalizedPayruns((int) $selectedYear);
        return view('hrm.filing.monthly-submission', compact('finalizedPayruns', 'availableYears', 'selectedYear'));
    }

    /**
     * Show the form for creating a new monthly submission.
     * @return Renderable
     */
    public function create(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Store a newly created monthly submission.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $request->validate([
            'month' => 'required|string',
        ]);

        $month = $request->month;
        $payslipIds = Payrun::where('term', $month)->pluck('payslip_id');
        $payslips = PaySlip::whereIn('id', $payslipIds)
            ->where('workspace', getActiveWorkspace())
            ->get();

        if ($payslips->isEmpty()) {
            return redirect()->back()->with('error', 'No payrun payslips found for the selected month.');
        }

        return redirect()->route('monthly-submission.index')->with('success', 'EMP201 submission processed successfully.');
    }

    /**
     * Display the specified monthly submission.
     * @param string $month
     * @return Renderable
     */
    public function show($month)
    {
        $payslipIds = Payrun::where('term', $month)->pluck('payslip_id');
        $payslips = PaySlip::whereIn('id', $payslipIds)
            ->where('workspace', getActiveWorkspace())
            ->get();

        $emp201Data = $this->calculateEMP201Data($payslips, $month);

        return view('hrm.filing.emp201-view', compact('payslips', 'emp201Data', 'month'));
    }

    /**
     * Display UIF declaration for a specific month
     * @param string $month
     * @return Renderable
     */
    public function showUIF($month)
    {
        $payslipIds = Payrun::where('term', $month)->pluck('payslip_id');
        $payslips = PaySlip::whereIn('id', $payslipIds)
            ->where('workspace', getActiveWorkspace())
            ->get();

        $uifData = $this->calculateUIFData($payslips, $month);

        return view('hrm.filing.uif-view', compact('payslips', 'uifData', 'month'));
    }

    /**
     * Export EMP201 as PDF
     */
    public function exportEMP201PDF($month)
    {
        $payslipIds = Payrun::where('term', $month)->pluck('payslip_id');
        $payslips = PaySlip::whereIn('id', $payslipIds)->where('workspace', getActiveWorkspace())->get();
        $emp201Data = $this->calculateEMP201Data($payslips, $month);

        $pdf = \PDF::loadView('hrm.filing.emp201-pdf', compact('payslips', 'emp201Data', 'month'));
        return $pdf->download('EMP201_' . $month . '.pdf');
    }

    /**
     * Export UIF as PDF
     */
    public function exportUIFPDF($month)
    {
        $payslipIds = Payrun::where('term', $month)->pluck('payslip_id');
        $payslips = PaySlip::whereIn('id', $payslipIds)->where('workspace', getActiveWorkspace())->get();
        $uifData = $this->calculateUIFData($payslips, $month);

        $pdf = \PDF::loadView('hrm.filing.uif-pdf', compact('payslips', 'uifData', 'month'));
        return $pdf->download('UIF_Declaration_' . $month . '.pdf');
    }

    /**
     * Store ETI inputs
     */
    public function storeETIInputs(Request $request, $month)
    {
        $request->validate([
            'amount_claimed' => 'nullable|numeric',
            'amount_forfeited' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'last_version' => 'required|string',
            'not_eti_compliant' => 'nullable|boolean'
        ]);

        return redirect()->back()->with('success', 'ETI inputs saved successfully');
    }

    /**
     * Finalize EMP201 for a specific month
     * @param Request $request
     * @param string $month
     * @return \Illuminate\Http\RedirectResponse
     */
    public function finalizeEMP201(Request $request, $month)
    {
        $payslipIds = Payrun::where('term', $month)->pluck('payslip_id');
        $payslips = PaySlip::whereIn('id', $payslipIds)
            ->where('workspace', getActiveWorkspace())
            ->get();

        if ($payslips->isEmpty()) {
            return redirect()->back()->with('error', 'No payrun payslips found for the selected month.');
        }

        foreach ($payslips as $payslip) {
            $payslip->emp201_status = 'finalized';
            $payslip->emp201_finalized_at = Carbon::now();
            $payslip->save();
        }

        return redirect()->back()->with('success', 'EMP201 finalized successfully for ' . $month);
    }

    /**
     * Get finalized payruns grouped by month
     * @return array
     */
    private function getFinalizedPayruns(?int $year = null)
    {
        $payslips = PaySlip::where('workspace', getActiveWorkspace())->pluck('id');
        $payrunTerms = Payrun::whereIn('payslip_id', $payslips)
            ->distinct('term')
            ->orderBy('term', 'DESC')
            ->pluck('term');

        if (!empty($year)) {
            $payrunTerms = $payrunTerms->filter(function ($term) use ($year) {
                return Carbon::parse($term)->year === $year;
            })->values();
        }

        $finalizedPayruns = [];
        foreach ($payrunTerms as $term) {
            $payrunPayslips = Payrun::where('term', $term)
                ->whereIn('payslip_id', $payslips)
                ->get();

            $finalizedPayruns[] = [
                'term' => $term,
                'month_name' => Carbon::parse($term)->format('F Y'),
                'payslip_count' => $payrunPayslips->count(),
                'total_paye' => $payrunPayslips->sum(function ($payrun) {
                    return PaySlip::find($payrun->payslip_id)->saturation_deduction ?? 0;
                }),
                'status' => $this->getEMP201Status($term),
                'finalized_date' => $this->getEMP201FinalizedDate($term)
            ];
        }

        return $finalizedPayruns;
    }

    private function getAvailablePayrunYears(): array
    {
        $payslips = PaySlip::where('workspace', getActiveWorkspace())->pluck('id');

        return Payrun::whereIn('payslip_id', $payslips)
            ->distinct('term')
            ->orderBy('term', 'DESC')
            ->pluck('term')
            ->map(function ($term) {
                return (int) Carbon::parse($term)->year;
            })
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Calculate EMP201 data for given payslips
     * @param $payslips
     * @return array
     */
    private function calculateEMP201Data($payslips, $month)
    {
        $totalPAYE = 0;
        $totalUIF = 0;
        $totalSDL = 0;
        $totalETI = 0;
        $totalGrossSalary = 0;

        foreach ($payslips as $payslip) {
            $totalPAYE += $this->extractPAYEFromPayslip($payslip);
            $totalUIF += $this->extractUIFFromPayslip($payslip);
            $totalSDL += $this->extractSDLFromPayslip($payslip);
            $totalGrossSalary += ($payslip->basic_salary + $payslip->allowance + $payslip->other_payment);
        }

        $totalETI = $this->calculateETI($payslips, $month);
        $etiBroughtForward = $this->getETIBroughtForward($month);

        return [
            'paye_liability' => $totalPAYE,
            'uif_liability' => $totalUIF,
            'sdl_liability' => $totalSDL,
            'eti_current_month' => $totalETI,
            'eti_brought_forward' => $etiBroughtForward,
            'total_eti' => $totalETI + $etiBroughtForward,
            'gross_salary' => $totalGrossSalary,
            'total_payable' => $totalPAYE + $totalUIF + $totalSDL - $totalETI
        ];
    }

    /**
     * Extract PAYE amount from payslip
     * Uses shared TaxCalculationService for consistent tax calculations
     * @param $payslip
     * @return float
     */
    private function extractPAYEFromPayslip($payslip)
    {
        // Use frozen value if available
        if ($payslip->paye_amount !== null) {
            return (float) $payslip->paye_amount;
        }

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
     * Extract UIF amount from payslip
     * @param $payslip
     * @return float
     */
    private function extractUIFFromPayslip($payslip)
    {
        // Use frozen value if available
        if ($payslip->uif_amount !== null) {
            return (float) $payslip->uif_amount;
        }

        $taxYear = TaxYear::resolveForTerm($payslip->salary_month);
        $uifRate = $taxYear ? $taxYear->uif_rate : 0.01;
        $uifCeiling = $taxYear ? $taxYear->uif_ceiling : 177.12;

        $grossSalary = $payslip->basic_salary + $payslip->allowance + $payslip->other_payment;
        $uif = $grossSalary * $uifRate;
        return min($uif, $uifCeiling);
    }

    /**
     * Extract SDL amount from payslip
     * @param $payslip
     * @return float
     */
    private function extractSDLFromPayslip($payslip)
    {
        // Use frozen value if available
        if ($payslip->sdl_amount !== null) {
            return (float) $payslip->sdl_amount;
        }

        $company_settings = getCompanyAllSetting();
        if (!empty($company_settings['is_sdl_calculate']) && $company_settings['is_sdl_calculate'] == 1) {
            $taxYear = TaxYear::resolveForTerm($payslip->salary_month);
            $sdlRate = $taxYear ? $taxYear->sdl_rate : 0.01;

            $grossSalary = $payslip->basic_salary + $payslip->allowance + $payslip->other_payment;
            return $grossSalary * $sdlRate;
        }
        return 0;
    }

    /**
     * Get EMP201 status for a term
     * @param string $term
     * @return string
     */
    private function getEMP201Status($term)
    {
        $payslipIds = Payrun::where('term', $term)->pluck('payslip_id');
        $finalizedCount = PaySlip::whereIn('id', $payslipIds)
            ->where('emp201_status', 'finalized')
            ->where('workspace', getActiveWorkspace())
            ->count();

        return $finalizedCount > 0 ? 'Finalized' : 'New';
    }

    /**
     * Get EMP201 finalized date for a term
     * @param string $term
     * @return string|null
     */
    private function getEMP201FinalizedDate($term)
    {
        $payslipIds = Payrun::where('term', $term)->pluck('payslip_id');
        $payslip = PaySlip::whereIn('id', $payslipIds)
            ->where('emp201_status', 'finalized')
            ->where('workspace', getActiveWorkspace())
            ->first();

        return $payslip ? $payslip->emp201_finalized_at : null;
    }

    private function calculateETI($payslips, $month)
    {
        $totalETI = 0;

        foreach ($payslips as $payslip) {
            $employee = $payslip->employee_profile;
            if (!$employee) continue;

            $grossSalary = $payslip->basic_salary + $payslip->allowance + $payslip->other_payment;

            if ($this->isETIEligible($employee, $grossSalary, $month)) {
                $totalETI += $this->calculateETIAmount($employee, $grossSalary, $month);
            }
        }

        return $totalETI;
    }

    private function isETIEligible($employee, $grossSalary, $term = null)
    {
        $taxYear = $term ? TaxYear::resolveForTerm($term) : null;
        $minAge = $taxYear ? $taxYear->eti_min_age : 18;
        $maxAge = $taxYear ? $taxYear->eti_max_age : 29;
        $salaryCap = $taxYear ? $taxYear->eti_salary_cap : 6500;

        $age = Carbon::parse($employee->date_of_birth ?? '1990-01-01')->age;
        return $age >= $minAge && $age <= $maxAge && $grossSalary <= $salaryCap;
    }

    private function calculateETIAmount($employee, $grossSalary, $month)
    {
        $taxYear = TaxYear::resolveForTerm($month);
        $minAge = $taxYear ? $taxYear->eti_min_age : 18;
        $maxAge = $taxYear ? $taxYear->eti_max_age : 29;
        $etiRate = $taxYear ? $taxYear->eti_rate : 0.5;
        $etiMax = $taxYear ? $taxYear->eti_max_amount : 1000;

        $age = Carbon::parse($employee->date_of_birth ?? '1990-01-01')->age;

        if ($age >= $minAge && $age <= $maxAge) {
            return min($grossSalary * $etiRate, $etiMax);
        }

        return 0;
    }

    private function getETIBroughtForward($month)
    {
        return 0;
    }

    /**
     * Calculate UIF data for given payslips
     * @param $payslips
     * @param $month
     * @return array
     */
    private function calculateUIFData($payslips, $month)
    {
        $totalUIF = 0;
        $totalRemuneration = 0;

        foreach ($payslips as $payslip) {
            $grossSalary = $payslip->basic_salary + $payslip->allowance + $payslip->other_payment;
            $totalRemuneration += $grossSalary;
            $totalUIF += $this->extractUIFFromPayslip($payslip);
        }

        $employeeContribution = $totalUIF / 2;
        $employerContribution = $totalUIF / 2;

        return [
            'total_uif_liability' => $totalUIF,
            'employee_contribution' => $employeeContribution,
            'employer_contribution' => $employerContribution,
            'total_remuneration' => $totalRemuneration
        ];
    }

    /**
     * Display bi-filing view
     */
    public function biFiling(Request $request)
    {
        $biFilingData = $this->getBiFilingData();
        $perPage = (int) $request->get('per_page', 10);
        $selectedSeason = $request->get('season', $biFilingData[0]['season'] ?? null);

        foreach ($biFilingData as $index => &$season) {
            $pageName = 'page_' . str_replace('-', '_', $season['season']);
            $currentPage = $season['season'] === $selectedSeason
                ? LengthAwarePaginator::resolveCurrentPage($pageName)
                : 1;

            $payruns = collect($season['payruns']);
            $season['payruns'] = new LengthAwarePaginator(
                $payruns->forPage($currentPage, $perPage)->values(),
                $payruns->count(),
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'pageName' => $pageName,
                    'query' => $request->query(),
                ]
            );
        }
        unset($season);

        return view('hrm.filing.annual', compact('biFilingData', 'selectedSeason'));
    }

    /**
     * Get bi-filing data grouped by filing seasons
     */
    private function getBiFilingData()
    {
        $payslips = PaySlip::where('workspace', getActiveWorkspace())->pluck('id');
        $payrunTerms = Payrun::whereIn('payslip_id', $payslips)
            ->distinct('term')
            ->orderBy('term', 'DESC')
            ->pluck('term');
        $biFilingData = [];
        $currentYear = date('Y');

        for ($year = $currentYear; $year >= $currentYear - 2; $year--) {
            $seasonStart = ($year) . '-02-28';
            $seasonEnd = $year . '-08-31';

            $seasonPayruns = $payrunTerms->filter(function ($term) use ($seasonStart, $seasonEnd) {
                return $term >= $seasonStart && $term <= $seasonEnd;
            });

            if ($seasonPayruns->count() > 0) {
                $biFilingData[] = [
                    'season' => $year . '-02-28',
                    'season_label' => 'Filing Season ' . ($year - 1) . '/' . $year,
                    'payruns' => $this->getPayrunDetails($seasonPayruns)
                ];
            }
        }

        return $biFilingData;
    }

    /**
     * Get detailed payrun information
     */
    private function getPayrunDetails($payrunTerms)
    {
        $payrunDetails = [];

        foreach ($payrunTerms as $term) {
            $payruns = Payrun::where('term', $term)->get();

            foreach ($payruns as $payrun) {
                $payslip = PaySlip::where('workspace', getActiveWorkspace())->where('id', $payrun->payslip_id)->first();
                if ($payslip && $payslip->employee_profile) {
                    $payrunDetails[] = [
                        'employee_name' => $payslip->employee_profile->first_name ?? 'Unknown',
                        'employee_number' => $payslip->employee_profile->employee_id ?? 'N/A',
                        'date' => $term,
                        'type' => 'Payrun',
                        'payslip_id' => $payslip->id,
                        'payslip' => $payslip
                    ];
                }
            }
        }

        return $payrunDetails;
    }

    /**
     * Export individual employee bi-filing PDF
     */
    public function exportEmployeeBiFilingPDF($payslipId)
    {
        $payslip = PaySlip::with('employee_profile')->find($payslipId);

        if (!$payslip || !$payslip->employee_profile) {
            return redirect()->back()->with('error', 'Employee data not found');
        }

        $payrun = Payrun::where('payslip_id', $payslipId)->first();
        $employeeData = $this->calculateEmployeeTaxData($payslip);

        $pdf = \PDF::loadView('hrm.filing.employee-bi-filing-pdf', compact('payslip', 'payrun', 'employeeData'));
        return $pdf->download('Employee_' . $payslip->employee_profile->name . '_' . $payrun->term . '.pdf');
    }

    /**
     * Export EMP501 reconciliation as PDF for a given season
     */
    public function exportEMP501PDF($season)
    {
        $year = Carbon::parse($season)->year;
        $seasonData = [
            'season' => $season,
            'season_label' => 'Filing Season ' . ($year - 1) . '/' . $year,
            'start_date' => Carbon::create($year, 3, 1)->startOfDay(),
            'end_date' => Carbon::create($year, 8, 31)->endOfDay(),
        ];

        $payrunTerms = Payrun::whereBetween('term', [$seasonData['start_date']->format('Y-m'), $seasonData['end_date']->format('Y-m')])
            ->distinct('term')
            ->orderBy('term', 'ASC')
            ->pluck('term');

        $monthlyData = [];
        $employeeSummary = [];

        foreach ($payrunTerms as $term) {
            $payslipIds = Payrun::where('term', $term)->pluck('payslip_id');
            $payslips = PaySlip::whereIn('id', $payslipIds)
                ->where('workspace', getActiveWorkspace())
                ->with('employee_profile')
                ->get();

            if ($payslips->isNotEmpty()) {
                $monthData = $this->calculateEMP201Data($payslips, $term);
                $monthData['month_name'] = Carbon::parse($term)->format('F Y');
                $monthlyData[] = $monthData;

                foreach ($payslips as $payslip) {
                    if ($payslip->employee_profile) {
                        $empId = $payslip->employee_id;
                        if (!isset($employeeSummary[$empId])) {
                            $employeeSummary[$empId] = [
                                'name' => $payslip->employee_profile->first_name . ' ' . $payslip->employee_profile->last_name,
                                'employee_id' => $payslip->employee_profile->employee_id,
                                'total_remuneration' => 0,
                                'total_paye' => 0,
                            ];
                        }
                        $employeeSummary[$empId]['total_remuneration'] += ($payslip->basic_salary + $payslip->allowance + $payslip->other_payment);
                        $employeeSummary[$empId]['total_paye'] += $this->extractPAYEFromPayslip($payslip);
                    }
                }
            }
        }

        $summary = [
            'total_paye' => array_sum(array_column($monthlyData, 'paye_liability')),

            'total_uif' => array_sum(array_column($monthlyData, 'uif_liability')),

            'total_sdl' => array_sum(array_column($monthlyData, 'sdl_liability')),

            'total_eti' => array_sum(array_column($monthlyData, 'eti_current_month')),

            'total_payable' => 0,
        ];
        $summary['total_payable'] = $summary['total_paye'] + $summary['total_uif'] + $summary['total_sdl'] - $summary['total_eti'];

        $pdf = \PDF::loadView('hrm.filing.emp501-pdf', compact('seasonData', 'monthlyData', 'summary', 'employeeSummary'));
        return $pdf->download('EMP501_' . str_replace('/', '-', $seasonData['season_label']) . '.pdf');
    }

    /**
     * Calculate tax data for individual employee
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
     * Display OID return view
     */
    public function oidReturn(Request $request)
    {
        $season = $request->get('season', '2025-02-28');
        $oidData = $this->getOIDData($season);

        return view('hrm.filing.return', compact('oidData', 'season'));
    }

    /**
     * Get OID data grouped by categories
     */
    private function getOIDData($season)
    {
        $startDate = Carbon::parse($season)->subYear()->startOfMonth();
        $endDate = Carbon::parse($season)->endOfMonth();

        $payslips = PaySlip::whereBetween('salary_month', [$startDate, $endDate])
            ->where('workspace', getActiveWorkspace())
            ->where('status', 2)
            ->get();

        return [
            'benefits' => $this->getBenefitsData($payslips),
            'income' => $this->getIncomeData($payslips),
            'allowances' => $this->getAllowancesData($payslips),
            'seasons' => $this->getFilingSeasons()
        ];
    }

    private function getBenefitsData($payslips)
    {
        $benefits = [];
        $employeeIds = $payslips->pluck('employee_id')->unique();

        $benefits['accommodation_benefit'] = ['name' => 'Accommodation Benefit', 'count' => AccommodationBenefit::whereIn('employee_id', $employeeIds)->count()];
        $benefits['medical_costs_benefit'] = ['name' => 'Medical Costs Benefit', 'count' => MedicalCost::whereIn('employee_id', $employeeIds)->count()];
        $benefits['pension_fund_benefit'] = ['name' => 'Pension Fund Benefit', 'count' => PensionFund::whereIn('employee_id', $employeeIds)->count()];
        $benefits['company_car_benefit'] = ['name' => 'Company Car Benefit', 'count' => CompanyCar::whereIn('employee_id', $employeeIds)->count()];
        $benefits['bursaries_scholarships'] = ['name' => 'Bursaries and Scholarships', 'count' => Bursary::whereIn('employee_id', $employeeIds)->count()];

        return $benefits;
    }

    private function getIncomeData($payslips)
    {
        $income = [];
        $employeeIds = $payslips->pluck('employee_id')->unique();

        $income['basic_salary'] = ['name' => 'Basic Salary', 'count' => $payslips->where('basic_salary', '>', 0)->count()];
        $income['annual_bonus'] = ['name' => 'Annual Bonus', 'count' => AnnualBonus::whereIn('employee_id', $employeeIds)->count()];
        $income['commission'] = ['name' => 'Commission', 'count' => PayslipCommission::whereIn('employee_id', $employeeIds)->count()];
        $income['extra_pay'] = ['name' => 'Extra Pay', 'count' => ExtraPay::whereIn('employee_id', $employeeIds)->count()];
        $income['once_off_commission'] = ['name' => 'Once-off Commission', 'count' => OnceOffCommission::whereIn('employee_id', $employeeIds)->count()];

        return $income;
    }

    private function getAllowancesData($payslips)
    {
        $allowances = [];
        $employeeIds = $payslips->pluck('employee_id')->unique();

        $allowances['travel_allowance'] = ['name' => 'Travel Allowance', 'count' => TravelAllowance::whereIn('employee_id', $employeeIds)->count()];
        $allowances['phone_allowance'] = ['name' => 'Phone Allowance', 'count' => PhoneAllowance::whereIn('employee_id', $employeeIds)->count()];
        $allowances['computer_allowance'] = ['name' => 'Computer Allowance', 'count' => ComputerAllowance::whereIn('employee_id', $employeeIds)->count()];
        $allowances['tool_allowance'] = ['name' => 'Tool Allowance', 'count' => ToolAllowance::whereIn('employee_id', $employeeIds)->count()];
        $allowances['uniform_allowance'] = ['name' => 'Uniform Allowance', 'count' => UniformAllowance::whereIn('employee_id', $employeeIds)->count()];

        return $allowances;
    }

    private function getFilingSeasons()
    {
        $currentYear = date('Y');
        return [
            $currentYear . '-02-28',
            ($currentYear + 1) . '-02-28',
            ($currentYear - 1) . '-02-28'
        ];
    }

    public function exportOIDExcel(Request $request)
    {
        $selectedItems = $request->input('accounts', []);
        $season = $request->input('season', '2025-02-28');

        $startDate = Carbon::parse($season)->subYear()->startOfMonth();
        $endDate = Carbon::parse($season)->endOfMonth();

        $payslips = PaySlip::whereBetween('salary_month', [$startDate, $endDate])
            ->where('workspace', getActiveWorkspace())
            ->where('status', 2)
            ->with('employee_profile')
            ->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $summarySheet = $spreadsheet->createSheet();
        $summarySheet->setTitle('Summary');

        $summarySheet->setCellValue('A1', 'OID RETURN SUMMARY');
        $summarySheet->mergeCells('A1:C1');
        $summarySheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E8F4FD']]
        ]);

        $summarySheet->setCellValue('A3', 'Season:');
        $summarySheet->setCellValue('B3', $season);
        $summarySheet->setCellValue('A4', 'Total Employees:');
        $summarySheet->setCellValue('B4', $payslips->groupBy('employee_id')->count());
        $summarySheet->setCellValue('A5', 'Total Payslips:');
        $summarySheet->setCellValue('B5', $payslips->count());

        $summarySheet->setCellValue('A7', 'Month');
        $summarySheet->setCellValue('B7', 'Employee Count');
        $summarySheet->setCellValue('C7', 'Total Amount');

        $summarySheet->getStyle('A7:C7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        $row = 8;
        for ($month = 1; $month <= 12; $month++) {
            $monthlyPayslips = $payslips->filter(function ($payslip) use ($month) {
                return Carbon::parse($payslip->salary_month)->month == $month;
            });

            $summarySheet->setCellValue('A' . $row, date('F', mktime(0, 0, 0, $month, 1)));
            $summarySheet->setCellValue('B' . $row, $monthlyPayslips->groupBy('employee_id')->count());
            $summarySheet->setCellValue('C' . $row, 'R' . number_format($monthlyPayslips->sum('net_payble'), 2));
            $row++;
        }

        $employeeSheet = $spreadsheet->createSheet();
        $employeeSheet->setTitle('Employee Data');

        $employeeSheet->setCellValue('A1', 'EMPLOYEE COMPENSATION DATA');
        $employeeSheet->mergeCells('A1:E1');
        $employeeSheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E8F4FD']]
        ]);

        $employeeSheet->setCellValue('A3', 'Employee Name');
        $employeeSheet->setCellValue('B3', 'Employee ID');
        $employeeSheet->setCellValue('C3', 'Basic Salary');
        $employeeSheet->setCellValue('D3', 'Allowances');
        $employeeSheet->setCellValue('E3', 'Benefits');

        $employeeSheet->getStyle('A3:E3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        $row = 4;
        foreach ($payslips->groupBy('employee_id') as $employeePayslips) {
            $firstPayslip = $employeePayslips->first();
            if ($firstPayslip->employee_profile) {
                $employeeSheet->setCellValue('A' . $row, $firstPayslip->employee_profile->first_name . ' ' . $firstPayslip->employee_profile->last_name);
                $employeeSheet->setCellValue('B' . $row, $firstPayslip->employee_profile->employee_id);
                $employeeSheet->setCellValue('C' . $row, 'R' . number_format($employeePayslips->sum('basic_salary'), 2));
                $employeeSheet->setCellValue('D' . $row, 'R' . number_format($employeePayslips->sum('allowance'), 2));
                $employeeSheet->setCellValue('E' . $row, 'R' . number_format($employeePayslips->sum('other_payment'), 2));
                $row++;
            }
        }

        if ($row > 4) {
            $employeeSheet->getStyle('A4:E' . ($row - 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
        }

        $directorSheet = $spreadsheet->createSheet();
        $directorSheet->setTitle('Director Data');
        $directorSheet->setCellValue('A1', 'DIRECTOR DATA');
        $directorSheet->setCellValue('A3', 'No Director Data Available');

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            foreach (range('A', 'E') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        $fileName = 'OID_Return_' . $season . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment;filename="' . $fileName . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function getOIDItemsFromPayslip()
    {
        return [
            'benefits' => [
                'accommodation_benefit' => 'Accommodation Benefit',
                'bursaries_scholarships' => 'Bursaries and Scholarships',
                'company_car_benefit' => 'Company Car Benefit',
                'medical_costs_benefit' => 'Medical Costs Benefit',
                'pension_fund_benefit' => 'Pension Fund Benefit'
            ],
            'income' => [
                'basic_salary' => 'Basic Salary',
                'annual_bonus' => 'Annual Bonus',
                'commission' => 'Commission',
                'extra_pay' => 'Extra Pay'
            ],
            'allowances' => [
                'computer_allowance' => 'Computer Allowance',
                'phone_allowance' => 'Phone Allowance',
                'travel_allowance' => 'Travel Allowance',
                'tool_allowance' => 'Tool Allowance'
            ]
        ];
    }

    /**
     * Export Tax Certificate file for SARS submission
     */
    public function exportTaxCertificate($season)
    {
        try {
            $seasonData = $this->getSeasonDateRange($season);
            $payslips = $this->getSeasonPayslips($seasonData['start_date'], $seasonData['end_date']);

            if ($payslips->isEmpty()) {
                $fallbackPayslips = PaySlip::whereBetween('salary_month', [
                    $seasonData['start_date']->format('Y-m-d'),
                    $seasonData['end_date']->format('Y-m-d')
                ])
                    ->where('workspace', getActiveWorkspace())
                    ->with(['employee_profile'])
                    ->get();

                if ($fallbackPayslips->isEmpty()) {
                    return redirect()->back()->with('error', 'No payslip data found for the selected season (' . $season . '). Season range: ' . $seasonData['start_date']->format('Y-m-d') . ' to ' . $seasonData['end_date']->format('Y-m-d'));
                } else {
                    $payslips = $fallbackPayslips;
                }
            }

            $content = $this->generateTaxCertificateContent($payslips, $seasonData);

            $fileName = 'tax_certificate_export_' . str_replace(['/', '-'], '_', $season);

            return response($content)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error generating tax certificate: ' . $e->getMessage());
        }
    }

    /**
     * Get date range for filing season
     */
    private function getSeasonDateRange($season)
    {
        $year = Carbon::parse($season)->year;

        return [
            'start_date' => Carbon::create($year, 3, 1)->startOfDay(),
            'end_date' => Carbon::create($year, 8, 31)->endOfDay(),
            'year' => $year
        ];
    }

    /**
     * Get payslips for the season
     */
    private function getSeasonPayslips($startDate, $endDate)
    {

        $payrunTerms = Payrun::whereBetween('term', [$startDate->format('Y-m'), $endDate->format('Y-m')])
            ->distinct('term')
            ->orderBy('term', 'ASC')
            ->pluck('term');

        $payslips = collect();

        foreach ($payrunTerms as $term) {
            $payslipIds = Payrun::where('term', $term)->pluck('payslip_id');
            $termPayslips = PaySlip::whereIn('id', $payslipIds)
                ->where('workspace', getActiveWorkspace())
                ->with(['employee_profile'])
                ->get();

            $payslips = $payslips->merge($termPayslips);
        }

        return $payslips;
    }

    /**
     * Generate complete tax certificate content
     */
    private function generateTaxCertificateContent($payslips, $seasonData)
    {
        $lines = [];

        $lines[] = $this->generateCompanyRecord($seasonData);

        $employeeCount = 0;
        $totalRemuneration = 0;
        $totalTax = 0;

        foreach ($payslips->groupBy('employee_id') as $employeeId => $employeePayslips) {
            $employeeRecord = $this->generateEmployeeRecord($employeePayslips, $seasonData);
            if ($employeeRecord) {
                $lines[] = $employeeRecord['line'];
                $employeeCount++;
                $totalRemuneration += $employeeRecord['remuneration'];
                $totalTax += $employeeRecord['tax'];
            }
        }

        $lines[] = $this->generateSummaryRecord($employeeCount, $totalRemuneration, $totalTax);

        return implode("\n", $lines);
    }

    private function generateCompanyRecord($seasonData)
    {
        $workspace = WorkSpace::find(getActiveWorkspace());
        $settings = getCompanyAllSetting();

        $companyName = $workspace ? $workspace->name : '';
        $tax_number = $settings['tax_number'] ?? '';
        $sdl_number = $settings['sdl_number'] ?? '';
        $uif_number = $settings['uif_number'] ?? '';
        $accountant_first_name = $settings['accountant_first_name'] ?? '';
        $accountant_last_name = $settings['accountant_last_name'] ?? '';
        $accountant_position = $settings['accountant_position'] ?? '';
        $accountant_primary_number = $settings['accountant_primary_number'] ?? '';
        $accountant_secondary_number = $settings['accountant_secondary_number'] ?? '';
        $accountant_email = $settings['accountant_email'] ?? '';
        $address1 = $settings['company_address'] ?? '';
        $address2 = $settings['company_address_2'] ?? '';
        $address3 = $settings['company_address_3'] ?? '';
        $city = $settings['company_city'] ?? '';
        $stateId = $settings['company_state'] ?? '';
        $state = Province::find($stateId);
        $postalCode = $settings['company_zipcode'] ?? '';
        $countryId = $settings['company_country'] ?? '';
        $countryCode = Country::find($countryId);
        $companySic7Id = $settings['company_sic7_code'] ?? '';
        $companySic7 = Sic7Code::find($companySic7Id);

        return sprintf(
            '2010,"%s",2015,"LIVE",2020,"%s",2022,"%s",2024,"%s",2025,"%s",2026,"%s",2027,"%s",2028,"%s",2029,"%s",2030,%d,2031,%d%02d,2036,"%s",2037,"N",2038,"%s",2040,"%s",2063,"%s",2064,"%s",2065,"%s",2066,"%s",2080,%s,2081,"%s",2082,"%s",9999',
            $companyName,
            $tax_number,
            $sdl_number,
            $uif_number,
            $accountant_first_name,
            $accountant_primary_number,
            $accountant_email,
            'Reliance Corporation',
            'RC Books',
            $seasonData['year'],
            $seasonData['year'],
            00,
            $accountant_last_name,
            $accountant_position,
            $accountant_secondary_number,
            2,
            $address1,
            $address2,
            $state->name ?? '',
            $city,
            $postalCode,
            $countryCode->iso_code ?? '',
            $companySic7->code ?? ''
        );
    }

    private function generateEmployeeRecord($employeePayslips, $seasonData)
    {
        $employee = $employeePayslips->first()->employee_profile;
        if (!$employee) return null;

        $totals = $this->calculateEmployeeTaxTotals($employeePayslips);

        if ($totals['gross_income'] <= 0) return null;

        $certificateType = $totals['paye_deducted'] > 0 ? 'IRP5' : 'IT3(a)';
        $employeeType = $certificateType === 'IRP5' ? 'B' : 'A';

        $settings = getCompanyAllSetting();
        $taxNumber = $settings['tax_number'] ?? '0000000000';
        $taxNumber = preg_replace('/[^A-Z0-9]/', '', strtoupper($taxNumber));
        $taxNumber = substr(str_pad($taxNumber, 10, '0', STR_PAD_LEFT), 0, 10);

        $year = str_pad((string)$seasonData['year'], 4, '0', STR_PAD_LEFT);

        $mm = '08';
        if (isset($seasonData['end_date']) && Carbon::parse($seasonData['end_date'])->format('m') == '02') {
            $mm = '02';
        }

        $unique = preg_replace('/[^A-Z0-9]/', '', strtoupper($employee->employee_id ?? ''));
        $unique = str_pad($unique, 14, '0', STR_PAD_LEFT);

        $certificateNumber = $taxNumber . $year . $mm . $unique;

        $address = $this->getEmployeeAddress($employee);

        $monthsWorked = $this->calculateMonthsWorked($employee->date_of_appointment, $seasonData);
        $monthsInService = $this->calculateMonthsInService($employee->date_of_appointment, $seasonData);
        $employerAddress1 = $settings['company_address'] ?? '';
        $employerAddress2 = $settings['company_address_2'] ?? '';
        $employerAddress3 = $settings['company_address_3'] ?? '';
        $employerCity = $settings['company_city'] ?? '';
        $employerPostalCode = $settings['company_zipcode'] ?? '';
        $employerCountryId = $settings['company_country'] ?? '';
        $employerCountry = Country::find($employerCountryId)->iso_code ?? 'ZA';
        $employerSic7Id = $settings['company_sic7_code'] ?? '';
        $employerSic7 = Sic7Code::find($employerSic7Id)->code ?? '';
        $employeeState = $employee->state ?? '';
        $employeeCountry = $employee->country ?? 'ZA';
        switch (strtolower($employee->account_type ?? '')) {
            case 'cheque':
            case 'current':
            case 'cheque/current account':
                $bankAccountType = 1;
                break;
            case 'savings':
            case 'savings account':
                $bankAccountType = 2;
                break;
            case 'transmission':
            case 'transmission account':
                $bankAccountType = 3;
                break;
            case 'bond':
            case 'bond account':
                $bankAccountType = 4;
                break;
            case 'credit card':
            case 'credit card account':
                $bankAccountType = 5;
                break;
            case 'subscription share':
            case 'subscription share account':
                $bankAccountType = 6;
                break;
            case 'foreign bank':
            case 'foreign bank account':
                $bankAccountType = 7;
                break;
            default:
                $bankAccountType = 0;
        }
        $bankAccountNumber = $employee->account_number ?? '';
        $bankBranchNumber = $employee->branch_code ?? '';
        $bankName = $employee->bank ?? '';
        $bankBranchName = $employee->branch_name ?? '';
        $accountHolderName = $employee->first_name . ' ' . $employee->last_name ?? '';
        switch (strtolower($employee->holder_relationship ?? '')) {
            case 'own':
                $accountHolderRelationship = 1;
                break;
            case 'joint':
                $accountHolderRelationship = 2;
                break;
            case 'third party':
                $accountHolderRelationship = 3;
                break;
            default:
                $accountHolderRelationship = 1;
        }
        $dateOfJoining = $this->formatDate($employee->date_of_appointment ?? $seasonData['start_date']);
        $line = sprintf(
            '3010,"%s",3015,"%s",3020,"%s",3025,%d,3026,N,3030,"%s",3040,"%s",3050,"%s",3060,"%s",3065,"%s",3075,"%s",3080,%s,3100,"%s",3136,"%s",3145,"%s",3147,"%s",3148,"%s",3149,"%s",3150,"%s",3151,"%s",3160,"%s",3170,%s,3180,%s,3190,%s,3195,"N",3200,%.4f,3210,%.4f,3212,"%s",3214,"%s",3215,"%s",3216,"%s",3217,"%s",3220,"Y",3240,"%d",3241,"%s",3242,"%s",3243,"%s",3244,"%s",3245,"%s",3246,"%d",3263,"%s",3279,"N",3285,"%s",3288,"%s",3601,%.0f,3699,%.0f,4102,%.2f,4141,%.2f,4149,%.2f,9999',
            $certificateNumber,
            $certificateType,
            $employeeType,
            $seasonData['year'],
            $employee->last_name ?? '',
            $employee->first_name ?? '',
            substr($employee->first_name ?? '', 0, 1),
            $employee->identification_type == 'RSA ID' || $employee->identification_type == 'Refugee id' ? $employee->id_number : '',
            $employee->identification_type == 'Passport/foreign id' ? $employee->id_number : '',
            $employee->passport_country ?? '',
            $this->formatDateOfBirth($employee->date_of_birth),
            $employee->tax_reference_number ?? '',
            $employee->phone_number ?? '',
            $employerAddress1,
            $employerAddress2,
            $employerAddress3,
            $employerCity,
            $employerPostalCode,
            $employerCountry,
            $employee->employee_id ?? '',
            $this->formatDate($employee->date_of_appointment ?? $seasonData['start_date']),
            $this->formatDate($seasonData['end_date']),
            $dateOfJoining,
            $monthsWorked,
            $monthsInService,
            $address['number'],
            $address['street'],
            $address['city'],
            $employeeState,
            $address['postal_code'],
            $bankAccountType,
            $bankAccountNumber,
            $bankBranchNumber,
            $bankName,
            $bankBranchName,
            $accountHolderName,
            $accountHolderRelationship,
            $employerSic7,
            $employeeCountry,
            "1",
            $totals['gross_income'],
            $totals['gross_income'],
            $totals['paye_deducted'],
            $totals['uif_deducted'],
            $totals['net_remuneration']
        );

        return [
            'line' => $line,
            'remuneration' => $totals['gross_income'],
            'tax' => $totals['paye_deducted']
        ];
    }

    /**
     * Calculate employee totals for the tax year
     */
    private function calculateEmployeeTaxTotals($employeePayslips)
    {
        $grossIncome = 0;
        $payeDeducted = 0;
        $uifDeducted = 0;
        $periodsWorked = 0;

        foreach ($employeePayslips as $payslip) {
            $gross = ($payslip->basic_salary ?? 0) +
                ($payslip->allowance ?? 0) +
                ($payslip->commission ?? 0) +
                ($payslip->other_payment ?? 0) +
                ($payslip->overtime ?? 0);

            $grossIncome += $gross;

            $totalDeductions = $payslip->saturation_deduction ?? 0;

            // Use frozen values if available
            if ($payslip->uif_amount !== null) {
                $uif = (float) $payslip->uif_amount;
            } else {
                $taxYear = TaxYear::resolveForTerm($payslip->salary_month);
                $uifRate = $taxYear ? $taxYear->uif_rate : 0.01;
                $uifCeiling = $taxYear ? $taxYear->uif_ceiling : 177.12;
                $uif = min($gross * $uifRate, $uifCeiling);
            }
            $uifDeducted += $uif;

            if ($payslip->paye_amount !== null) {
                $payeDeducted += (float) $payslip->paye_amount;
            } else {
                $estimatedPAYE = max(0, $totalDeductions - $uif - ($payslip->loan ?? 0));
                $payeDeducted += $estimatedPAYE;
            }

            $periodsWorked++;
        }

        return [
            'gross_income' => $grossIncome,
            'paye_deducted' => $payeDeducted,
            'uif_deducted' => $uifDeducted,
            'net_remuneration' => $grossIncome - $payeDeducted - $uifDeducted,
            'periods_worked' => $periodsWorked
        ];
    }

    /**
     * Get employee address fields
     */
    private function getEmployeeAddress($employee)
    {
        return [
            'number' => $employee->flat_no ?? '',
            'street' => $employee->street ?? '',
            'suburb' => $employee->city ?? '',
            'city' => $employee->city ?? '',
            'postal_code' => $employee->pincode ?? ''
        ];
    }

    private function generateSummaryRecord($employeeCount, $totalRemuneration, $totalTax)
    {
        return sprintf(
            '6010,%d,6020,%.0f,6030,%.2f,9999',
            $employeeCount,
            $totalRemuneration,
            $totalTax
        );
    }

    private function formatDate($date)
    {
        if (!$date) return '';
        return Carbon::parse($date)->format('Ymd');
    }

    private function formatDateOfBirth($date)
    {
        if (!$date) return '';
        return Carbon::parse($date)->format('Ymd');
    }

    private function calculateMonthsWorked($dateOfAppointment, $seasonData)
    {
        if (!$dateOfAppointment) {
            return 0.0000;
        }

        $startDate = Carbon::parse($dateOfAppointment);
        $seasonStart = $seasonData['start_date'];
        $seasonEnd = $seasonData['end_date'];

        $effectiveStartDate = $startDate->greaterThan($seasonStart) ? $startDate : $seasonStart;
        $monthsWorked = $effectiveStartDate->diffInMonths($seasonEnd);
        $remainingDays = $effectiveStartDate->copy()->addMonths($monthsWorked)->diffInDays($seasonEnd);
        if ($remainingDays > 0) {
            $monthsWorked += $remainingDays / 30;
        }

        return round($monthsWorked, 4);
    }

    private function calculateMonthsInService($dateOfAppointment, $seasonData)
    {
        if (!$dateOfAppointment) {
            return 0.0000;
        }

        $startDate = Carbon::parse($dateOfAppointment);
        $seasonEnd = $seasonData['end_date'];

        $monthsInService = $startDate->diffInMonths($seasonEnd);

        $remainingDays = $startDate->copy()->addMonths($monthsInService)->diffInDays($seasonEnd);
        if ($remainingDays > 0) {
            $monthsInService += $remainingDays / 30;
        }

        return round($monthsInService, 4);
    }

    /**
     * Tax Year Summary Report — total PAYE/UIF/SDL per tax year for the current workspace.
     */
    public function taxYearReport(Request $request)
    {
        $workspaceId = getActiveWorkspace();
        $taxYears = TaxYear::locked()->orderByDesc('effective_from')->get();

        $selectedId = $request->input('tax_year_id');
        $reportData = null;

        if ($selectedId) {
            $taxYear = TaxYear::findOrFail($selectedId);

            // Scope payslips to this workspace
            $payslipIds = PaySlip::where('workspace', $workspaceId)->pluck('id');

            $reportData = PaySlip::whereIn('id', $payslipIds)
                ->where('tax_year_id', $taxYear->id)
                ->select(
                    DB::raw('COUNT(*) as payslip_count'),
                    DB::raw('COUNT(DISTINCT employee_id) as employee_count'),
                    DB::raw('SUM(paye_amount) as total_paye'),
                    DB::raw('SUM(uif_amount) as total_uif'),
                    DB::raw('SUM(sdl_amount) as total_sdl'),
                    DB::raw('SUM(net_payble) as total_net_pay')
                )
                ->first();

            $reportData->tax_year = $taxYear;

            // Monthly breakdown
            $reportData->monthly = PaySlip::whereIn('id', $payslipIds)
                ->where('tax_year_id', $taxYear->id)
                ->select(
                    'salary_month',
                    DB::raw('COUNT(*) as payslip_count'),
                    DB::raw('SUM(paye_amount) as total_paye'),
                    DB::raw('SUM(uif_amount) as total_uif'),
                    DB::raw('SUM(sdl_amount) as total_sdl')
                )
                ->groupBy('salary_month')
                ->orderBy('salary_month')
                ->get();
        }

        return view('hrm.filing.tax-year-report', compact('taxYears', 'selectedId', 'reportData'));
    }
}
