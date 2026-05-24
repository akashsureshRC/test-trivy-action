<?php

namespace App\Jobs;

use App\Models\EmailTemplate;
use App\Models\Hrm\Employee;
use App\Models\Hrm\PaySlip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class SendPayslipEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    protected $payslipId;
    protected $term;
    protected $userId;
    protected $workspaceId;

    /**
     * Create a new job instance.
     *
     * @param int $payslipId
     * @param string $term
     * @param int $userId
     * @param int $workspaceId
     */
    public function __construct(int $payslipId, string $term, int $userId, int $workspaceId)
    {
        $this->payslipId = $payslipId;
        $this->term = $term;
        $this->userId = $userId;
        $this->workspaceId = $workspaceId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $payslip = PaySlip::find($this->payslipId);
        
        if (!$payslip) {
            Log::warning('SendPayslipEmailJob: Payslip not found', ['payslip_id' => $this->payslipId]);
            return;
        }

        $employee = Employee::find($payslip->employee_id);
        
        if (!$employee || !$employee->email) {
            Log::warning('SendPayslipEmailJob: Employee or email not found', [
                'payslip_id' => $this->payslipId,
                'employee_id' => $payslip->employee_id
            ]);
            return;
        }

        $payslipIdEncrypted = Crypt::encrypt($payslip->id);
        $payslipUrl = route('payslip.payslipPdf', $payslipIdEncrypted);

        $uArr = [
            'payslip_email' => $employee->email,
            'name' => $employee->first_name . ' ' . $employee->last_name,
            'url' => $payslipUrl,
            'salary_month' => $this->term,
        ];

        try {
            EmailTemplate::sendEmailTemplate('New Payroll', [$employee->email], $uArr, $this->userId, $this->workspaceId);
            
            Log::info('SendPayslipEmailJob: Email sent successfully', [
                'payslip_id' => $this->payslipId,
                'employee_email' => $employee->email
            ]);
        } catch (\Exception $e) {
            Log::error('SendPayslipEmailJob: Failed to send email', [
                'payslip_id' => $this->payslipId,
                'employee_email' => $employee->email,
                'error' => $e->getMessage()
            ]);
            
            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('SendPayslipEmailJob: Job failed after all retries', [
            'payslip_id' => $this->payslipId,
            'error' => $exception->getMessage()
        ]);
    }
}
