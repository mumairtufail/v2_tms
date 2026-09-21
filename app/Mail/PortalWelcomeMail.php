<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to a person at a customer when they are first given portal access.
 * Includes the password the company just set, when there is one; otherwise it
 * tells them to ask the company for it.
 */
class PortalWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $appName,
        public string $companyName,
        public string $portalUrl,
        public string $signInEmail,
        public ?string $password = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Welcome to {$this->appName}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.customers.portal-welcome');
    }
}
