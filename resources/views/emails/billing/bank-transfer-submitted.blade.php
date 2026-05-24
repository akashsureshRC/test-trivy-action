{{-- Admin → Admin (internal): Bank Transfer Submitted --}}
@extends('emails.layouts.admin')

@section('preheader', 'New EFT proof submitted for Invoice #' . $invoice->invoice_number)

@section('content')
<h1 style="margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #111827;">New EFT Payment Proof</h1>
<p style="margin: 0 0 24px; color: #6b7280; font-size: 15px;">A customer submission requires your review.</p>

<p style="margin: 0 0 24px; color: #374151;">Dear {{ $admin->name }},</p>
<p style="margin: 0 0 24px; color: #374151;">A customer has submitted proof of EFT payment that requires your review.</p>

{{-- Submission details panel --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; margin: 0 0 24px;">
    <tr>
        <td style="padding: 20px 24px;">
            <p style="margin: 0 0 14px; font-weight: 700; font-size: 15px; color: #111827;">Submission Details</p>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <td style="padding: 6px 0; color: #6b7280; font-size: 14px; border-bottom: 1px solid #f3f4f6;">Customer</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; border-bottom: 1px solid #f3f4f6;">{{ $customer->name }} ({{ $customer->email }})</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #6b7280; font-size: 14px; border-bottom: 1px solid #f3f4f6;">Invoice Number</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; border-bottom: 1px solid #f3f4f6;">{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #6b7280; font-size: 14px; border-bottom: 1px solid #f3f4f6;">Invoice Amount</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; border-bottom: 1px solid #f3f4f6;">R {{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #6b7280; font-size: 14px; border-bottom: 1px solid #f3f4f6;">Amount Claimed</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 700; color: #111827; font-size: 16px; border-bottom: 1px solid #f3f4f6;">R {{ number_format($submission->amount, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #6b7280; font-size: 14px; border-bottom: 1px solid #f3f4f6;">Bank Reference</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; border-bottom: 1px solid #f3f4f6;">{{ $submission->bank_reference }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #6b7280; font-size: 14px; {{ $submission->notes ? 'border-bottom: 1px solid #f3f4f6;' : '' }}">Payment Date</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; {{ $submission->notes ? 'border-bottom: 1px solid #f3f4f6;' : '' }}">{{ formatDate($submission->payment_date) }}</td>
                </tr>
                @if($submission->notes)
                <tr>
                    <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">Customer Notes</td>
                    <td style="padding: 6px 0; text-align: right; color: #111827; font-size: 14px; font-style: italic;">{{ $submission->notes }}</td>
                </tr>
                @endif
            </table>
        </td>
    </tr>
</table>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 24px;">
    <tr>
        <td align="center">
            <a href="{{ $reviewUrl }}" style="display: inline-block; padding: 12px 28px; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 14px; border-radius: 8px;">Review Submission</a>
        </td>
    </tr>
</table>

<div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 14px 18px; border-radius: 0 8px 8px 0; margin: 0 0 16px; font-size: 14px; color: #1e40af;">
    <strong>Quick Actions:</strong> If the payment is verified in your bank account, approve the submission. If it cannot be verified, reject with a reason.
</div>

<p style="font-size: 13px; color: #9ca3af; margin: 0;">This is an automated notification. The customer will be notified once you take action.</p>
@endsection
