{{-- Admin → Customer: Invoice Generated --}}
@extends('emails.layouts.admin')

@section('preheader', 'Invoice #' . $invoice->invoice_number . ' — R ' . number_format($invoice->total_amount, 2) . ' due')

@section('content')
<h1 style="margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #111827;">Invoice #{{ $invoice->invoice_number }}</h1>
<p style="margin: 0 0 24px; color: #6b7280; font-size: 15px;">Your invoice is ready for payment.</p>

<p style="margin: 0 0 24px; color: #374151;">Dear {{ $user->name }},</p>
<p style="margin: 0 0 24px; color: #374151;">Your invoice for payroll services is now available.</p>

{{-- Invoice details panel --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; margin: 0 0 24px;">
    <tr>
        <td style="padding: 20px 24px;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <td style="padding: 6px 0; color: #6b7280; font-size: 14px; border-bottom: 1px solid #f3f4f6;">Billing Period</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; border-bottom: 1px solid #f3f4f6;">{{ $invoice->billingCycle ? formatDate($invoice->period_start) . ' – ' . formatDate($invoice->period_end) : 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #6b7280; font-size: 14px; border-bottom: 1px solid #f3f4f6;">Payslips Processed</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; border-bottom: 1px solid #f3f4f6;">{{ number_format($invoice->total_payslips) }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #6b7280; font-size: 14px; border-bottom: 1px solid #f3f4f6;">Due Date</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; border-bottom: 1px solid #f3f4f6;">{{ $invoice->due_date ? formatDate($invoice->due_date) : 'Upon receipt' }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- Line items table --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="font-size: 14px; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; margin: 0 0 24px;">
    <tr style="background-color: #f9fafb;">
        <td style="padding: 10px 16px; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Description</td>
        <td style="padding: 10px 16px; font-weight: 600; color: #374151; text-align: right; border-bottom: 1px solid #e5e7eb;">Amount</td>
    </tr>
    <tr>
        <td style="padding: 10px 16px; border-bottom: 1px solid #f3f4f6; color: #374151;">Payslip Processing ({{ number_format($invoice->total_payslips) }} payslips)</td>
        <td style="padding: 10px 16px; text-align: right; border-bottom: 1px solid #f3f4f6; color: #374151;">R {{ number_format($invoice->subtotal, 2) }}</td>
    </tr>
    @if($invoice->tax_amount > 0)
    <tr>
        <td style="padding: 10px 16px; border-bottom: 1px solid #f3f4f6; color: #374151;">VAT (15%)</td>
        <td style="padding: 10px 16px; text-align: right; border-bottom: 1px solid #f3f4f6; color: #374151;">R {{ number_format($invoice->tax_amount, 2) }}</td>
    </tr>
    @endif
    <tr style="background-color: #f9fafb;">
        <td style="padding: 12px 16px; font-weight: 700; color: #111827;">Total</td>
        <td style="padding: 12px 16px; text-align: right; font-weight: 700; font-size: 16px; color: #111827;">R {{ number_format($invoice->total_amount, 2) }}</td>
    </tr>
</table>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 24px;">
    <tr>
        <td align="center">
            <a href="{{ $paymentUrl }}" style="display: inline-block; padding: 12px 28px; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 14px; border-radius: 8px;">Pay Now</a>
        </td>
    </tr>
</table>

<div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 14px 18px; border-radius: 0 8px 8px 0; margin: 0 0 16px; font-size: 14px; color: #1e40af;">
    <strong>Payment Methods:</strong> Online payment via PayFast (Credit Card, Instant EFT) or EFT to our bank account (details on invoice).
</div>

<p style="font-size: 13px; color: #9ca3af; margin: 16px 0 0;">This is an automated email. Please ensure payment by the due date to avoid service interruptions.</p>
@endsection
