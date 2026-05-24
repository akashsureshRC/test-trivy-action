<?php

namespace App\Http\Controllers\Hrm;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Hrm\Employee;
use App\Services\Hrm\EssService;

class EssManagementController extends Controller
{
    protected EssService $essService;

    public function __construct(EssService $essService)
    {
        $this->essService = $essService;
    }

    /**
     * Display ESS management overview.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $employees = Employee::where('workspace_id', getActiveWorkspace())
            ->orderBy('first_name')
            ->paginate($perPage)
            ->appends($request->query())
            ->through(function ($employee) {
                $employee->ess_status = $this->getEssStatus($employee);
                return $employee;
            });

        $allEmployees = Employee::where('workspace_id', getActiveWorkspace())->get()
            ->map(function ($employee) {
                $employee->ess_status = $this->getEssStatus($employee);
                return $employee;
            });

        $stats = [
            'total' => $allEmployees->count(),
            'enabled' => $allEmployees->filter(fn($e) => $e->ess_status['label'] === 'Active')->count(),
            'pending' => $allEmployees->filter(fn($e) => $e->ess_status['label'] === 'Pending Setup')->count(),
            'not_invited' => $allEmployees->filter(fn($e) => $e->ess_status['label'] === 'Not Invited')->count(),
        ];

        return view('hrm.ess-management.index', compact('employees', 'stats'));
    }

    /**
     * Send ESS invitation to a single employee.
     */
    public function sendInvitation(Request $request, $id)
    {
        $employee = Employee::where('workspace_id', getActiveWorkspace())
            ->findOrFail($id);

        if (empty($employee->email)) {
            return redirect()->back()->with('error', 'Employee does not have an email address.');
        }

        if ($this->essService->sendInvitation($employee)) {
            return redirect()->back()->with('success', "ESS invitation sent to {$employee->first_name} {$employee->last_name}.");
        }

        return redirect()->back()->with('error', 'Failed to send ESS invitation. Please try again.');
    }

    /**
     * Resend ESS invitation.
     */
    public function resendInvitation(Request $request, $id)
    {
        return $this->sendInvitation($request, $id);
    }

    /**
     * Send bulk ESS invitations.
     */
    public function sendBulkInvitations(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        $results = $this->essService->sendBulkInvitations($request->employee_ids);

        $message = "Invitations queued: {$results['success']} queued for sending, {$results['failed']} failed to queue, {$results['skipped']} skipped (already have access).";

        return redirect()->back()->with('success', $message);
    }

    /**
     * Disable ESS access for an employee.
     */
    public function disableAccess($id)
    {
        $employee = Employee::where('workspace_id', getActiveWorkspace())
            ->findOrFail($id);

        if ($this->essService->disableAccess($employee)) {
            return redirect()->back()->with('success', "ESS access disabled for {$employee->first_name} {$employee->last_name}.");
        }

        return redirect()->back()->with('error', 'Failed to disable ESS access.');
    }

    /**
     * Enable ESS access for an employee.
     */
    public function enableAccess($id)
    {
        $employee = Employee::where('workspace_id', getActiveWorkspace())
            ->findOrFail($id);

        if (!$employee->password) {
            return redirect()->back()->with('error', 'Employee must set up their password first. Send them an invitation.');
        }

        if ($this->essService->enableAccess($employee)) {
            return redirect()->back()->with('success', "ESS access enabled for {$employee->first_name} {$employee->last_name}.");
        }

        return redirect()->back()->with('error', 'Failed to enable ESS access.');
    }

    /**
     * Get ESS status label for an employee.
     */
    private function getEssStatus(Employee $employee): array
    {
        if ($employee->ess_enabled && $employee->password) {
            return [
                'label' => 'Active',
                'class' => 'success',
            ];
        }

        if ($employee->ess_setup_token && $employee->ess_setup_token_expires_at?->isFuture()) {
            return [
                'label' => 'Pending Setup',
                'class' => 'warning',
            ];
        }

        if ($employee->ess_setup_token && $employee->ess_setup_token_expires_at?->isPast()) {
            return [
                'label' => 'Invite Expired',
                'class' => 'danger',
            ];
        }

        if (!$employee->ess_enabled && $employee->password) {
            return [
                'label' => 'Disabled',
                'class' => 'secondary',
            ];
        }

        return [
            'label' => 'Not Invited',
            'class' => 'info',
        ];
    }
}
