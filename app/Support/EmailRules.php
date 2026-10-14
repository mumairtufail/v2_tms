<?php

namespace App\Support;

use App\Rules\DeliverableEmail;

/**
 * Validation rules for email addresses, in one place.
 *
 * Laravel's bare `email` rule is syntax only (it resolves to RFCValidation), so
 * `info@acme-tpyo.com` passes happily and only turns out to be wrong when a send
 * bounces. `deliverable()` adds a DNS check on top, which rejects an address
 * whose domain has no mail records at the moment the form is submitted.
 *
 * What this does and does not catch:
 *   - caught: a misspelled or non-existent domain (gmial.com, acme-tpyo.com)
 *   - not caught: a real domain with no such mailbox (typo@gmail.com) — only a
 *     send attempt can tell you that, which is why delivery outcomes are also
 *     recorded against the contact (see MailService::recordDelivery).
 *
 * The DNS check fails open: a lookup that cannot be completed accepts the
 * address, so a DNS outage never blocks staff from adding a contact. Only a
 * definite negative - no such domain, or a Null MX - rejects it. It is still
 * switchable altogether via config('mail.validate_dns').
 */
class EmailRules
{
    /**
     * For an address the app will actually send to — a person at a customer, a
     * staff login, an invoice recipient.
     *
     * @return array<int, string>
     */
    public static function deliverable(): array
    {
        // One rule rather than email:rfc,dns, because that rule answers only
        // yes or no: a typo'd domain, a domain that refuses mail and a
        // malformed address all produced the same "must be a valid email
        // address" and left the person guessing which half to fix.
        //
        // DeliverableEmail names the actual problem, and it reads
        // config('mail.validate_dns') itself, so the syntax check still runs
        // when DNS checking is off.
        return [new DeliverableEmail()];
    }

    /**
     * For an address only ever compared against one already stored — sign-in
     * forms, password reset lookups.
     *
     * Deliberately syntax-only. Resolving DNS here would add a lookup to every
     * sign-in attempt (including failed ones, which is a cheap way to make the
     * login endpoint do unbounded outbound work), and would reject a user whose
     * mail domain has lapsed since their account was created — locking out
     * someone whose password is perfectly good.
     *
     * @return array<int, string>
     */
    public static function identifier(): array
    {
        return ['email'];
    }
}
