{{-- Admin → Customer: Payment Reminder --}}
@extends('emails.layouts.admin')

@section('preheader')
@if($isUrgent)URGENT: Invoice #{{ $invoice->invoice_number }} is {{ $daysOverdue }} days overdue
@else Payment reminder for Invoice #{{ $invoice->invoice_number }}@endif
@endsection

@section('content')
@if($isUrgent)
<h1 style="margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #dc2626;">Payment Overdue</h1>
<p style="margin: 0 0 24px; color: #991b1b; font-size: 15px;">Immediate attention required</p>
@else
<h1 style="margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #111827;">Payment Reminder</h1>
<p style="margin: 0 0 24px; color: #6b7280; font-size: 15px;">A friendly reminder about your invoice.</p>
@endif

<p style="margin: 0 0 24px; color: #374151;">Dear {{ $user->name }},</p>

@if($isUrgent)
<p style="margin: 0 0 24px; color: #374151;">This is an urgent reminder that your invoice is now <strong>{{ $daysOverdue }} days overdue</strong>. Please make payment immediately to avoid service interruption.</p>
@else
<p style="margin: 0 0 24px; color: #374151;">This is a friendly reminder that your invoice is due for payment.</p>
@endif

{{-- Invoice details panel --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: {{ $isUrgent ? '#fef2f2' : '#f9fafb' }}; border: 1px solid {{ $isUrgent ? '#fecaca' : '#e5e7eb' }}; border-radius: 10px; margin: 0 0 24px;">
    <tr>
        <td style="padding: 20px 24px;">
            <p style="margin: 0 0 14px; font-weight: 700; font-size: 15px; color: {{ $isUrgent ? '#991b1b' : '#111827' }};">Invoice Details</p>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <td style="padding: 6px 0; color: #6b7280; font-size: 14px; border-bottom: 1px solid {{ $isUrgent ? '#fee2e2' : '#f3f4f6' }};">Invoice Number</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; border-bottom: 1px solid {{ $isUrgent ? '#fee2e2' : '#f3f4f6' }};">{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #6b7280; font-size: 14px; border-bottom: 1px solid {{ $isUrgent ? '#fee2e2' : '#f3f4f6' }};">Amount Due</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 700; color: {{ $isUrgent ? '#dc2626' : '#111827' }}; font-size: 16px; border-bottom: 1px solid {{ $isUrgent ? '#fee2e2' : '#f3f4f6' }};">R {{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #6b7280; font-size: 14px; border-bottom: 1px solid {{ $isUrgent ? '#fee2e2' : '#f3f4f6' }};">Due Date</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: {{ $isUrgent ? '#dc2626' : '#111827' }}; font-size: 14px; border-bottom: 1px solid {{ $isUrgent ? '#fee2e2' : '#f3f4f6' }};">{{ $invoice->due_date ? formatDate($invoice->due_date) : 'Immediately' }}</td>
                </tr>
                @if($daysOverdue > 0)
                <tr>
                    <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">Days Overdue</td>
                    <td style="padding: 6px 0; text-align: right; font-size: 14px;"><span style="display: inline-block; padding: 2px 10px; font-size: 12px; font-weight: 600; border-radius: 20px; background-color: #fee2e2; color: #991b1b;">{{ $daysOverdue }} days</span></td>
                </tr>
                @endif
            </table>
        </td>
    </tr>
</table>

@if($isUrgent)
<div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 14px 18px; border-radius: 0 8px 8px 0; margin: 0 0 24px; font-size: 14px; color: #991b1b;">
    <strong>Important:</strong> If payment is not received within 7 days, your account may be suspended and you will be unable to process payslips until the outstanding balance is cleared.
</div>
@endif

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 24px;">
    <tr>
        <td align="center">
            <a href="{{ $paymentUrl }}" style="display: inline-block; padding: 12px 28px; background-color: {{ $isUrgent ? '#dc2626' : ($brand['accent_color'] ?? '#3956ca') }}; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 14px; border-radius: 8px;">Pay Now</a>
        </td>
    </tr>
</table>

<p style="font-size: 13px; color: #9ca3af; margin: 0;">If you have already made payment, please disregard this email — it may take a few hours for your payment to be reflected.</p>
@endsection
