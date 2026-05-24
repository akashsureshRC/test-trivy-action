<?php

namespace App\Services\Hrm;

use App\Mail\EssInviteMail;
use App\Mail\EssPasswordResetMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Hrm\Employee;

class EssService
{
    /**
     * Send ESS invitation to an employee.
     *
     * @param Employee $employee
     * @return bool
     */
    public function sendInvitation(Employee $employee): bool
    {
        if (empty($employee->email)) {
            return false;
        }

        // Generate setup token
        $token = $employee->generateEssSetupToken();

        // Dispatch invitation email to the queue.
        // SMTP config is applied at processing time inside EssQueueableMail::send().
        try {
            Mail::to($employee->email)->send(new EssInviteMail($employee, $token));
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to queue ESS invitation for employee ' . $employee->id . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send password reset email to an employee.
     *
     * @param Employee $employee
     * @return bool
     */
    public function sendPasswordReset(Employee $employee): bool
    {
        if (empty($employee->email) || !$employee->ess_enabled) {
            return false;
        }

        // Generate new setup token for password reset
        $token = $employee->generateEssSetupToken();

        // Dispatch password reset email to the queue.
        // SMTP config is applied at processing time inside EssQueueableMail::send().
        try {
            Mail::to($employee->email)->send(new EssPasswordResetMail($employee, $token));
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to queue ESS password reset for employee ' . $employee->id . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Bulk send ESS invitations to multiple employees.
     *
     * @param array $employeeIds
     * @return array
     */
    public function sendBulkInvitations(array $employeeIds): array
    {
        $results = [
            'success' => 0,  // queued successfully
            'failed' => 0,   // failed to queue (token/dispatch error)
            'skipped' => 0,  // already have ESS access
        ];

        $employees = Employee::whereIn('id', $employeeIds)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        foreach ($employees as $employee) {
            // Skip if already has ESS access
            if ($employee->ess_enabled && $employee->password) {
                $results['skipped']++;
                continue;
            }

            // sendInvitation now dispatches to the queue; true = queued, false = dispatch error
            if ($this->sendInvitation($employee)) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Disable ESS access for an employee.
     *
     * @param Employee $employee
     * @return bool
     */
    public function disableAccess(Employee $employee): bool
    {
        return $employee->forceFill([
            'ess_enabled' => false,
        ])->save();
    }

    /**
     * Enable ESS access for an employee (only if they have a password set).
     *
     * @param Employee $employee
     * @return bool
     */
    public function enableAccess(Employee $employee): bool
    {
        if (!$employee->password) {
            return false;
        }

        return $employee->forceFill([
            'ess_enabled' => true,
        ])->save();
    }
}
