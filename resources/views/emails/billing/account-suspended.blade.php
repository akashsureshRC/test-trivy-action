{{-- Admin → Customer: Account Suspended --}}
@extends('emails.layouts.admin')

@section('preheader', 'Your account has been suspended — action required')

@section('content')
<h1 style="margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #dc2626;">Account Suspended</h1>
<p style="margin: 0 0 24px; color: #991b1b; font-size: 15px;">Your account requires immediate attention.</p>

<p style="margin: 0 0 24px; color: #374151;">Dear {{ $user->name }},</p>
<p style="margin: 0 0 24px; color: #374151;">We regret to inform you that your {{ $brand['company_name'] ?? config('app.name') }} account has been <strong>suspended</strong> due to {{ $reason }}.</p>

{{-- Suspension details panel --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; margin: 0 0 24px;">
    <tr>
        <td style="padding: 20px 24px;">
            <p style="margin: 0 0 14px; font-weight: 700; font-size: 15px; color: #991b1b;">Suspension Details</p>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <td style="padding: 6px 0; color: #374151; font-size: 14px; border-bottom: 1px solid #fee2e2;">Account</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; border-bottom: 1px solid #fee2e2;">{{ $user->email }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #374151; font-size: 14px; border-bottom: 1px solid #fee2e2;">Reason</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #991b1b; font-size: 14px; border-bottom: 1px solid #fee2e2;">{{ $reason }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #374151; font-size: 14px; {{ $invoice ? 'border-bottom: 1px solid #fee2e2;' : '' }}">Suspended On</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; {{ $invoice ? 'border-bottom: 1px solid #fee2e2;' : '' }}">{{ formatDate(now()) }}</td>
                </tr>
                @if($invoice)
                <tr>
                    <td style="padding: 6px 0; color: #374151; font-size: 14px; border-bottom: 1px solid #fee2e2;">Outstanding Invoice</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; border-bottom: 1px solid #fee2e2;">#{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #374151; font-size: 14px;">Amount Due</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 700; color: #dc2626; font-size: 16px;">R {{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
                @endif
            </table>
        </td>
    </tr>
</table>

<p style="font-weight: 600; font-size: 15px; color: #111827; margin: 0 0 12px;">What This Means</p>
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr><td style="padding: 4px 0; font-size: 14px; color: #dc2626;">&#10007; You cannot process new payslips</td></tr>
    <tr><td style="padding: 4px 0; font-size: 14px; color: #dc2626;">&#10007; Payrun functionality is disabled</td></tr>
    <tr><td style="padding: 4px 0; font-size: 14px; color: #166534;">&#10003; You can still view existing data</td></tr>
    <tr><td style="padding: 4px 0; font-size: 14px; color: #166534;">&#10003; Employee self-service remains accessible</td></tr>
</table>

<hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">

<p style="font-weight: 600; font-size: 15px; color: #111827; margin: 0 0 12px;">How to Reactivate</p>
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
        <td style="padding: 10px 0; font-size: 14px; color: #374151;">
            <span style="display: inline-block; width: 28px; height: 28px; line-height: 28px; text-align: center; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff; font-size: 13px; font-weight: 700; border-radius: 50%; margin-right: 10px; vertical-align: middle;">1</span>
            Pay the outstanding balance
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 0; font-size: 14px; color: #374151;">
            <span style="display: inline-block; width: 28px; height: 28px; line-height: 28px; text-align: center; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff; font-size: 13px; font-weight: 700; border-radius: 50%; margin-right: 10px; vertical-align: middle;">2</span>
            Your account will be automatically reactivated
        </td>
    </tr>
</table>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 28px 0;">
    <tr>
        <td align="center">
            <a href="{{ $paymentUrl }}" style="display: inline-block; padding: 12px 28px; background-color: #dc2626; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 14px; border-radius: 8px;">Pay Outstanding Balance</a>
        </td>
    </tr>
</table>

@if(!empty($supportEmail))
<div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 14px 18px; border-radius: 0 8px 8px 0; margin: 0 0 16px; font-size: 14px; color: #1e40af;">
    If you believe this suspension was made in error or need to discuss payment arrangements, please contact us at <a href="mailto:{{ $supportEmail }}" style="color: #1e40af; text-decoration: underline;">{{ $supportEmail }}</a>.
</div>
@endif

<p style="font-size: 13px; color: #9ca3af; margin: 0;">This is an automated notification. Your prompt attention to this matter is appreciated.</p>
@endsection
