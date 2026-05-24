<?php

namespace App\Mail\Billing;

use App\Models\Billing\BankTransferPayment;
use App\Models\Billing\Invoice;
use App\Models\User;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class BankTransferSubmitted extends BillingMailable
{

    public BankTransferPayment $submission;
    public Invoice $invoice;
    public User $admin;

    /**
     * Create a new message instance.
     */
    public function __construct(BankTransferPayment $submission, Invoice $invoice, User $admin)
    {
        $this->submission = $submission;
        $this->invoice = $invoice;
        $this->admin = $admin;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New EFT Payment Proof Submitted - Invoice #' . $this->invoice->invoice_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.billing.bank-transfer-submitted',
            with: [
                'submission' => $this->submission,
                'invoice' => $this->invoice,
                'admin' => $this->admin,
                'customer' => $this->invoice->user,
                'reviewUrl' => route('billing.invoices.show', $this->invoice->id),
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
