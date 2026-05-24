<?php

namespace App\Mail\Billing;

use App\Models\Billing\BillingPayment;
use App\Models\Billing\BillingSetting;
use App\Models\Billing\Invoice;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PaymentConfirmation extends BillingMailable
{

    public Invoice $invoice;
    public BillingPayment $payment;
    public array $company;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice, BillingPayment $payment)
    {
        $this->invoice = $invoice;
        $this->payment = $payment;
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
            subject: 'Payment Confirmation - Invoice ' . $this->invoice->invoice_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.billing.payment-confirmation',
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
