@extends('emails.layout')

@section('title', 'Welcome to ' . $appName)
@section('brand', $appName)
@section('preheader', 'Your ' . $companyName . ' portal account is ready.')

@section('content')
    <h1 style="margin:0 0 20px 0;font-size:20px;line-height:28px;font-weight:600;color:#111827;">Welcome to {{ $appName }}</h1>

    <p style="margin:0 0 16px 0;font-size:15px;line-height:24px;color:#4b5563;">Hi {{ $recipientName }},</p>
    <p style="margin:0 0 24px 0;font-size:15px;line-height:24px;color:#4b5563;">
        {{ $companyName }} has set up a portal account for you. You can place orders, follow their progress and see your
        documents in one place.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background-color:#f9fafb;border:1px solid #eef0f2;border-radius:8px;margin:0 0 24px 0;">
        <tr>
            <td style="padding:16px;font-size:13px;line-height:22px;color:#4b5563;">
                <span style="color:#9ca3af;">Sign in with</span> {{ $signInEmail }}<br>
                <span style="color:#9ca3af;">Password</span> set by {{ $companyName }} — contact them if you don't have it yet
            </td>
        </tr>
    </table>

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 24px 0;">
        <tr>
            <td style="border-radius:8px;background-color:#059669;">
                <a href="{{ $portalUrl }}" target="_blank"
                   style="display:inline-block;padding:12px 24px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px;">
                    Open your portal
                </a>
            </td>
        </tr>
    </table>

    <hr style="border:none;border-top:1px solid #eef0f2;margin:0 0 16px 0;">

    <p style="margin:0;font-size:12px;line-height:20px;color:#9ca3af;">
        Button not working? Paste this into your browser:<br>
        <a href="{{ $portalUrl }}" style="color:#059669;word-break:break-all;">{{ $portalUrl }}</a>
    </p>
@endsection

@section('footer')
    Sent by {{ $companyName }} via {{ $appName }}. If you weren't expecting this, you can ignore this email.
@endsection
