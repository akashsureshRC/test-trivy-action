{{-- Customer → Employee: ESS Password Reset --}}
@extends('emails.layouts.company')

@section('preheader', 'Reset your Employee Self-Service password')

@section('content')
<h1 style="margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #111827;">Password Reset Request</h1>
<p style="margin: 0 0 24px; color: #6b7280; font-size: 15px;">We received a request to reset your password.</p>

<p style="margin: 0 0 16px; color: #374151;">Hello {{ $employee->first_name }},</p>
<p style="margin: 0 0 24px; color: #374151;">We received a request to reset your Employee Self-Service password. Click the button below to create a new password:</p>

{{-- CTA --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 8px 0 28px;">
    <tr>
        <td align="center">
            <a href="{{ $resetUrl }}" style="display: inline-block; padding: 14px 32px; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 15px; border-radius: 8px;">Reset My Password</a>
        </td>
    </tr>
</table>

{{-- Expiry warning --}}
<div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 0 8px 8px 0; margin: 0 0 20px; font-size: 14px; color: #92400e;">
    <strong>Note:</strong> This link will expire in 48 hours for security reasons.
</div>

{{-- Security notice --}}
<div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 12px 16px; border-radius: 0 8px 8px 0; margin: 0 0 24px; font-size: 14px; color: #991b1b;">
    <strong>Didn't request this?</strong> If you did not request a password reset, please ignore this email. Your password will remain unchanged and your account is secure.
</div>

{{-- Fallback link --}}
<p style="font-size: 12px; color: #9ca3af; margin: 0; word-break: break-all;">If the button above doesn't work, copy and paste this link into your browser:<br>{{ $resetUrl }}</p>
@endsection
