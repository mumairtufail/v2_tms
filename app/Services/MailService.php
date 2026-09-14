<?php

namespace App\Services;

use App\Jobs\SendMailJob;
use App\Mail\SmtpTestMail;
use App\Models\SmtpSetting;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;
use Throwable;

/**
 * Sends email through the company's active SMTP account, falling back to the
 * mailer configured in .env when the company has none.
 */
class MailService
{
    public const TIMEOUT_SECONDS = 15;

    /**
     * Push the email onto the queue. The SMTP account is resolved when the job
     * runs, so switching accounts applies to emails still waiting in the queue.
     */
    public function queue(Mailable $mailable, string|array $to, ?int $companyId = null): void
    {
        SendMailJob::dispatch($mailable, $to, $companyId);
    }

    public function sendNow(Mailable $mailable, string|array $to, ?int $companyId = null): void
    {
        $this->mailerFor($companyId)->to($to)->send($mailable);
    }

    public function activeSetting(?int $companyId): ?SmtpSetting
    {
        return SmtpSetting::forCompany($companyId)->active()->first();
    }

    public function mailerFor(?int $companyId): MailerContract
    {
        $setting = $this->activeSetting($companyId);

        return $setting ? $this->mailerForSetting($setting) : Mail::mailer();
    }

    public function mailerForSetting(SmtpSetting $setting): Mailer
    {
        // SSL means implicit TLS; otherwise Symfony upgrades via STARTTLS when the server offers it
        $transport = new EsmtpTransport(
            $setting->host,
            $setting->port,
            $setting->encryption === 'ssl' ? true : null
        );
        $transport->setUsername($setting->username);
        $transport->setPassword($setting->password);

        $stream = $transport->getStream();
        if ($stream instanceof SocketStream) {
            $stream->setTimeout(self::TIMEOUT_SECONDS);
        }

        $mailer = new Mailer('smtp-setting-' . ($setting->id ?? 'draft'), app('view'), $transport, app('events'));
        $mailer->alwaysFrom($setting->from_address, $setting->from_name ?: config('mail.from.name'));

        return $mailer;
    }

    /**
     * Send a test email right away so the user gets instant feedback.
     * Returns null on success, or a human-readable reason on failure.
     */
    public function sendTest(SmtpSetting $setting, string $to): ?string
    {
        $error = null;

        try {
            $this->mailerForSetting($setting)->to($to)->send(new SmtpTestMail(
                fromAddress: $setting->from_address,
                providerLabel: $setting->provider_label,
                host: $setting->host,
                brandName: $setting->from_name ?: config('app.name'),
            ));
        } catch (Throwable $e) {
            Log::warning('SMTP test email failed', [
                'smtp_setting_id' => $setting->id,
                'host'            => $setting->host,
                'port'            => $setting->port,
                'error'           => $e->getMessage(),
            ]);
            $error = $this->friendlyError($e, $setting);
        }

        if ($setting->exists) {
            $setting->recordTestResult($error === null, $error);
        }

        return $error;
    }

    /**
     * Translate transport exceptions into something a non-technical user can act on.
     */
    public function friendlyError(Throwable $e, SmtpSetting $setting): string
    {
        $message = $e->getMessage();
        $lower = Str::lower($message);
        $meta = $setting->providerMeta();

        if (Str::contains($lower, ['535', '534', 'authenticat', 'username and password not accepted', 'invalid credentials', 'failed to authenticate'])) {
            $hint = in_array($meta['password_label'], ['App password', 'API key'], true)
                ? " {$meta['label']} requires an {$meta['password_label']} here, not your normal login password."
                : ' Double-check the email address and password.';

            return 'The email server rejected the username or password.' . $hint;
        }

        if (Str::contains($lower, ['getaddrinfo', 'name or service not known', 'no such host', 'php_network_getaddresses'])) {
            return "We couldn't find the server \"{$setting->host}\". Check the server address for typos.";
        }

        if (Str::contains($lower, ['ssl', 'tls', 'crypto', 'certificate', 'wrong version number'])) {
            return 'A secure connection could not be established. Try switching encryption: TLS normally uses port 587 and SSL uses port 465.';
        }

        if (Str::contains($lower, ['connection could not be established', 'connection refused', 'timed out', 'timeout', 'unable to connect'])) {
            return "Couldn't connect to {$setting->host} on port {$setting->port}. Check the server address and port. Some hosting providers also block outgoing email ports.";
        }

        if (Str::contains($lower, ['550', '553', '554', 'sender', 'not owned', 'not allowed to send', 'relay'])) {
            return "The server refused to send from {$setting->from_address}. Make sure this address belongs to (or is verified for) the account you signed in with.";
        }

        return 'The email could not be sent: ' . Str::limit($message, 200);
    }
}
