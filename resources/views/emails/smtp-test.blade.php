@extends('emails.layout')

@section('title', 'Test email')
@section('brand', $brandName)
@section('preheader', 'Your email settings are working.')

@section('content')
    <h1 style="margin:0 0 20px 0;font-size:20px;line-height:28px;font-weight:600;color:#111827;">Your email settings work</h1>

    <p style="margin:0 0 24px 0;font-size:15px;line-height:24px;color:#4b5563;">
        This is a test message. If you're reading it, emails such as password resets and order updates can be delivered
        through this account.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background-color:#f9fafb;border:1px solid #eef0f2;border-radius:8px;">
        <tr>
            <td style="padding:16px;font-size:13px;line-height:22px;color:#4b5563;">
                <span style="color:#9ca3af;">Sent from</span> {{ $fromAddress }}<br>
                <span style="color:#9ca3af;">Provider</span> {{ $providerLabel }}<br>
                <span style="color:#9ca3af;">Server</span> {{ $host }}
            </td>
        </tr>
    </table>

    <p style="margin:24px 0 0 0;font-size:13px;line-height:20px;color:#6b7280;">
        Found this in your spam folder? Mark it as "Not spam" so future emails reach the inbox.
    </p>
@endsection

@section('footer', 'Sent from your email settings')
