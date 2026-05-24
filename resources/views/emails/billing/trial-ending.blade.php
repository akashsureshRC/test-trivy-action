{{-- Admin → Customer: Trial Ending --}}
@extends('emails.layouts.admin')

@section('preheader')
@if($daysRemaining <= 0)Your trial has ended — continue with pay-per-payslip
@elseif($daysRemaining <= 3)Your trial ends in {{ $daysRemaining }} day{{ $daysRemaining === 1 ? '' : 's' }}
@else Trial update — {{ $daysRemaining }} days remaining @endif
@endsection

@section('content')
@if($daysRemaining <= 0)
<h1 style="margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #111827;">Your Trial Has Ended</h1>
@elseif($daysRemaining <= 3)
<h1 style="margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #111827;">Your Trial Ends Soon</h1>
@else
<h1 style="margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #111827;">Trial Update</h1>
@endif

<p style="margin: 0 0 24px; color: #374151;">Dear {{ $user->name }},</p>

@if($daysRemaining <= 0)
<p style="margin: 0 0 24px; color: #374151;">Your free trial has ended. Thank you for trying our payroll solution! Your account is now on our pay-per-payslip billing — you only pay for what you process.</p>
@elseif($daysRemaining <= 3)
<div class="alert-warning" style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 14px 18px; border-radius: 0 8px 8px 0; margin: 0 0 24px; font-size: 14px; color: #92400e;">
    Your free trial ends in <strong>{{ $daysRemaining }} {{ $daysRemaining === 1 ? 'day' : 'days' }}</strong>. Don't worry — your data stays safe, and you can continue using {{ $brand['company_name'] ?? config('app.name') }} on our pay-per-payslip model.
</div>
@else
<p style="margin: 0 0 24px; color: #374151;">Here's a quick update on your trial usage.</p>
@endif

{{-- Trial summary panel --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; margin: 0 0 24px;">
    <tr>
        <td style="padding: 20px 24px;">
            <p style="margin: 0 0 14px; font-weight: 700; font-size: 15px; color: #111827;">Trial Summary</p>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <td style="padding: 6px 0; color: #6b7280; font-size: 14px; border-bottom: 1px solid #f3f4f6;">Days Remaining</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: {{ $daysRemaining <= 3 ? '#dc2626' : '#111827' }}; font-size: 14px; border-bottom: 1px solid #f3f4f6;">{{ max(0, $daysRemaining) }} {{ $daysRemaining === 1 ? 'day' : 'days' }}</td>
                </tr>
                @if($payslipsLimit > 0)
                <tr>
                    <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">Trial Payslips Used</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px;">{{ $payslipsUsed }} of {{ $payslipsLimit }}</td>
                </tr>
                @endif
            </table>
        </td>
    </tr>
</table>

@if($daysRemaining > 0)
<p style="font-weight: 600; font-size: 15px; color: #111827; margin: 0 0 12px;">What Happens After Your Trial?</p>
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr><td style="padding: 4px 0; font-size: 14px; color: #374151;">&#10003; No monthly fees — only pay for what you use</td></tr>
    <tr><td style="padding: 4px 0; font-size: 14px; color: #374151;">&#10003; Volume discounts — lower rates as you grow</td></tr>
    <tr><td style="padding: 4px 0; font-size: 14px; color: #374151;">&#10003; No commitment — scale up or down anytime</td></tr>
</table>
@endif

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 28px 0;">
    <tr>
        <td align="center">
            <a href="{{ $pricingUrl }}" style="display: inline-block; padding: 12px 28px; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 14px; border-radius: 8px;">View Pricing</a>
        </td>
    </tr>
</table>

<hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">

{{-- Pricing tiers table --}}
<p style="font-weight: 600; font-size: 15px; color: #111827; margin: 0 0 16px;">Our Pricing Tiers</p>
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="font-size: 14px; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
    <tr style="background-color: #f9fafb;">
        <td style="padding: 10px 16px; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Payslips / Month</td>
        <td style="padding: 10px 16px; font-weight: 600; color: #374151; text-align: right; border-bottom: 1px solid #e5e7eb;">Price per Payslip</td>
    </tr>
    <tr><td style="padding: 8px 16px; border-bottom: 1px solid #f3f4f6;">1 – 50</td><td style="padding: 8px 16px; text-align: right; border-bottom: 1px solid #f3f4f6;">R 15.00</td></tr>
    <tr><td style="padding: 8px 16px; border-bottom: 1px solid #f3f4f6;">51 – 200</td><td style="padding: 8px 16px; text-align: right; border-bottom: 1px solid #f3f4f6;">R 12.00</td></tr>
    <tr><td style="padding: 8px 16px; border-bottom: 1px solid #f3f4f6;">201 – 500</td><td style="padding: 8px 16px; text-align: right; border-bottom: 1px solid #f3f4f6;">R 9.00</td></tr>
    <tr><td style="padding: 8px 16px; border-bottom: 1px solid #f3f4f6;">501 – 1 000</td><td style="padding: 8px 16px; text-align: right; border-bottom: 1px solid #f3f4f6;">R 6.00</td></tr>
    <tr><td style="padding: 8px 16px;">1 001+</td><td style="padding: 8px 16px; text-align: right;">R 4.00</td></tr>
</table>
<p style="font-size: 12px; color: #9ca3af; margin: 8px 0 20px;">Prices exclude VAT</p>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
        <td align="center">
            <a href="{{ $dashboardUrl }}" style="display: inline-block; padding: 10px 24px; background-color: transparent; color: {{ $brand['accent_color'] ?? '#3956ca' }} !important; text-decoration: none; font-weight: 600; font-size: 14px; border-radius: 8px; border: 2px solid {{ $brand['accent_color'] ?? '#3956ca' }};">Go to Dashboard</a>
        </td>
    </tr>
</table>

<p style="font-size: 13px; color: #9ca3af; margin: 20px 0 0;">This is an automated notification about your trial status.</p>
@endsection
