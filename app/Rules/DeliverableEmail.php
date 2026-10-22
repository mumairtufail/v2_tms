<?php

namespace App\Rules;

use Closure;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Validates an address the application will actually send to, and says which
 * part is wrong.
 *
 * Laravel's `email:rfc,dns` answers only yes or no, so a typo'd domain, a
 * domain that publishes "I accept no mail", and a malformed address all
 * produce the same unhelpful "must be a valid email address". The person is
 * left guessing which half to fix.
 *
 * Four outcomes are distinguished here:
 *   - the text is not an address at all
 *   - the domain does not exist
 *   - the domain exists but refuses mail (Null MX, RFC 7505)
 *   - the domain exists but publishes no mail server (no MX; usually parked)
 *
 * Unlike the bare dns rule this one fails OPEN when the lookup itself cannot
 * be completed. A DNS blip should not stop staff adding a customer; only a
 * definite negative answer rejects the address.
 */
class DeliverableEmail implements ValidationRule
{
    /** Seconds a domain's lookup result is reused for. */
    protected const CACHE_TTL = 300;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return; // `required` and `string` own this case.
        }

        $value = trim($value);

        if (! (new EmailValidator())->isValid($value, new RFCValidation())) {
            $fail('Enter a complete email address, like name@company.com.');

            return;
        }

        if (! config('mail.validate_dns')) {
            return;
        }

        $domain = $this->domainOf($value);

        if ($domain === null) {
            $fail('Enter a complete email address, like name@company.com.');

            return;
        }

        $result = $this->inspectDomain($domain);

        // A domain with an address record but no MX passes under RFC 5321's
        // fallback, unless the stricter setting is on (the default).
        if ($result === 'no_mx' && ! config('mail.require_mx', true)) {
            $result = 'ok';
        }

        if ($result !== 'ok') {
            Log::channel('mail')->info('Email address rejected at validation', [
                'field' => $attribute,
                'domain' => $domain,
                'reason' => $result,
            ]);
        }

        match ($result) {
            'no_domain' => $fail("We couldn't find the domain \"{$domain}\". Check the spelling after the @."),
            'no_mail' => $fail("The domain \"{$domain}\" is not set up to receive email. Use an address at a domain that accepts mail."),
            'no_mx' => $fail("The domain \"{$domain}\" has no mail server, so nothing sent to this address would arrive. Check the spelling after the @, or use a different address."),
            default => null,
        };
    }

    protected function domainOf(string $email): ?string
    {
        $at = strrpos($email, '@');

        if ($at === false) {
            return null;
        }

        $domain = substr($email, $at + 1);

        // An IDN is looked up in its ASCII form.
        if (function_exists('idn_to_ascii')) {
            $ascii = @idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

            if (is_string($ascii) && $ascii !== '') {
                $domain = $ascii;
            }
        }

        return $domain !== '' ? rtrim($domain, '.') : null;
    }

    /**
     * @return string 'ok' | 'no_domain' | 'no_mail' | 'no_mx'
     */
    protected function inspectDomain(string $domain): string
    {
        return Cache::remember(
            'email-domain:v2:'.strtolower($domain),
            self::CACHE_TTL,
            fn () => $this->lookup($domain)
        );
    }

    protected function lookup(string $domain): string
    {
        try {
            $mx = @dns_get_record($domain, DNS_MX);

            if ($mx === false) {
                // The resolver failed rather than answering "no such domain".
                // Treat that as inconclusive and let the address through.
                Log::warning('Email domain lookup failed; accepting address', ['domain' => $domain]);

                return 'ok';
            }

            if ($mx !== []) {
                // RFC 7505: a single MX whose target is "." means the domain
                // has explicitly declared that it accepts no mail.
                $targets = array_filter(array_map(
                    fn ($record) => rtrim($record['target'] ?? '', '.'),
                    $mx
                ));

                return $targets === [] ? 'no_mail' : 'ok';
            }

            // No MX but the domain resolves. RFC 5321 would fall back to the
            // address record, but that is nearly always a parked domain, so it
            // is reported separately and validate() decides (mail.require_mx).
            if (@checkdnsrr($domain, 'A') || @checkdnsrr($domain, 'AAAA')) {
                return 'no_mx';
            }

            return @checkdnsrr($domain, 'NS') ? 'no_mail' : 'no_domain';
        } catch (\Throwable $e) {
            Log::warning('Email domain lookup errored; accepting address', [
                'domain' => $domain,
                'error' => $e->getMessage(),
            ]);

            return 'ok';
        }
    }
}
