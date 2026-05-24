<?php

namespace App\Mail\Billing;

use App\Models\Billing\BankTransferPayment;
use App\Models\Billing\Invoice;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class BankTransferRejected extends BillingMailable
{

    public BankTransferPayment $submission;
    public Invoice $invoice;

    /**
     * Create a new message instance.
     */
    public function __construct(BankTransferPayment $submission, Invoice $invoice)
    {
        $this->submission = $submission;
        $this->invoice = $invoice;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Proof Rejected - Invoice #' . $this->invoice->invoice_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.billing.bank-transfer-rejected',
            with: [
                'submission' => $this->submission,
                'invoice' => $this->invoice,
                'user' => $this->invoice->user,
                'invoiceUrl' => route('my-billing.invoices.show', $this->invoice->id),
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
