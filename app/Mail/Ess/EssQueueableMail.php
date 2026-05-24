<?php

namespace App\Mail\Ess;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Hrm\Employee;

abstract class EssQueueableMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The employee this mail is being sent to.
     * Subclasses must declare this property so the base send() can
     * resolve the correct company SMTP at queue-processing time.
     */
    public Employee $employee;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 60;

    /**
     * Send the message.
     *
     * Applies the company's SMTP configuration at queue-worker processing
     * time (not at dispatch time) so the correct mailer is active even
     * when a separate queue worker process handles the job.
     *
     * @param  \Illuminate\Contracts\Mail\Factory|\Illuminate\Contracts\Mail\Mailer  $mailer
     * @return \Illuminate\Mail\SentMessage|null
     */
    public function send($mailer)
    {
        // Apply company SMTP config for this employee's workspace
        setCompanyConfigEmailForEmployee($this->employee);

        // Purge the cached SMTP transport so Laravel rebuilds it
        // with the updated config values
        if (app()->bound('mail.manager')) {
            app('mail.manager')->purge('smtp');
        }

        return parent::send($mailer);
    }
}
