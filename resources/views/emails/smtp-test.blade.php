@extends('emails.layout')

@section('title', 'Test email')
@section('brand', $brandName)

@section('content')
    <p style="margin:0 0 16px 0;font-size:36px;line-height:40px;">&#9989;</p>
    <h1 style="margin:0 0 12px 0;font-size:22px;line-height:30px;font-weight:700;color:#111827;">Your email settings work</h1>
    <p style="margin:0 0 20px 0;font-size:15px;line-height:24px;color:#374151;">
        This is a test message. If you're reading it, emails such as password resets can be delivered through this account.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;">
        <tr>
            <td style="padding:14px 16px;font-size:13px;line-height:22px;color:#374151;">
                <strong style="color:#111827;">Sent from:</strong> {{ $fromAddress }}<br>
                <strong style="color:#111827;">Provider:</strong> {{ $providerLabel }}<br>
                <strong style="color:#111827;">Server:</strong> {{ $host }}
            </td>
        </tr>
    </table>

    <p style="margin:20px 0 0 0;font-size:13px;line-height:20px;color:#6b7280;">
        Found this in your spam folder? Mark it as "Not spam" so future emails reach the inbox.
    </p>
@endsection

@section('footer', 'Sent from your email settings')
