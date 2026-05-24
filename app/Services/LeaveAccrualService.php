<?php

namespace App\Services;

use App\Models\Hrm\Employee;
use App\Models\Hrm\EmployeeEntitlementPolicy;
use App\Models\Hrm\Leave;
use App\Models\Hrm\LeaveManagement;
use App\Models\Hrm\LeaveRecord;
use Carbon\Carbon;

class LeaveAccrualService
{
    public function getCycleStartDate(LeaveManagement $leaveManagement, Employee $employee, ?Carbon $asOfDate = null): Carbon
    {
        $asOfDate = ($asOfDate ? $asOfDate->copy() : Carbon::now())->startOfDay();
        $cycleLength = $this->getCycleLength($leaveManagement);
        $cycleStartType = $leaveManagement->cycle_start_type ?? 'january';

        switch ($cycleStartType) {
            case 'appointment':
                $anchorDate = Carbon::parse($employee->date_of_appointment ?? $employee->created_at)->startOfDay();
                if ($asOfDate->lt($anchorDate)) {
                    return $anchorDate;
                }
                $monthsSinceAnchor = $anchorDate->diffInMonths($asOfDate);
                $completedCycles = intdiv($monthsSinceAnchor, $cycleLength);

                return $anchorDate->copy()->addMonths($completedCycles * $cycleLength);

            case 'custom':
                if ($leaveManagement->custom_cycle_date) {
                    $anchorDate = Carbon::parse($leaveManagement->custom_cycle_date)->startOfDay();
                    if ($asOfDate->lt($anchorDate)) {
                        return $anchorDate;
                    }
                    $monthsSinceAnchor = $anchorDate->diffInMonths($asOfDate);
                    $completedCycles = intdiv($monthsSinceAnchor, $cycleLength);

                    return $anchorDate->copy()->addMonths($completedCycles * $cycleLength);
                }
                break;
        }

        $januaryAnchor = Carbon::create($asOfDate->year, 1, 1)->startOfDay();
        while ($januaryAnchor->copy()->addMonths($cycleLength)->lte($asOfDate)) {
            $januaryAnchor->addMonths($cycleLength);
        }

        return $januaryAnchor;
    }

    public function getCycleEndDate(LeaveManagement $leaveManagement, Employee $employee, ?Carbon $asOfDate = null): Carbon
    {
        $cycleStartDate = $this->getCycleStartDate($leaveManagement, $employee, $asOfDate);

        return $cycleStartDate->copy()->addMonths($this->getCycleLength($leaveManagement))->subDay()->endOfDay();
    }

    public function isEligible(EmployeeEntitlementPolicy $policy, Employee $employee, ?Carbon $asOfDate = null): bool
    {
        $asOfDate = ($asOfDate ? $asOfDate->copy() : Carbon::now())->endOfDay();
        $entitlementDetails = $policy->entitlementPolicy;

        if (!$entitlementDetails || empty($entitlementDetails->entitlement_after_months)) {
            return true;
        }

        $appointmentDate = Carbon::parse($employee->date_of_appointment ?? $employee->created_at)->startOfDay();
        $eligibilityDate = $appointmentDate->copy()->addMonths((int) $entitlementDetails->entitlement_after_months);

        return $asOfDate->gte($eligibilityDate);
    }

    public function getAccruedEntitlement(EmployeeEntitlementPolicy $policy, Employee $employee, ?Carbon $asOfDate = null): float
    {
        $asOfDate = ($asOfDate ? $asOfDate->copy() : Carbon::now())->endOfDay();

        if (!$this->isEligible($policy, $employee, $asOfDate)) {
            return 0.0;
        }

        $entitlementValue = $this->resolveEntitlementValue($policy, $employee, $asOfDate);
        if ($entitlementValue <= 0) {
            return 0.0;
        }

        $entitlementDetails = $policy->entitlementPolicy;
        if ($entitlementDetails && $entitlementDetails->use_upfront_accrual) {
            return round($entitlementValue, 2);
        }

        $leaveManagement = $policy->leaveManagement;
        if (!$leaveManagement) {
            return 0.0;
        }

        $cycleStartDate = $this->getCycleStartDate($leaveManagement, $employee, $asOfDate);
        if ($asOfDate->lt($cycleStartDate)) {
            return 0.0;
        }

        $monthsElapsed = $cycleStartDate->diffInMonths($asOfDate) + 1;
        if ($monthsElapsed < 1) {
            $monthsElapsed = 1;
        }

        return round($entitlementValue * $monthsElapsed, 2);
    }

    public function getBalanceSnapshot(
        EmployeeEntitlementPolicy $policy,
        Employee $employee,
        ?Carbon $asOfDate = null,
        bool $includePending = false
    ): array {
        $asOfDate = ($asOfDate ? $asOfDate->copy() : Carbon::now())->endOfDay();
        $leaveManagement = $policy->leaveManagement;

        if (!$leaveManagement) {
            return [
                'eligible' => false,
                'accrued' => 0.0,
                'used' => 0.0,
                'pending' => 0.0,
                'available' => 0.0,
                'cycle_start' => null,
                'cycle_end' => null,
            ];
        }

        if (!$this->isEligible($policy, $employee, $asOfDate)) {
            return [
                'eligible' => false,
                'accrued' => 0.0,
                'used' => 0.0,
                'pending' => 0.0,
                'available' => 0.0,
                'cycle_start' => $this->getCycleStartDate($leaveManagement, $employee, $asOfDate),
                'cycle_end' => $this->getCycleEndDate($leaveManagement, $employee, $asOfDate),
            ];
        }

        $cycleStartDate = $this->getCycleStartDate($leaveManagement, $employee, $asOfDate);
        $cycleEndDate = $this->getCycleEndDate($leaveManagement, $employee, $asOfDate);
        $accrued = $this->getAccruedEntitlement($policy, $employee, $asOfDate);

        $used = (float) LeaveRecord::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveManagement->id)
            ->whereBetween('start_date', [$cycleStartDate->toDateString(), $cycleEndDate->toDateString()])
            ->sum('total_days');

        $pending = 0.0;
        if ($includePending) {
            $pending = (float) Leave::where('employee_id', $employee->id)
                ->where('leave_management_id', $leaveManagement->id)
                ->where('workspace', $employee->workspace_id)
                ->where('status', 'Pending')
                ->whereBetween('start_date', [$cycleStartDate->toDateString(), $cycleEndDate->toDateString()])
                ->sum('total_leave_days');
        }

        $available = $accrued - $used - $pending;

        return [
            'eligible' => true,
            'accrued' => round($accrued, 2),
            'used' => round($used, 2),
            'pending' => round($pending, 2),
            'available' => round(max(0, $available), 2),
            'cycle_start' => $cycleStartDate,
            'cycle_end' => $cycleEndDate,
        ];
    }

    public function getLeaveSummaryForTerm(EmployeeEntitlementPolicy $policy, Employee $employee, Carbon $termDate): array
    {
        $monthStartDate = $termDate->copy()->startOfMonth();
        $monthEndDate = $termDate->copy()->endOfMonth();
        $leaveManagement = $policy->leaveManagement;

        if (!$leaveManagement || !$this->isEligible($policy, $employee, $monthEndDate)) {
            return [
                'eligible' => false,
                'available_before_term' => 0.0,
                'taken_this_term' => 0.0,
                'remaining_after_term' => 0.0,
                'paid_leave' => 0.0,
                'unpaid_leave' => 0.0,
            ];
        }

        $cycleStartDate = $this->getCycleStartDate($leaveManagement, $employee, $monthEndDate);
        $cycleEndDate = $this->getCycleEndDate($leaveManagement, $employee, $monthEndDate);
        $accruedToTermEnd = $this->getAccruedEntitlement($policy, $employee, $monthEndDate);

        $leaveTakenBeforeThisTerm = (float) LeaveRecord::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveManagement->id)
            ->whereBetween('start_date', [$cycleStartDate->toDateString(), $monthStartDate->copy()->subDay()->toDateString()])
            ->sum('total_days');

        $leaveTakenThisTerm = (float) LeaveRecord::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveManagement->id)
            ->whereBetween('start_date', [$monthStartDate->toDateString(), $monthEndDate->toDateString()])
            ->sum('total_days');

        $availableBeforeTerm = max(0, $accruedToTermEnd - $leaveTakenBeforeThisTerm);
        $remainingAfterTerm = max(0, $availableBeforeTerm - $leaveTakenThisTerm);

        $paidLeave = 0.0;
        $unpaidLeave = 0.0;
        if ($leaveManagement->unpaid_leave) {
            $unpaidLeave = $leaveTakenThisTerm;
        } else {
            $paidLeave = min($leaveTakenThisTerm, $availableBeforeTerm);
            $unpaidLeave = max(0, $leaveTakenThisTerm - $paidLeave);
        }

        return [
            'eligible' => true,
            'cycle_start' => $cycleStartDate,
            'cycle_end' => $cycleEndDate,
            'accrued_to_term_end' => round($accruedToTermEnd, 2),
            'available_before_term' => round($availableBeforeTerm, 2),
            'taken_this_term' => round($leaveTakenThisTerm, 2),
            'remaining_after_term' => round($remainingAfterTerm, 2),
            'paid_leave' => round($paidLeave, 2),
            'unpaid_leave' => round($unpaidLeave, 2),
        ];
    }

    private function getCycleLength(LeaveManagement $leaveManagement): int
    {
        $cycleLength = (int) ($leaveManagement->cycle_length ?? 12);

        return $cycleLength > 0 ? $cycleLength : 12;
    }

    private function resolveEntitlementValue(EmployeeEntitlementPolicy $policy, Employee $employee, Carbon $asOfDate): float
    {
        $value = (float) ($policy->default_entitlement ?? 0);
        $details = $policy->entitlementPolicy;

        if (!$details || empty($details->cycle_specific_rules)) {
            return $value;
        }

        $rules = is_array($details->cycle_specific_rules)
            ? $details->cycle_specific_rules
            : json_decode($details->cycle_specific_rules, true);

        if (!is_array($rules)) {
            return $value;
        }

        $cycleNumber = $this->getCycleNumber($policy, $employee, $asOfDate);

        foreach ($rules as $rule) {
            $first = (int) ($rule['first_cycle'] ?? 0);
            $last = (int) ($rule['last_cycle'] ?? 0);
            $entitlement = (float) ($rule['entitlement'] ?? 0);

            if ($first > 0 && $last >= $first && $cycleNumber >= $first && $cycleNumber <= $last) {
                return $entitlement;
            }
        }

        return $value;
    }

    private function getCycleNumber(EmployeeEntitlementPolicy $policy, Employee $employee, Carbon $asOfDate): int
    {
        $leaveManagement = $policy->leaveManagement;
        if (!$leaveManagement) {
            return 1;
        }

        $cycleLength = $this->getCycleLength($leaveManagement);
        $cycleStartType = $leaveManagement->cycle_start_type ?? 'january';

        switch ($cycleStartType) {
            case 'appointment':
                $anchorDate = Carbon::parse($employee->date_of_appointment ?? $employee->created_at)->startOfDay();
                break;
            case 'custom':
                $anchorDate = $leaveManagement->custom_cycle_date
                    ? Carbon::parse($leaveManagement->custom_cycle_date)->startOfDay()
                    : Carbon::create($asOfDate->year, 1, 1)->startOfDay();
                break;
            case 'january':
            default:
                $anchorDate = Carbon::create($asOfDate->year, 1, 1)->startOfDay();
                break;
        }

        if ($asOfDate->lt($anchorDate)) {
            return 1;
        }

        $monthsSinceAnchor = $anchorDate->diffInMonths($asOfDate);

        return intdiv($monthsSinceAnchor, $cycleLength) + 1;
    }
}
