<?php

namespace App\Mail\Billing;

use App\Models\Billing\Invoice;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class InvoiceGenerated extends BillingMailable
{

    public Invoice $invoice;
    public bool $attachPdf;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice, bool $attachPdf = true)
    {
        $this->invoice = $invoice;
        $this->attachPdf = $attachPdf;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice #' . $this->invoice->invoice_number . ' - RC ClearPay',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.billing.invoice-generated',
            with: [
                'invoice' => $this->invoice,
                'user' => $this->invoice->user,
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
        if (!$this->attachPdf) {
            return [];
        }

        // Generate PDF and attach
        $pdfGenerator = app(\App\Services\InvoicePdfService::class);
        $pdfPath = $pdfGenerator->generateAndSave($this->invoice);

        if ($pdfPath && file_exists($pdfPath)) {
            return [
                Attachment::fromPath($pdfPath)
                    ->as('Invoice-' . $this->invoice->invoice_number . '.pdf')
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
