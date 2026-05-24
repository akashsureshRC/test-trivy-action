<?php

namespace App\Mail\Billing;

use App\Models\User;
use App\Models\Billing\BillingSetting;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class WelcomeEmail extends BillingMailable
{

    public User $user;
    public int $trialDays;
    public int $trialPayslips;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->trialDays = BillingSetting::getTrialDays();
        $this->trialPayslips = BillingSetting::getTrialPayslipsLimit();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to RC ClearPay - Your Free Trial Starts Now!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.billing.welcome',
            with: [
                'user' => $this->user,
                'trialDays' => $this->trialDays,
                'trialPayslips' => $this->trialPayslips,
                'dashboardUrl' => route('hrm.dashboard'),
                'pricingUrl' => route('my-billing.pricing'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
