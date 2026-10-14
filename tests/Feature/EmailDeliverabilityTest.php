<?php

namespace Tests\Feature;

use App\Mail\SmtpTestMail;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\SmtpSetting;
use App\Services\MailService;
use App\Support\EmailRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Throwable;

/**
 * Answers "does this email address actually exist?" in the two ways the app can.
 *
 * Up front, a DNS check rejects an address whose domain cannot receive mail.
 * Afterwards, the outcome of every real send is stamped onto the person holding
 * that address, which is the only way to catch a real domain with no mailbox.
 */
class EmailDeliverabilityTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Deliverability Freight',
            'slug' => 'deliverability-freight',
            'shortcode' => 'DLV',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $this->customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Acme Shipping',
            'short_code' => 'ACME',
            'address_1' => '1 Dock Road',
            'city' => 'Vancouver',
            'state' => 'BC',
            'postal_code' => 'V6B 1A1',
            'country' => 'CA',
            'currency' => 'CAD',
            'default_billing_option' => 'third_party',
            'is_active' => true,
            'is_deleted' => false,
        ]);
    }

    private function contact(string $email): CustomerContact
    {
        return CustomerContact::create([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => $email,
        ]);
    }

    // ── The rules themselves ────────────────────────────────────────────────

    /**
     * Asserts behaviour rather than the rule strings: deliverable() now returns
     * a DeliverableEmail rule object, which reads mail.validate_dns itself so
     * that it can say *why* an address was rejected.
     */
    protected function rejects(string $email): bool
    {
        return \Illuminate\Support\Facades\Validator::make(
            ['email' => $email],
            ['email' => EmailRules::deliverable()]
        )->fails();
    }

    public function test_deliverable_addresses_get_a_dns_check_when_it_is_enabled(): void
    {
        config(['mail.validate_dns' => true]);
        \Illuminate\Support\Facades\Cache::flush();

        $this->assertTrue($this->rejects('someone@gmial-nonexistent-xyz123.invalid'));
        $this->assertFalse($this->rejects('someone@gmail.com'));
    }

    public function test_dns_check_can_be_switched_off_without_losing_syntax_validation(): void
    {
        config(['mail.validate_dns' => false]);
        \Illuminate\Support\Facades\Cache::flush();

        // Still validated, just not resolved — this is the CI / offline path, and
        // it must never degrade to "no validation at all".
        $this->assertTrue($this->rejects('not-an-email'));
        $this->assertTrue($this->rejects('missing-domain@'));
        $this->assertFalse($this->rejects('someone@gmial-nonexistent-xyz123.invalid'));
    }

    public function test_sign_in_addresses_are_never_dns_checked(): void
    {
        config(['mail.validate_dns' => true]);

        // A lapsed mail domain must not lock out a user whose password is fine,
        // and login must not do outbound DNS work per attempt.
        $this->assertSame(['email'], EmailRules::identifier());
    }

    /**
     * .invalid is reserved by RFC 2606 precisely so it can never resolve, which
     * keeps this deterministic instead of depending on a domain staying dead.
     */
    public function test_an_address_whose_domain_cannot_receive_mail_is_rejected(): void
    {
        config(['mail.validate_dns' => true]);

        $passes = validator(
            ['email' => 'jane@acme-shipping.invalid'],
            ['email' => EmailRules::deliverable()]
        )->passes();

        $this->assertFalse($passes, 'An unresolvable mail domain should not be accepted.');
    }

    public function test_a_real_mail_domain_is_accepted(): void
    {
        config(['mail.validate_dns' => false]);

        $this->assertTrue(
            validator(['email' => 'jane@example.com'], ['email' => EmailRules::deliverable()])->passes()
        );
    }

    // ── Delivery outcomes ───────────────────────────────────────────────────

    public function test_a_successful_send_marks_the_person_as_delivered(): void
    {
        $contact = $this->contact('jane@example.com');

        $this->assertNull($contact->email_status, 'A new person starts with no delivery history.');

        // MAIL_MAILER=array in phpunit.xml, so this succeeds without a mail server.
        app(MailService::class)->sendNow($this->mailable(), 'jane@example.com', $this->company->id);

        $contact->refresh();

        $this->assertTrue($contact->emailDelivered());
        $this->assertSame('sent', $contact->email_status);
        $this->assertNull($contact->email_status_reason);
        $this->assertNotNull($contact->email_status_at);
    }

    public function test_a_failed_send_marks_the_person_undeliverable_with_a_readable_reason(): void
    {
        $contact = $this->contact('jane@example.com');

        // Port 1 is never listening, so the transport fails fast and deterministically.
        SmtpSetting::create([
            'company_id' => $this->company->id,
            'name' => 'Broken account',
            'host' => '127.0.0.1',
            'port' => 1,
            'encryption' => 'tls',
            'username' => 'someone@example.com',
            'password' => 'secret',
            'from_address' => 'someone@example.com',
            'from_name' => 'Deliverability Freight',
            'is_active' => true,
        ]);

        try {
            app(MailService::class)->sendNow($this->mailable(), 'jane@example.com', $this->company->id);
            $this->fail('A send through a dead SMTP host should surface the failure.');
        } catch (Throwable) {
            // Rethrown on purpose so a real queue can retry — the status is still recorded.
        }

        $contact->refresh();

        $this->assertTrue($contact->emailFailed());
        $this->assertSame('failed', $contact->email_status);
        $this->assertStringContainsString("Couldn't connect", (string) $contact->email_status_reason);
    }

    public function test_a_later_success_clears_an_earlier_failure(): void
    {
        $contact = $this->contact('jane@example.com');
        $contact->forceFill([
            'email_status' => 'failed',
            'email_status_reason' => 'Previously undeliverable',
            'email_status_at' => now()->subDay(),
        ])->save();

        app(MailService::class)->sendNow($this->mailable(), 'jane@example.com', $this->company->id);

        $contact->refresh();

        $this->assertTrue($contact->emailDelivered());
        $this->assertNull($contact->email_status_reason, 'A stale failure reason must not linger.');
    }

    public function test_delivery_status_does_not_leak_across_companies(): void
    {
        $other = Company::create([
            'name' => 'Other Freight',
            'slug' => 'other-freight',
            'shortcode' => 'OTH',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $otherCustomer = Customer::create([
            'company_id' => $other->id,
            'name' => 'Other Customer',
            'short_code' => 'OTHC',
            'address_1' => '2 Dock Road',
            'city' => 'Calgary',
            'state' => 'AB',
            'postal_code' => 'T2P 1A1',
            'country' => 'CA',
            'currency' => 'CAD',
            'default_billing_option' => 'third_party',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $mine = $this->contact('shared@example.com');

        // Same address, different tenant. One company's send must not write the
        // other's record — these two people are unrelated.
        $theirs = CustomerContact::create([
            'company_id' => $other->id,
            'customer_id' => $otherCustomer->id,
            'first_name' => 'Sam',
            'last_name' => 'Other',
            'email' => 'shared@example.com',
        ]);

        app(MailService::class)->sendNow($this->mailable(), 'shared@example.com', $this->company->id);

        $this->assertSame('sent', $mine->refresh()->email_status);
        $this->assertNull($theirs->refresh()->email_status);
    }

    public function test_recording_the_outcome_never_breaks_a_send_to_a_stranger(): void
    {
        // Nobody in the system holds this address; the send must still go through.
        app(MailService::class)->sendNow($this->mailable(), 'nobody@example.com', $this->company->id);

        $this->assertDatabaseCount('customer_contacts', 0);
    }

    private function mailable(): SmtpTestMail
    {
        return new SmtpTestMail(
            fromAddress: 'someone@example.com',
            providerLabel: 'Test',
            host: '127.0.0.1',
            brandName: 'Deliverability Freight',
        );
    }
}
