{{-- Customer → Employee: ESS Invitation --}}
@extends('emails.layouts.company')

@section('preheader', 'You have been invited to access the Employee Self-Service portal')

@section('content')
<h1 style="margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #111827;">Welcome to Employee Self-Service</h1>
<p style="margin: 0 0 24px; color: #6b7280; font-size: 15px;">Your account is ready to be set up.</p>

<p style="margin: 0 0 16px; color: #374151;">Hello {{ $employee->first_name }},</p>
<p style="margin: 0 0 24px; color: #374151;">You've been invited to access the Employee Self-Service portal. Set up your account to get started.</p>

{{-- Feature list --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f9fafb; border-radius: 10px; margin: 0 0 24px;">
    <tr>
        <td style="padding: 20px 24px;">
            <p style="margin: 0 0 14px; font-weight: 700; font-size: 15px; color: #111827;">What You Can Do</p>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <td style="padding: 6px 0; font-size: 14px; color: #374151; border-bottom: 1px solid #e5e7eb;">
                        <span style="color: {{ $brand['accent_color'] ?? '#3956ca' }}; font-weight: 700; margin-right: 8px;">&#10003;</span>
                        View and download your payslips
                    </td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; font-size: 14px; color: #374151; border-bottom: 1px solid #e5e7eb;">
                        <span style="color: {{ $brand['accent_color'] ?? '#3956ca' }}; font-weight: 700; margin-right: 8px;">&#10003;</span>
                        Submit and track leave requests
                    </td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; font-size: 14px; color: #374151; border-bottom: 1px solid #e5e7eb;">
                        <span style="color: {{ $brand['accent_color'] ?? '#3956ca' }}; font-weight: 700; margin-right: 8px;">&#10003;</span>
                        Access your tax certificates
                    </td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; font-size: 14px; color: #374151;">
                        <span style="color: {{ $brand['accent_color'] ?? '#3956ca' }}; font-weight: 700; margin-right: 8px;">&#10003;</span>
                        Update your personal information
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p style="margin: 0 0 8px; color: #374151; font-size: 14px;">Click the button below to set up your password and activate your account:</p>

{{-- CTA --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 20px 0 24px;">
    <tr>
        <td align="center">
            <a href="{{ $setupUrl }}" style="display: inline-block; padding: 14px 32px; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 15px; border-radius: 8px;">Set Up My Account</a>
        </td>
    </tr>
</table>

{{-- Expiry warning --}}
<div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 0 8px 8px 0; margin: 0 0 24px; font-size: 14px; color: #92400e;">
    <strong>Note:</strong> This link will expire in 48 hours. If it expires, please contact your HR department for a new invitation.
</div>

{{-- Account details --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 10px; margin: 0 0 24px;">
    <tr>
        <td style="padding: 16px 20px;">
            <p style="margin: 0 0 8px; font-weight: 700; font-size: 13px; color: #0369a1; text-transform: uppercase; letter-spacing: 0.5px;">Your Account Details</p>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <td style="padding: 4px 0; color: #374151; font-size: 14px;">Employee ID</td>
                    <td style="padding: 4px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px;">{{ $employee->employee_id }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; color: #374151; font-size: 14px;">Email</td>
                    <td style="padding: 4px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px;">{{ $employee->email }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- Fallback link --}}
<p style="font-size: 12px; color: #9ca3af; margin: 0; word-break: break-all;">If the button above doesn't work, copy and paste this link into your browser:<br>{{ $setupUrl }}</p>
@endsection
