<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>@yield('title')</title>
</head>
{{--
    Shared layout for every email the app sends.
    Templates fill: title, brand, preheader (optional), content, footer.
    Plain tables and inline styles only — email clients ignore stylesheets.
--}}
<body style="margin:0;padding:0;background-color:#f6f7f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#111827;-webkit-font-smoothing:antialiased;">

    {{-- Inbox preview line, hidden in the message body --}}
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;height:0;width:0;">
        @yield('preheader')
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f6f7f9;">
        <tr>
            <td align="center" style="padding:40px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:520px;">

                    {{-- Brand --}}
                    <tr>
                        <td style="padding:0 4px 16px 4px;font-size:13px;font-weight:600;letter-spacing:0.6px;text-transform:uppercase;color:#6b7280;">
                            @yield('brand')
                        </td>
                    </tr>

                    {{-- Card --}}
                    <tr>
                        <td style="background-color:#ffffff;border:1px solid #e8eaed;border-radius:12px;padding:32px;">
                            @yield('content')
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:16px 4px 0 4px;font-size:12px;line-height:20px;color:#9ca3af;">
                            @yield('footer')
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
