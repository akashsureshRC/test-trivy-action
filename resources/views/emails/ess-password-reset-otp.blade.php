{{-- Customer → Employee: ESS Password Reset OTP --}}
@extends('emails.layouts.company')

@section('preheader', 'Your password reset OTP code — valid for ' . $expiryMinutes . ' minutes')

@section('content')
<h1 style="margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #111827;">Password Reset OTP</h1>
<p style="margin: 0 0 24px; color: #6b7280; font-size: 15px;">Use this one-time code to reset your password.</p>

<p style="margin: 0 0 16px; color: #374151;">Hello {{ $employee->first_name }},</p>
<p style="margin: 0 0 24px; color: #374151;">We received a request to reset your Employee Self-Service password. Enter the code below in the app to proceed:</p>

{{-- OTP display --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 28px;">
    <tr>
        <td align="center">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; border-radius: 12px; width: 100%; max-width: 380px;">
                <tr>
                    <td style="padding: 28px 32px; text-align: center;">
                        <p style="margin: 0 0 6px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px; color: rgba(255,255,255,0.8);">Your OTP Code</p>
                        <p style="margin: 0 0 10px; font-size: 44px; font-weight: 700; letter-spacing: 10px; font-family: 'Courier New', monospace; color: #ffffff;">{{ $otp }}</p>
                        <p style="margin: 0; font-size: 13px; color: rgba(255,255,255,0.75);">Valid for {{ $expiryMinutes }} minutes</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- How to use --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 10px; margin: 0 0 24px;">
    <tr>
        <td style="padding: 18px 22px;">
            <p style="margin: 0 0 12px; font-weight: 700; font-size: 14px; color: #0369a1;">How to Use This OTP</p>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <td style="padding: 5px 0; font-size: 14px; color: #374151;">
                        <span style="display: inline-block; width: 24px; height: 24px; line-height: 24px; text-align: center; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff; font-size: 12px; font-weight: 700; border-radius: 50%; margin-right: 8px; vertical-align: middle;">1</span>
                        Open the ESS mobile app
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; font-size: 14px; color: #374151;">
                        <span style="display: inline-block; width: 24px; height: 24px; line-height: 24px; text-align: center; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff; font-size: 12px; font-weight: 700; border-radius: 50%; margin-right: 8px; vertical-align: middle;">2</span>
                        Go to "Forgot Password"
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; font-size: 14px; color: #374151;">
                        <span style="display: inline-block; width: 24px; height: 24px; line-height: 24px; text-align: center; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff; font-size: 12px; font-weight: 700; border-radius: 50%; margin-right: 8px; vertical-align: middle;">3</span>
                        Enter the 6-digit code above
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; font-size: 14px; color: #374151;">
                        <span style="display: inline-block; width: 24px; height: 24px; line-height: 24px; text-align: center; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff; font-size: 12px; font-weight: 700; border-radius: 50%; margin-right: 8px; vertical-align: middle;">4</span>
                        Create your new password
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- Expiry warning --}}
<div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 0 8px 8px 0; margin: 0 0 20px; font-size: 14px; color: #92400e;">
    <strong>Important:</strong> This OTP will expire in {{ $expiryMinutes }} minutes. If you don't use it in time, you'll need to request a new one.
</div>

{{-- Security notice --}}
<div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 12px 16px; border-radius: 0 8px 8px 0; margin: 0 0 24px; font-size: 14px; color: #991b1b;">
    <strong>Security Notice:</strong> If you didn't request a password reset, please ignore this email and contact your HR department. Your account is secure — no changes will be made without this OTP.
</div>

{{-- Security tips --}}
<p style="font-size: 13px; color: #6b7280; margin: 0 0 6px; font-weight: 600;">For your security:</p>
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr><td style="padding: 3px 0 3px 12px; font-size: 13px; color: #6b7280;">&bull; Never share this OTP with anyone</td></tr>
    <tr><td style="padding: 3px 0 3px 12px; font-size: 13px; color: #6b7280;">&bull; We will never ask for your OTP via email or phone</td></tr>
    <tr><td style="padding: 3px 0 3px 12px; font-size: 13px; color: #6b7280;">&bull; Use this code only in the official ESS mobile app</td></tr>
</table>
@endsection
