@extends('emails.layout')

@section('title', 'Reset your password')
@section('brand', $brandName)
@section('preheader', 'Reset your password — this link expires in ' . $expireMinutes . ' minutes.')

@section('content')
    <h1 style="margin:0 0 20px 0;font-size:20px;line-height:28px;font-weight:600;color:#111827;">Reset your password</h1>

    <p style="margin:0 0 16px 0;font-size:15px;line-height:24px;color:#4b5563;">Hi {{ $userName }},</p>
    <p style="margin:0 0 24px 0;font-size:15px;line-height:24px;color:#4b5563;">
        We received a request to reset your password. Choose a new one using the button below.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 24px 0;">
        <tr>
            <td style="border-radius:8px;background-color:#059669;">
                <a href="{{ $url }}" target="_blank"
                   style="display:inline-block;padding:12px 24px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px;">
                    Reset password
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 24px 0;font-size:14px;line-height:22px;color:#6b7280;">
        This link expires in {{ $expireMinutes }} minutes. If you didn't ask for it, you can ignore this email — your password stays the same.
    </p>

    <hr style="border:none;border-top:1px solid #eef0f2;margin:0 0 16px 0;">

    <p style="margin:0;font-size:12px;line-height:20px;color:#9ca3af;">
        Button not working? Paste this into your browser:<br>
        <a href="{{ $url }}" style="color:#059669;word-break:break-all;">{{ $url }}</a>
    </p>
@endsection

@section('footer', 'Sent by ' . $brandName)
