{{--
    Base Email Layout — RC ClearPay
    Shared by both Admin→Customer and Customer→Employee emails.

    Required variables:
      $brand  — array from getEmailBranding() containing:
        logo_url, company_name, accent_color, footer_text, support_email, address
    
    Sections:
      @yield('preheader')   — Hidden preview text
      @yield('content')     — Main body content
      @yield('footer-extra') — Additional footer content (optional)
--}}
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="format-detection" content="telephone=no,address=no,email=no,date=no,url=no">
    <title>{{ $brand['company_name'] ?? 'RC ClearPay' }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:AllowPNG/>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        /* Reset */
        body, table, td, p, a, li, blockquote { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; height: 100% !important; }
        a[x-apple-data-detectors] { color: inherit !important; text-decoration: none !important; font-size: inherit !important; font-family: inherit !important; font-weight: inherit !important; line-height: inherit !important; }

        /* Typography */
        body, td, th {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 15px;
            line-height: 1.6;
            color: #374151;
        }

        /* Links */
        a { color: {{ $brand['accent_color'] ?? '#3956ca' }}; text-decoration: none; }
        a:hover { text-decoration: underline; }

        /* Button */
        .email-btn {
            display: inline-block;
            padding: 12px 28px;
            background-color: {{ $brand['accent_color'] ?? '#3956ca' }};
            color: #ffffff !important;
            text-decoration: none !important;
            font-weight: 600;
            font-size: 14px;
            border-radius: 8px;
            mso-padding-alt: 0;
            text-align: center;
        }
        .email-btn:hover { opacity: 0.9; }

        .email-btn-outline {
            display: inline-block;
            padding: 10px 24px;
            background-color: transparent;
            color: {{ $brand['accent_color'] ?? '#3956ca' }} !important;
            text-decoration: none !important;
            font-weight: 600;
            font-size: 14px;
            border-radius: 8px;
            border: 2px solid {{ $brand['accent_color'] ?? '#3956ca' }};
            text-align: center;
        }

        /* Alert Boxes */
        .alert-info {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 14px 18px;
            border-radius: 0 8px 8px 0;
            margin: 16px 0;
            font-size: 14px;
            color: #1e40af;
        }
        .alert-success {
            background-color: #f0fdf4;
            border-left: 4px solid #22c55e;
            padding: 14px 18px;
            border-radius: 0 8px 8px 0;
            margin: 16px 0;
            font-size: 14px;
            color: #166534;
        }
        .alert-warning {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 14px 18px;
            border-radius: 0 8px 8px 0;
            margin: 16px 0;
            font-size: 14px;
            color: #92400e;
        }
        .alert-danger {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 14px 18px;
            border-radius: 0 8px 8px 0;
            margin: 16px 0;
            font-size: 14px;
            color: #991b1b;
        }

        /* Data Panel */
        .data-panel {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 20px 24px;
            margin: 20px 0;
        }
        .data-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
        }
        .data-row:last-child { border-bottom: none; }
        .data-label { color: #6b7280; font-weight: 500; }
        .data-value { color: #111827; font-weight: 600; text-align: right; }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-danger  { background-color: #fee2e2; color: #991b1b; }
        .badge-info    { background-color: #dbeafe; color: #1e40af; }

        /* Divider */
        .email-divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 24px 0;
        }

        /* Steps */
        .step-number {
            display: inline-block;
            width: 28px;
            height: 28px;
            line-height: 28px;
            text-align: center;
            background-color: {{ $brand['accent_color'] ?? '#3956ca' }};
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            border-radius: 50%;
            margin-right: 10px;
            vertical-align: middle;
        }

        /* Responsive */
        @media only screen and (max-width: 620px) {
            .email-container { width: 100% !important; padding: 0 12px !important; }
            .email-content { padding: 28px 20px !important; }
            .email-header { padding: 20px !important; }
            .data-row { flex-direction: column; }
            .data-value { text-align: left; margin-top: 2px; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6;">
    {{-- Preheader (hidden preview text) --}}
    @hasSection('preheader')
    <div style="display: none; max-height: 0; overflow: hidden; mso-hide: all;">
        @yield('preheader')
        {{-- Spacer to push other content out of preheader --}}
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
    </div>
    @endif

    {{-- Main Wrapper --}}
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f3f4f6;">
        <tr>
            <td align="center" style="padding: 32px 16px;">
                {{-- Container --}}
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" class="email-container" style="max-width: 600px; width: 100%;">

                    {{-- Accent Bar --}}
                    <tr>
                        <td style="height: 4px; background-color: {{ $brand['accent_color'] ?? '#3956ca' }}; border-radius: 12px 12px 0 0; font-size: 0; line-height: 0;">&nbsp;</td>
                    </tr>

                    {{-- Header: Logo --}}
                    <tr>
                        <td class="email-header" style="background-color: #ffffff; padding: 28px 40px 20px 40px; text-align: center;">
                            <img src="{{ $brand['logo_url'] }}" alt="{{ $brand['company_name'] ?? 'RC ClearPay' }}" style="max-height: 48px; max-width: 200px; width: auto; height: auto;" onerror="this.style.display='none'">
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td class="email-content" style="background-color: #ffffff; padding: 8px 40px 36px 40px;">
                            @yield('content')
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #f9fafb; padding: 24px 40px; border-top: 1px solid #e5e7eb; border-radius: 0 0 12px 12px;">
                            @yield('footer-extra')

                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="text-align: center; font-size: 13px; color: #9ca3af; line-height: 1.6;">
                                        @if(!empty($brand['company_name']))
                                            <strong style="color: #6b7280;">{{ $brand['company_name'] }}</strong><br>
                                        @endif
                                        @if(!empty($brand['address']))
                                            {{ $brand['address'] }}<br>
                                        @endif
                                        @if(!empty($brand['support_email']))
                                            <a href="mailto:{{ $brand['support_email'] }}" style="color: {{ $brand['accent_color'] ?? '#3956ca' }}; text-decoration: none;">{{ $brand['support_email'] }}</a><br>
                                        @endif
                                        @if(!empty($brand['footer_text']))
                                            <span style="margin-top: 8px; display: inline-block;">{{ $brand['footer_text'] }}</span><br>
                                        @endif
                                        <span style="margin-top: 12px; display: inline-block; font-size: 12px; color: #d1d5db;">
                                            &copy; {{ date('Y') }} {{ $brand['company_name'] ?? 'RC ClearPay' }}. All rights reserved.
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
