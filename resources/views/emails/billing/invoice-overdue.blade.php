{{-- Admin → Customer: Invoice Overdue --}}
@extends('emails.layouts.admin')

@section('preheader', 'OVERDUE: Invoice ' . $invoice->invoice_number . ' — ' . $daysOverdue . ' days past due')

@section('content')
<h1 style="margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #dc2626;">Payment Overdue</h1>
<p style="margin: 0 0 24px; color: #991b1b; font-size: 15px;">Immediate action required</p>

<p style="margin: 0 0 24px; color: #374151;">Hello {{ $invoice->user->name ?? 'Valued Customer' }},</p>
<p style="margin: 0 0 24px; color: #374151;">This is a reminder that your invoice <strong>{{ $invoice->invoice_number }}</strong> is now <strong>overdue</strong>. Please arrange payment as soon as possible to avoid service interruption.</p>

{{-- Overdue counter --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; margin: 0 0 24px;">
    <tr>
        <td style="padding: 24px; text-align: center;">
            <p style="margin: 0 0 4px; font-size: 14px; font-weight: 600; color: #991b1b; text-transform: uppercase; letter-spacing: 0.5px;">Invoice Overdue</p>
            <p style="margin: 0 0 4px; font-size: 42px; font-weight: 800; color: #dc2626; line-height: 1.1;">{{ $daysOverdue }}</p>
            <p style="margin: 0; font-size: 14px; color: #dc2626;">days past due</p>
        </td>
    </tr>
</table>

{{-- Invoice details panel --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; margin: 0 0 24px;">
    <tr>
        <td style="padding: 20px 24px;">
            <p style="margin: 0 0 14px; font-weight: 700; font-size: 15px; color: #111827;">Invoice Details</p>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <td style="padding: 6px 0; color: #6b7280; font-size: 14px; border-bottom: 1px solid #f3f4f6;">Invoice Number</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; border-bottom: 1px solid #f3f4f6;">{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #6b7280; font-size: 14px; border-bottom: 1px solid #f3f4f6;">Issue Date</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; border-bottom: 1px solid #f3f4f6;">{{ formatDate($invoice->created_at) }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #6b7280; font-size: 14px; border-bottom: 1px solid #f3f4f6;">Due Date</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #dc2626; font-size: 14px; border-bottom: 1px solid #f3f4f6;">{{ $invoice->due_date ? formatDate($invoice->due_date) : 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">Amount Due</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 700; color: #dc2626; font-size: 18px;">R {{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 24px;">
    <tr>
        <td align="center">
            <a href="{{ route('my-billing.pay', $invoice->id) }}" style="display: inline-block; padding: 12px 28px; background-color: #dc2626; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 14px; border-radius: 8px;">Pay Now</a>
        </td>
    </tr>
</table>

<div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 14px 18px; border-radius: 0 8px 8px 0; margin: 0 0 24px; font-size: 14px; color: #92400e;">
    <strong>Important Notice:</strong>
    <ul style="margin: 8px 0 0; padding-left: 20px;">
        <li>Continued non-payment may result in suspension of services</li>
        <li>Contact us immediately if you're experiencing payment difficulties</li>
    </ul>
</div>

<p style="margin: 0 0 8px; font-size: 14px; color: #6b7280;">If you have already made this payment, please disregard this email. Payments can take 1–2 business days to reflect.</p>

<p style="font-size: 13px; color: #9ca3af; margin: 16px 0 0;">This is an automated reminder from {{ $company['name'] ?? $brand['company_name'] }}.</p>
@endsection
