{{-- Admin → Customer: Trial Converted --}}
@extends('emails.layouts.admin')

@section('preheader', 'Your paid account is now active — welcome to ' . ($brand['company_name'] ?? 'RC ClearPay') . '!')

@section('content')
<h1 style="margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #111827;">You're All Set!</h1>
<p style="margin: 0 0 24px; color: #6b7280; font-size: 15px;">Your paid account is now fully active.</p>

<p style="margin: 0 0 24px; color: #374151;">Dear {{ $user->name }},</p>
<p style="margin: 0 0 24px; color: #374151;">Congratulations! Your account has been successfully upgraded.</p>

{{-- Account status panel --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; margin: 0 0 24px;">
    <tr>
        <td style="padding: 20px 24px;">
            <p style="margin: 0 0 14px; font-weight: 700; font-size: 15px; color: #166534;">Account Status</p>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <td style="padding: 6px 0; color: #374151; font-size: 14px; border-bottom: 1px solid #dcfce7;">Account Type</td>
                    <td style="padding: 6px 0; text-align: right; font-size: 14px; border-bottom: 1px solid #dcfce7;"><span style="display: inline-block; padding: 2px 10px; font-size: 12px; font-weight: 600; border-radius: 20px; background-color: #dcfce7; color: #166534;">Paid Customer</span></td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #374151; font-size: 14px; border-bottom: 1px solid #dcfce7;">Status</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #166534; font-size: 14px; border-bottom: 1px solid #dcfce7;">Active</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #374151; font-size: 14px;">Access</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #166534; font-size: 14px;">Unlimited</td>
                </tr>
                @if($payment)
                <tr>
                    <td style="padding: 6px 0; color: #374151; font-size: 14px; border-top: 1px solid #dcfce7;">Payment Reference</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; border-top: 1px solid #dcfce7;">{{ $payment->gateway_reference ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #374151; font-size: 14px;">Amount Paid</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px;">R {{ number_format($payment->amount, 2) }}</td>
                </tr>
                @endif
            </table>
        </td>
    </tr>
</table>

<p style="font-weight: 600; font-size: 15px; color: #111827; margin: 0 0 12px;">What's Included</p>
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr><td style="padding: 4px 0; font-size: 14px; color: #374151;">&#10003; Unlimited payslip processing</td></tr>
    <tr><td style="padding: 4px 0; font-size: 14px; color: #374151;">&#10003; Full employee management</td></tr>
    <tr><td style="padding: 4px 0; font-size: 14px; color: #374151;">&#10003; Complete tax calculations</td></tr>
    <tr><td style="padding: 4px 0; font-size: 14px; color: #374151;">&#10003; All reporting features</td></tr>
    <tr><td style="padding: 4px 0; font-size: 14px; color: #374151;">&#10003; Priority support</td></tr>
</table>

<hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">

<p style="font-weight: 600; font-size: 15px; color: #111827; margin: 0 0 12px;">How Billing Works</p>
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
        <td style="padding: 10px 0; font-size: 14px; color: #374151;">
            <span style="display: inline-block; width: 28px; height: 28px; line-height: 28px; text-align: center; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff; font-size: 13px; font-weight: 700; border-radius: 50%; margin-right: 10px; vertical-align: middle;">1</span>
            Process payslips as normal throughout the month
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 0; font-size: 14px; color: #374151;">
            <span style="display: inline-block; width: 28px; height: 28px; line-height: 28px; text-align: center; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff; font-size: 13px; font-weight: 700; border-radius: 50%; margin-right: 10px; vertical-align: middle;">2</span>
            We automatically generate an invoice at month end
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 0; font-size: 14px; color: #374151;">
            <span style="display: inline-block; width: 28px; height: 28px; line-height: 28px; text-align: center; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff; font-size: 13px; font-weight: 700; border-radius: 50%; margin-right: 10px; vertical-align: middle;">3</span>
            Pay at your convenience within the due date
        </td>
    </tr>
</table>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 28px 0;">
    <tr>
        <td align="center">
            <a href="{{ $billingUrl }}" style="display: inline-block; padding: 12px 28px; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 14px; border-radius: 8px;">View Billing Dashboard</a>
        </td>
    </tr>
</table>

<p style="font-size: 13px; color: #9ca3af; margin: 16px 0 0;">This email confirms your account upgrade. Please keep it for your records.</p>
@endsection
