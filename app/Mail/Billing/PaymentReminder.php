<?php

namespace App\Mail\Billing;

use App\Models\Billing\Invoice;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PaymentReminder extends BillingMailable
{

    public Invoice $invoice;
    public int $daysOverdue;
    public bool $isUrgent;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
        $this->daysOverdue = $invoice->due_date ? $invoice->due_date->diffInDays(now()) : 0;
        $this->isUrgent = $this->daysOverdue > 7;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isUrgent 
            ? 'URGENT: Payment Overdue - Invoice #' . $this->invoice->invoice_number
            : 'Payment Reminder - Invoice #' . $this->invoice->invoice_number;

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
            view: 'emails.billing.payment-reminder',
            with: [
                'invoice' => $this->invoice,
                'user' => $this->invoice->user,
                'daysOverdue' => $this->daysOverdue,
                'isUrgent' => $this->isUrgent,
                'paymentUrl' => route('my-billing.pay', $this->invoice->id),
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
