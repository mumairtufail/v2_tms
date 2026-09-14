<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class SmtpSetting extends Model
{
    public const ENCRYPTIONS = [
        'tls'  => 'TLS',
        'ssl'  => 'SSL',
        'none' => 'None',
    ];

    /**
     * Presets so users of common providers only need an email address and password.
     * `username`: 'email' reuses the from address, null asks the user, any other
     * string is the fixed username the provider expects.
     */
    public const PROVIDERS = [
        'gmail' => [
            'label'          => 'Gmail',
            'subtitle'       => 'Gmail & Google Workspace',
            'host'           => 'smtp.gmail.com',
            'port'           => 587,
            'encryption'     => 'tls',
            'username'       => 'email',
            'password_label' => 'App password',
            'help'           => 'Google needs an App Password, not your normal password. Turn on 2-Step Verification for the account, then create a 16-character App Password.',
            'help_url'       => 'https://myaccount.google.com/apppasswords',
        ],
        'outlook' => [
            'label'          => 'Outlook',
            'subtitle'       => 'Outlook & Microsoft 365',
            'host'           => 'smtp.office365.com',
            'port'           => 587,
            'encryption'     => 'tls',
            'username'       => 'email',
            'password_label' => 'Password',
            'help'           => 'Use your Microsoft email and password. If sign-in is rejected, your IT admin needs to enable "Authenticated SMTP" for this mailbox.',
            'help_url'       => 'https://learn.microsoft.com/exchange/clients-and-mobile-in-exchange-online/authenticated-client-smtp-submission',
        ],
        'yahoo' => [
            'label'          => 'Yahoo',
            'subtitle'       => 'Yahoo Mail',
            'host'           => 'smtp.mail.yahoo.com',
            'port'           => 465,
            'encryption'     => 'ssl',
            'username'       => 'email',
            'password_label' => 'App password',
            'help'           => 'Yahoo needs an App Password. Create one under Account Security → Generate app password.',
            'help_url'       => 'https://login.yahoo.com/account/security',
        ],
        'zoho' => [
            'label'          => 'Zoho',
            'subtitle'       => 'Zoho Mail',
            'host'           => 'smtp.zoho.com',
            'port'           => 465,
            'encryption'     => 'ssl',
            'username'       => 'email',
            'password_label' => 'Password',
            'help'           => 'Use your Zoho email and password (or an app-specific password if 2FA is on). EU accounts should change the server to smtp.zoho.eu under Advanced settings.',
            'help_url'       => null,
        ],
        'sendgrid' => [
            'label'          => 'SendGrid',
            'subtitle'       => 'Twilio SendGrid',
            'host'           => 'smtp.sendgrid.net',
            'port'           => 587,
            'encryption'     => 'tls',
            'username'       => 'apikey',
            'password_label' => 'API key',
            'help'           => 'Create an API key with "Mail Send" permission and paste it as the API key. The sender email must be verified in SendGrid.',
            'help_url'       => 'https://app.sendgrid.com/settings/api_keys',
        ],
        'mailgun' => [
            'label'          => 'Mailgun',
            'subtitle'       => 'Mailgun SMTP',
            'host'           => 'smtp.mailgun.org',
            'port'           => 587,
            'encryption'     => 'tls',
            'username'       => null,
            'password_label' => 'SMTP password',
            'help'           => 'Find the SMTP login and password under Sending → Domain settings → SMTP credentials. EU domains use smtp.eu.mailgun.org.',
            'help_url'       => null,
        ],
        'ses' => [
            'label'          => 'Amazon SES',
            'subtitle'       => 'AWS Simple Email Service',
            'host'           => 'email-smtp.us-east-1.amazonaws.com',
            'port'           => 587,
            'encryption'     => 'tls',
            'username'       => null,
            'password_label' => 'SMTP password',
            'help'           => 'Create SMTP credentials in the SES console (they are different from your AWS access keys). Change the region in the server address under Advanced settings if needed.',
            'help_url'       => null,
        ],
        'custom' => [
            'label'          => 'Other',
            'subtitle'       => 'Any SMTP server',
            'host'           => '',
            'port'           => 587,
            'encryption'     => 'tls',
            'username'       => 'email',
            'password_label' => 'Password',
            'help'           => 'Your email host (cPanel, GoDaddy, Hostinger, etc.) lists the server address and port under Email Accounts → "Connect devices" or "Mail client setup".',
            'help_url'       => null,
        ],
    ];

    protected $fillable = [
        'company_id',
        'name',
        'provider',
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'from_address',
        'from_name',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password'         => 'encrypted',
        'port'             => 'integer',
        'is_active'        => 'boolean',
        'last_test_passed' => 'boolean',
        'last_tested_at'   => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeForCompany($query, ?int $companyId)
    {
        return $companyId
            ? $query->where('company_id', $companyId)
            : $query->whereNull('company_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function providerMeta(): array
    {
        return self::PROVIDERS[$this->provider] ?? self::PROVIDERS['custom'];
    }

    public function getProviderLabelAttribute(): string
    {
        return $this->providerMeta()['label'];
    }

    public function getEncryptionLabelAttribute(): string
    {
        return self::ENCRYPTIONS[$this->encryption] ?? strtoupper((string) $this->encryption);
    }

    /**
     * Make this the only active account for its company.
     */
    public function activate(): void
    {
        DB::transaction(function () {
            static::forCompany($this->company_id)
                ->whereKeyNot($this->getKey())
                ->update(['is_active' => false]);

            $this->forceFill(['is_active' => true])->save();
        });
    }

    public function recordTestResult(bool $passed, ?string $error = null): void
    {
        $this->forceFill([
            'last_tested_at'   => now(),
            'last_test_passed' => $passed,
            'last_test_error'  => $passed ? null : $error,
        ])->save();
    }
}
