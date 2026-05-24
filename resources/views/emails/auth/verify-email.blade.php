{{-- Verify Email Address — Branded style --}}
@extends('emails.layouts.admin')

@section('preheader', 'Verify your email address to activate your account')

@section('content')
<h1 style="margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #111827;">Verify your email address</h1>
<p style="margin: 0 0 24px; color: #6b7280; font-size: 15px;">One quick step to secure and activate your account.</p>

<p style="margin: 0 0 16px; color: #374151;">Hi {{ $user->name ?? __('there') }},</p>
<p style="margin: 0 0 20px; color: #374151;">Thanks for signing up for <strong>{{ $brand['company_name'] ?? config('app.name') }}</strong>. Please verify your email address to continue.</p>

<div class="alert-info">
    {{ __('Click the button below to verify your email address.') }}
</div>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 24px 0;">
    <tr>
        <td align="center">
            <!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" href="{{ $url }}" style="height:44px;v-text-anchor:middle;width:220px;" arcsize="18%" fillcolor="{{ $brand['accent_color'] ?? '#3956ca' }}"><center style="color:#ffffff;font-family:sans-serif;font-size:14px;font-weight:bold;">Verify Email Address</center></v:roundrect><![endif]-->
            <!--[if !mso]><!--><a href="{{ $url }}" class="email-btn">{{ __('Verify Email Address') }}</a><!--<![endif]-->
        </td>
    </tr>
</table>

<p style="margin: 0 0 8px; color: #6b7280; font-size: 13px;">{{ __('If you did not create an account, no further action is required.') }}</p>
<p style="margin: 0; color: #9ca3af; font-size: 12px; word-break: break-all;">{{ __('If the button does not work, copy and paste this URL into your browser:') }}<br>{{ $url }}</p>
@endsection
