{{-- Test Mail — sent from Settings to verify SMTP configuration --}}
@extends('emails.layouts.company')

@section('preheader', 'This is a test email to verify your SMTP configuration')

@section('content')
<h1 style="margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #111827;">{{ __('Test Email') }}</h1>
<p style="margin: 0 0 24px; color: #6b7280; font-size: 15px;">{{ __('SMTP configuration verification') }}</p>

<p style="margin: 0 0 16px; color: #374151;"><strong>{{ __('Hi Dear,') }}</strong></p>
<p style="margin: 0 0 16px; color: #374151;">{{ __('This mail send only for testing purpose.') }}</p>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 10px; margin: 0 0 24px;">
    <tr>
        <td style="padding: 16px 20px; text-align: center;">
            <p style="margin: 0; font-size: 14px; color: #065f46; font-weight: 600;">&#10003; {{ __('Your email configuration is working correctly!') }}</p>
        </td>
    </tr>
</table>

<p style="margin: 0 0 8px; color: #374151;">{{ __('Feel free to reach out if you have any questions.') }}</p>
<p style="margin: 0 0 24px; color: #374151;">{{ __('Thank you for your business!') }}</p>

<p style="margin: 0 0 4px; color: #374151; font-weight: 600;">{{ __('Regards,') }}</p>
<p style="margin: 0; color: #374151; font-weight: 600;">{{ $brand['company_name'] ?? config('app.name', 'RC ClearPay') }}</p>
@endsection

