<?php

namespace App\Mail\Billing;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

abstract class BillingMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

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
     * Applies admin SMTP config before sending so that billing emails
     *
     * @param  \Illuminate\Contracts\Mail\Factory|\Illuminate\Contracts\Mail\Mailer  $mailer
     * @return \Illuminate\Mail\SentMessage|null
     */
    public function send($mailer)
    {
        // Apply admin SMTP configuration
        setAdminConfigEmail();

        // Purge the cached SMTP transport so Laravel rebuilds it
        // with the updated config values
        if (app()->bound('mail.manager')) {
            app('mail.manager')->purge('smtp');
        }

        return parent::send($mailer);
    }
}
