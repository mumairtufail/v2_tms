<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $url,
        public int $expireMinutes,
        public string $brandName,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Reset your ' . $this->brandName . ' password');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.auth.reset-password');
    }
}
