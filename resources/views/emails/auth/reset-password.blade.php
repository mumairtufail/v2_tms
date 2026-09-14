@extends('emails.layout')

@section('title', 'Reset your password')
@section('brand', $brandName)

@section('content')
    <h1 style="margin:0 0 16px 0;font-size:22px;line-height:30px;font-weight:700;color:#111827;">Reset your password</h1>
    <p style="margin:0 0 12px 0;font-size:15px;line-height:24px;color:#374151;">Hi {{ $userName }},</p>
    <p style="margin:0 0 12px 0;font-size:15px;line-height:24px;color:#374151;">
        We received a request to reset the password for your {{ $brandName }} account. Click the button below to choose a new one.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:28px 0;">
        <tr>
            <td style="border-radius:10px;background-color:#059669;">
                <a href="{{ $url }}" target="_blank" style="display:inline-block;padding:13px 28px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:10px;">Reset password</a>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 12px 0;font-size:14px;line-height:22px;color:#6b7280;">
        This link expires in {{ $expireMinutes }} minutes. If you didn't ask to reset your password, you can safely ignore this email. Your password won't change.
    </p>

    <hr style="border:none;border-top:1px solid #e5e7eb;margin:28px 0 20px 0;">

    <p style="margin:0;font-size:12px;line-height:18px;color:#6b7280;">
        Button not working? Copy and paste this link into your browser:<br>
        <a href="{{ $url }}" style="color:#059669;word-break:break-all;">{{ $url }}</a>
    </p>
@endsection

@section('footer', 'Sent by ' . $brandName)
