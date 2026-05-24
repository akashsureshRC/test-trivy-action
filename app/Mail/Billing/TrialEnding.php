<?php

namespace App\Mail\Billing;

use App\Models\User;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TrialEnding extends BillingMailable
{

    public User $user;
    public int $daysRemaining;
    public int $payslipsUsed;
    public int $payslipsLimit;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, int $daysRemaining, int $payslipsUsed = 0, int $payslipsLimit = 0)
    {
        $this->user = $user;
        $this->daysRemaining = $daysRemaining;
        $this->payslipsUsed = $payslipsUsed;
        $this->payslipsLimit = $payslipsLimit;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->daysRemaining <= 0 
            ? 'Your Trial Has Ended - RC ClearPay'
            : "Your Trial Ends in {$this->daysRemaining} Days - RC ClearPay";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.billing.trial-ending',
            with: [
                'user' => $this->user,
                'daysRemaining' => $this->daysRemaining,
                'payslipsUsed' => $this->payslipsUsed,
                'payslipsLimit' => $this->payslipsLimit,
                'pricingUrl' => route('my-billing.pricing'),
                'dashboardUrl' => route('my-billing.index'),
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
