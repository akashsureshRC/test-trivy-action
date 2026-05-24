<?php

namespace App\Mail\Billing;

use App\Models\Billing\BillingPayment;
use App\Models\Billing\Invoice;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PaymentReceived extends BillingMailable
{

    public Invoice $invoice;
    public BillingPayment $payment;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice, BillingPayment $payment)
    {
        $this->invoice = $invoice;
        $this->payment = $payment;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Received - Invoice #' . $this->invoice->invoice_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.billing.payment-received',
            with: [
                'invoice' => $this->invoice,
                'payment' => $this->payment,
                'user' => $this->invoice->user,
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
