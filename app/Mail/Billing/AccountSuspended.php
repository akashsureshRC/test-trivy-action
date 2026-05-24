<?php

namespace App\Mail\Billing;

use App\Models\Billing\Invoice;
use App\Models\User;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AccountSuspended extends BillingMailable
{

    public User $user;
    public ?Invoice $invoice;
    public string $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, ?Invoice $invoice = null, string $reason = '')
    {
        $this->user = $user;
        $this->invoice = $invoice;
        $this->reason = $reason ?: 'Overdue payment';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Account Suspended - RC ClearPay',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.billing.account-suspended',
            with: [
                'user' => $this->user,
                'invoice' => $this->invoice,
                'reason' => $this->reason,
                'paymentUrl' => $this->invoice ? route('my-billing.pay', $this->invoice->id) : route('my-billing.invoices'),
                'supportEmail' => config('mail.support_address', 'support@reliancecorp.co.za'),
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
