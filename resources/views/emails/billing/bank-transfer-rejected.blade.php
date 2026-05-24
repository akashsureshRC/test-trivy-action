{{-- Admin → Customer: Bank Transfer Rejected --}}
@extends('emails.layouts.admin')

@section('preheader', 'Your EFT payment proof for Invoice #' . $invoice->invoice_number . ' could not be verified')

@section('content')
<h1 style="margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #111827;">Payment Proof Rejected</h1>
<p style="margin: 0 0 24px; color: #991b1b; font-size: 15px;">We could not verify your EFT payment submission.</p>

<p style="margin: 0 0 24px; color: #374151;">Dear {{ $user->name }},</p>
<p style="margin: 0 0 24px; color: #374151;">Unfortunately, we were unable to verify your EFT payment submission for Invoice #{{ $invoice->invoice_number }}.</p>

{{-- Submission details panel --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; margin: 0 0 24px;">
    <tr>
        <td style="padding: 20px 24px;">
            <p style="margin: 0 0 14px; font-weight: 700; font-size: 15px; color: #991b1b;">Submission Details</p>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <td style="padding: 6px 0; color: #374151; font-size: 14px; border-bottom: 1px solid #fee2e2;">Invoice Number</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; border-bottom: 1px solid #fee2e2;">{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #374151; font-size: 14px; border-bottom: 1px solid #fee2e2;">Amount Claimed</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; border-bottom: 1px solid #fee2e2;">R {{ number_format($submission->amount, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #374151; font-size: 14px; border-bottom: 1px solid #fee2e2;">Bank Reference</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; border-bottom: 1px solid #fee2e2;">{{ $submission->bank_reference }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #374151; font-size: 14px; border-bottom: 1px solid #fee2e2;">Payment Date</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #111827; font-size: 14px; border-bottom: 1px solid #fee2e2;">{{ formatDate($submission->payment_date) }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #374151; font-size: 14px;">Status</td>
                    <td style="padding: 6px 0; text-align: right; font-size: 14px;"><span style="display: inline-block; padding: 2px 10px; font-size: 12px; font-weight: 600; border-radius: 20px; background-color: #fee2e2; color: #991b1b;">REJECTED</span></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- Rejection reason --}}
<div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 14px 18px; border-radius: 0 8px 8px 0; margin: 0 0 24px; font-size: 14px; color: #991b1b;">
    <strong>Reason for Rejection:</strong><br>
    {{ $submission->rejection_reason }}
</div>

<p style="font-weight: 600; font-size: 15px; color: #111827; margin: 0 0 12px;">What to Do Next</p>
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
        <td style="padding: 10px 0; font-size: 14px; color: #374151;">
            <span style="display: inline-block; width: 28px; height: 28px; line-height: 28px; text-align: center; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff; font-size: 13px; font-weight: 700; border-radius: 50%; margin-right: 10px; vertical-align: middle;">1</span>
            Verify the payment was processed by your bank
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 0; font-size: 14px; color: #374151;">
            <span style="display: inline-block; width: 28px; height: 28px; line-height: 28px; text-align: center; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff; font-size: 13px; font-weight: 700; border-radius: 50%; margin-right: 10px; vertical-align: middle;">2</span>
            Ensure the bank reference and amount are correct
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 0; font-size: 14px; color: #374151;">
            <span style="display: inline-block; width: 28px; height: 28px; line-height: 28px; text-align: center; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff; font-size: 13px; font-weight: 700; border-radius: 50%; margin-right: 10px; vertical-align: middle;">3</span>
            Submit a new proof with the correct details
        </td>
    </tr>
</table>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 28px 0;">
    <tr>
        <td align="center">
            <a href="{{ $invoiceUrl }}" style="display: inline-block; padding: 12px 28px; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 14px; border-radius: 8px;">View Invoice &amp; Resubmit</a>
        </td>
    </tr>
</table>

<p style="font-size: 13px; color: #9ca3af; margin: 0;">This invoice is still outstanding. Please make payment or submit corrected proof to avoid service interruption.</p>
@endsection
