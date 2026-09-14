<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SmtpTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $fromAddress,
        public string $providerLabel,
        public string $host,
        public string $brandName,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Test email: your email settings are working');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.smtp-test');
    }
}
