<?php

namespace App\Mail\Billing;

use App\Models\Billing\BillingSetting;
use App\Models\Billing\Invoice;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class InvoiceOverdueReminder extends BillingMailable
{

    public Invoice $invoice;
    public array $company;
    public int $daysOverdue;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
        $this->daysOverdue = $invoice->due_date ? now()->diffInDays($invoice->due_date) : 0;
        $this->company = [
            'name' => BillingSetting::get('company_name') ?: 'RC ClearPay',
            'email' => BillingSetting::get('company_email') ?: '',
            'phone' => BillingSetting::get('company_phone') ?: '',
            'address' => BillingSetting::get('company_address') ?: '',
        ];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'OVERDUE: Invoice ' . $this->invoice->invoice_number . ' requires immediate payment',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.billing.invoice-overdue',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
