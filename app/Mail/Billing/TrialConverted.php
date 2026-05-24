<?php

namespace App\Mail\Billing;

use App\Models\User;
use App\Models\Billing\Invoice;
use App\Models\Billing\BillingPayment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TrialConverted extends BillingMailable
{

    public User $user;
    public ?Invoice $invoice;
    public ?BillingPayment $payment;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, ?Invoice $invoice = null, ?BillingPayment $payment = null)
    {
        $this->user = $user;
        $this->invoice = $invoice;
        $this->payment = $payment;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to RC ClearPay - Your Paid Account is Now Active!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.billing.trial-converted',
            with: [
                'user' => $this->user,
                'invoice' => $this->invoice,
                'payment' => $this->payment,
                'dashboardUrl' => route('hrm.dashboard'),
                'billingUrl' => route('my-billing.index'),
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
