{{-- Admin → Customer: Bank Transfer Approved --}}
@extends('emails.layouts.admin')

@section('preheader', 'Your EFT payment for Invoice #' . $invoice->invoice_number . ' has been approved')

@section('content')
<h1 style="margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #111827;">Payment Approved</h1>
<p style="margin: 0 0 24px; color: #166534; font-size: 15px;">Your EFT payment proof has been verified.</p>

<p style="margin: 0 0 24px; color: #374151;">Dear {{ $user->name }},</p>
<p style="margin: 0 0 24px; color: #374151;">Great news! Your EFT payment proof has been verified and approved.</p>

{{-- Payment details panel --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; margin: 0 0 24px;">
    <tr>
        <td style="padding: 20px 24px;">
            <p style="margin: 0 0 14px; font-weight: 700; font-size: 15px; color: #166534;">Payment Details</p>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <td style="padding: 6px 0; color: #374151; font-size: 14px; border-bottom: 1px solid #dcfce7;">Invoice Number</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; border-bottom: 1px solid #dcfce7;">{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #374151; font-size: 14px; border-bottom: 1px solid #dcfce7;">Amount</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 700; color: #166534; font-size: 16px; border-bottom: 1px solid #dcfce7;">R {{ number_format($submission->amount, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #374151; font-size: 14px; border-bottom: 1px solid #dcfce7;">Bank Reference</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; border-bottom: 1px solid #dcfce7;">{{ $submission->bank_reference }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #374151; font-size: 14px; border-bottom: 1px solid #dcfce7;">Payment Date</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; border-bottom: 1px solid #dcfce7;">{{ formatDate($submission->payment_date) }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #374151; font-size: 14px;">Status</td>
                    <td style="padding: 6px 0; text-align: right; font-size: 14px;"><span style="display: inline-block; padding: 2px 10px; font-size: 12px; font-weight: 600; border-radius: 20px; background-color: #dcfce7; color: #166534;">APPROVED</span></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p style="margin: 0 0 24px; color: #374151;">Your invoice has been marked as paid and your account is in good standing.</p>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 24px;">
    <tr>
        <td align="center">
            <a href="{{ $dashboardUrl }}" style="display: inline-block; padding: 12px 28px; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 14px; border-radius: 8px;">View Billing Dashboard</a>
        </td>
    </tr>
</table>

<p style="font-size: 13px; color: #9ca3af; margin: 0;">This is an automated payment confirmation. Please keep this email for your records.</p>
@endsection
