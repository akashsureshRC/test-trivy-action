<?php

namespace App\Services;

use App\Models\Hrm\Employee;
use App\Models\TaxYear;
use Carbon\Carbon;

class TaxCalculationService
{
    /**
     * Calculate monthly PAYE tax based on annual income.
     * Reads tax brackets and rebates from the locked TaxYear for the given term.
     *
     * @param int $employeeId
     * @param float $monthlyIncome
     * @param string $term (Y-m-d format)
     * @return float Monthly tax amount
     */
    public static function calculateMonthlyPAYE($employeeId, $monthlyIncome, $term)
    {
        if ($monthlyIncome <= 0) {
            return 0;
        }

        $taxYear = TaxYear::resolveForTerm($term);

        if (!$taxYear) {
            return 0;
        }

        $annualIncome = $monthlyIncome * 12;

        $employee = Employee::find($employeeId);
        $taxPayerAge = self::resolveTaxPayerAge($employee, $term);

        $taxPayable = self::calculateAnnualTaxBeforeRebate($annualIncome, $taxYear->tax_brackets);
        $taxPayable -= self::calculateAnnualRebate($taxPayerAge, $taxYear);

        if ($taxPayable < 0) {
            $taxPayable = 0;
        }

        return round($taxPayable / 12, 2);
    }

    /**
     * Resolve the locked TaxYear configuration for a given payroll term.
     * Convenience wrapper used by other controllers needing statutory rates.
     */
    public static function getStatutoryRates(string $term): ?TaxYear
    {
        return TaxYear::resolveForTerm($term);
    }

    /**
     * Calculate annual PAYE tax before rebates using the provided tax brackets.
     */
    private static function calculateAnnualTaxBeforeRebate(float $annualIncome, array $taxBrackets): float
    {
        if ($annualIncome <= 0) {
            return 0;
        }

        foreach ($taxBrackets as $bracket) {
            $max = $bracket['max'] ?? PHP_INT_MAX;
            if ($annualIncome >= $bracket['min'] && $annualIncome <= $max) {
                return $bracket['base_tax'] + (($annualIncome - $bracket['threshold']) * $bracket['rate']);
            }
        }

        return 0;
    }

    /**
     * Calculate annual rebate based on taxpayer age and the tax year's rebate values.
     */
    private static function calculateAnnualRebate(?int $age, TaxYear $taxYear): float
    {
        $rebate = (float) $taxYear->primary_rebate;

        if ($age !== null && $age >= $taxYear->secondary_rebate_age) {
            $rebate += (float) $taxYear->secondary_rebate;
        }

        if ($age !== null && $age >= $taxYear->tertiary_rebate_age) {
            $rebate += (float) $taxYear->tertiary_rebate;
        }

        return $rebate;
    }

    /**
     * Resolve taxpayer age at the end of the applicable tax year.
     */
    private static function resolveTaxPayerAge(?Employee $employee, string $term): ?int
    {
        if (!$employee || !$employee->date_of_birth) {
            return null;
        }

        $termDate = Carbon::parse($term);
        $taxYearEndYear = $termDate->month >= 3 ? $termDate->year + 1 : $termDate->year;
        $taxYearEndDate = Carbon::create($taxYearEndYear, 2, 1)->endOfMonth();

        return $employee->date_of_birth->diffInYears($taxYearEndDate);
    }
}
