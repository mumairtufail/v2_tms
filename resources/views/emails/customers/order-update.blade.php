@extends('emails.layout')

@section('title', $title)
@section('brand', $brandName)
@section('preheader', $body)

@section('content')
    <h1 style="margin:0 0 20px 0;font-size:20px;line-height:28px;font-weight:600;color:#111827;">{{ $title }}</h1>

    <p style="margin:0 0 16px 0;font-size:15px;line-height:24px;color:#4b5563;">Hi {{ $recipientName }},</p>
    <p style="margin:0 0 {{ $url ? '24px' : '0' }} 0;font-size:15px;line-height:24px;color:#4b5563;">{{ $body }}</p>

    @if($url)
    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td style="border-radius:8px;background-color:#059669;">
                <a href="{{ $url }}" target="_blank"
                   style="display:inline-block;padding:12px 24px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px;">
                    View order
                </a>
            </td>
        </tr>
    </table>
    @endif
@endsection

@section('footer')
    Sent by {{ $brandName }}. You receive these because order updates are turned on for you — ask {{ $brandName }} to change your notification settings.
@endsection
