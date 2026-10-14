<?php

namespace Tests\Feature;

use App\Models\{Company, Role, User};
use App\Support\EmailRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Cache, Hash, Validator};
use Tests\TestCase;

/**
 * Address validation has to say which half is wrong, and say it once.
 */
class DeliverableEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['mail.validate_dns' => true]);
        Cache::flush();
    }

    protected function messageFor(string $email): ?string
    {
        $v = Validator::make(['email' => $email], ['email' => EmailRules::deliverable()]);

        return $v->fails() ? $v->errors()->first('email') : null;
    }

    public function test_malformed_text_is_named_as_such(): void
    {
        $this->assertStringContainsString(
            'Enter a complete email address',
            $this->messageFor('not-an-email')
        );
    }

    public function test_a_domain_that_does_not_exist_is_named(): void
    {
        $message = $this->messageFor('someone@gmial-nonexistent-xyz123.invalid');

        $this->assertStringContainsString("couldn't find the domain", $message);
        $this->assertStringContainsString('gmial-nonexistent-xyz123.invalid', $message,
            'The message must name the domain so the person knows what to correct');
    }

    public function test_a_domain_that_refuses_mail_is_distinguished_from_a_typo(): void
    {
        // example.com publishes a Null MX (RFC 7505): it exists, but states
        // that it accepts no mail. That is a different fix from a typo.
        $message = $this->messageFor('someone@example.com');

        $this->assertStringContainsString('not set up to receive email', $message);
        $this->assertStringContainsString('example.com', $message);
    }

    public function test_a_deliverable_address_passes(): void
    {
        $this->assertNull($this->messageFor('someone@gmail.com'));
    }

    public function test_syntax_is_still_checked_when_dns_checking_is_off(): void
    {
        config(['mail.validate_dns' => false]);

        $this->assertNotNull($this->messageFor('not-an-email'));
        // With DNS off a non-existent domain is accepted - syntax only.
        $this->assertNull($this->messageFor('someone@gmial-nonexistent-xyz123.invalid'));
    }

    public function test_the_form_shows_the_email_error_exactly_once(): void
    {
        $company = Company::create([
            'name' => 'Dup Co', 'slug' => 'dup-co', 'shortcode' => 'DUP',
            'is_active' => true, 'is_deleted' => false,
        ]);
        $role = Role::create(['name' => 'admin', 'company_id' => $company->id, 'is_active' => true]);
        $admin = User::create([
            'company_id' => $company->id, 'f_name' => 'S', 'l_name' => 'A',
            'email' => 'super@gmail.com', 'password' => Hash::make('x'),
            'status' => 'active', 'is_super_admin' => true,
        ]);

        $this->actingAs($admin)->post('/admin/users', [
            'f_name' => 'umair', 'l_name' => 'tufail',
            'email' => 'umair@example.com',
            'password' => 'Str0ng!Passw0rd', 'password_confirmation' => 'Str0ng!Passw0rd',
            'company_id' => $company->id, 'status' => 'active', 'role' => $role->id,
        ]);

        $message = session('errors')->get('email')[0];

        $html = $this->actingAs($admin)->get('/admin/users/create')->getContent();

        // The input component renders the error itself; a second explicit
        // x-input-error beside it printed every message twice.
        $this->assertSame(
            1,
            substr_count($html, e($message)),
            'The email error must render once, not once per x-input-error tag'
        );
    }
}
