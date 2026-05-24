{{-- Admin → Customer: Welcome Email --}}
@extends('emails.layouts.admin')

@section('preheader', 'Welcome to ' . ($brand['company_name'] ?? 'RC ClearPay') . ' — your free trial starts now!')

@section('content')
<h1 style="margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #111827;">Welcome aboard!</h1>
<p style="margin: 0 0 24px; color: #6b7280; font-size: 15px;">Your account is ready. Let's get you started.</p>

<p style="margin: 0 0 16px; color: #374151;">Dear {{ $user->name }},</p>
<p style="margin: 0 0 24px; color: #374151;">Thank you for choosing <strong>{{ $brand['company_name'] ?? config('app.name') }}</strong> for your payroll management needs. We're excited to have you on board!</p>

{{-- Trial details panel --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; margin: 0 0 24px;">
    <tr>
        <td style="padding: 20px 24px;">
            <p style="margin: 0 0 14px; font-weight: 700; font-size: 15px; color: #166534;">Your Free Trial</p>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <td style="padding: 6px 0; color: #374151; font-size: 14px; border-bottom: 1px solid #dcfce7;">Trial Duration</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; border-bottom: 1px solid #dcfce7;">{{ $trialDays }} days</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #374151; font-size: 14px; border-bottom: 1px solid #dcfce7;">Free Payslips</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; border-bottom: 1px solid #dcfce7;">{{ $trialPayslips }} payslips</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #374151; font-size: 14px;">Credit Card Required</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #166534; font-size: 14px;">No</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p style="font-weight: 600; font-size: 16px; color: #111827; margin: 0 0 16px;">Get Started in 3 Steps</p>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
        <td style="padding: 10px 0; font-size: 14px; color: #374151;">
            <span style="display: inline-block; width: 28px; height: 28px; line-height: 28px; text-align: center; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff; font-size: 13px; font-weight: 700; border-radius: 50%; margin-right: 10px; vertical-align: middle;">1</span>
            <strong>Add Company Details</strong> — Set up your company profile
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 0; font-size: 14px; color: #374151;">
            <span style="display: inline-block; width: 28px; height: 28px; line-height: 28px; text-align: center; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff; font-size: 13px; font-weight: 700; border-radius: 50%; margin-right: 10px; vertical-align: middle;">2</span>
            <strong>Add Employees</strong> — Import or manually add your team
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 0; font-size: 14px; color: #374151;">
            <span style="display: inline-block; width: 28px; height: 28px; line-height: 28px; text-align: center; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff; font-size: 13px; font-weight: 700; border-radius: 50%; margin-right: 10px; vertical-align: middle;">3</span>
            <strong>Run Payroll</strong> — Process payslips in a few clicks
        </td>
    </tr>
</table>

{{-- CTA --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 28px 0;">
    <tr>
        <td align="center">
            <!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" href="{{ $dashboardUrl }}" style="height:44px;v-text-anchor:middle;width:200px;" arcsize="18%" fillcolor="{{ $brand['accent_color'] ?? '#3956ca' }}"><center style="color:#ffffff;font-family:sans-serif;font-size:14px;font-weight:bold;">Go to Dashboard</center></v:roundrect><![endif]-->
            <!--[if !mso]><!--><a href="{{ $dashboardUrl }}" style="display: inline-block; padding: 12px 28px; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 14px; border-radius: 8px;">Go to Dashboard</a><!--<![endif]-->
        </td>
    </tr>
</table>

<hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">

<p style="font-weight: 600; font-size: 15px; color: #111827; margin: 0 0 12px;">What You Get</p>
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr><td style="padding: 4px 0; font-size: 14px; color: #374151;">&#10003; Automated Tax Calculations — PAYE, UIF, SDL</td></tr>
    <tr><td style="padding: 4px 0; font-size: 14px; color: #374151;">&#10003; Employee Self-Service Portal</td></tr>
    <tr><td style="padding: 4px 0; font-size: 14px; color: #374151;">&#10003; South African Compliance Ready</td></tr>
    <tr><td style="padding: 4px 0; font-size: 14px; color: #374151;">&#10003; Secure &amp; Reliable Infrastructure</td></tr>
</table>

<hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">

<p style="font-size: 14px; color: #6b7280; margin: 0 0 20px;">After your trial, you'll move to our flexible <strong>pay-per-payslip</strong> model — no monthly fees, no commitment.</p>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 8px;">
    <tr>
        <td align="center">
            <a href="{{ $pricingUrl }}" style="display: inline-block; padding: 10px 24px; background-color: transparent; color: {{ $brand['accent_color'] ?? '#3956ca' }} !important; text-decoration: none; font-weight: 600; font-size: 14px; border-radius: 8px; border: 2px solid {{ $brand['accent_color'] ?? '#3956ca' }};">View Pricing</a>
        </td>
    </tr>
</table>

<p style="font-size: 13px; color: #9ca3af; margin: 20px 0 0;">You received this email because you created an account on {{ $brand['company_name'] ?? config('app.name') }}.</p>
@endsection
