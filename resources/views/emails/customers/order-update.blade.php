@extends('emails.layout')

@section('title', $title)
@section('brand', $brandName)

@section('content')
    <h1 style="margin:0 0 16px 0;font-size:22px;line-height:30px;font-weight:700;color:#111827;">{{ $title }}</h1>
    <p style="margin:0 0 12px 0;font-size:15px;line-height:24px;color:#374151;">Hi {{ $recipientName }},</p>
    <p style="margin:0 0 12px 0;font-size:15px;line-height:24px;color:#374151;">{{ $body }}</p>

    @if($url)
    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:28px 0 8px 0;">
        <tr>
            <td style="border-radius:10px;background-color:#059669;">
                <a href="{{ $url }}" target="_blank" style="display:inline-block;padding:13px 28px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:10px;">View order</a>
            </td>
        </tr>
    </table>
    @endif
@endsection

@section('footer')
    Sent by {{ $brandName }}. You get this email because order updates are turned on for you. Ask {{ $brandName }} to change your notification settings.
@endsection
