<?php

namespace Tests\Feature;

use App\Jobs\SendMailJob;
use App\Mail\ResetPasswordMail;
use App\Models\Company;
use App\Models\SmtpSetting;
use App\Models\User;
use App\Services\MailService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SmtpSettingsTest extends TestCase
{
    use DatabaseTransactions;

    private Company $company;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = $this->makeCompany();
        $this->user = User::create([
            'company_id' => $this->company->id,
            'name' => 'Sam Sender',
            'f_name' => 'Sam',
            'l_name' => 'Sender',
            'email' => 'sam.sender.'.uniqid().'@example.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'is_active' => true,
            'is_deleted' => false,
            'is_super_admin' => true,
        ]);
    }

    private function makeCompany(): Company
    {
        $suffix = uniqid();

        return Company::create([
            'name' => 'Mail Co '.$suffix,
            'slug' => 'mail-co-'.$suffix,
            'shortcode' => 'MLC',
            'is_active' => true,
            'is_deleted' => false,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'provider' => 'gmail',
            'from_address' => 'dispatch@example.com',
            'from_name' => 'Dispatch',
            'username' => '',
            'password' => 'abcd efgh ijkl mnop',
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'encryption' => 'tls',
            'is_active' => 0,
        ], $overrides);
    }

    public function test_pages_render_in_empty_state(): void
    {
        $this->actingAs($this->user)
            ->get(route('v2.settings.index', $this->company))
            ->assertOk()
            ->assertSee('Email (SMTP)');

        $this->actingAs($this->user)
            ->get(route('v2.settings.smtp.index', $this->company))
            ->assertOk()
            ->assertSee('Connect your email account');

        $this->actingAs($this->user)
            ->get(route('v2.settings.smtp.create', $this->company))
            ->assertOk()
            ->assertSee('Which email service do you use?');
    }

    public function test_first_account_is_activated_and_defaults_are_filled(): void
    {
        $this->actingAs($this->user)
            ->post(route('v2.settings.smtp.store', $this->company), $this->payload())
            ->assertRedirect();

        $setting = SmtpSetting::forCompany($this->company->id)->firstOrFail();

        $this->assertTrue($setting->is_active);
        $this->assertSame('dispatch@example.com', $setting->username);
        $this->assertSame('abcdefghijklmnop', $setting->password);
        $this->assertSame('Gmail · dispatch@example.com', $setting->name);
        $this->assertNotSame('abcdefghijklmnop', DB::table('smtp_settings')->where('id', $setting->id)->value('password'));

        foreach (['index', 'show', 'edit'] as $page) {
            $this->actingAs($this->user)
                ->get(route("v2.settings.smtp.{$page}", $page === 'index' ? $this->company : [$this->company, $setting]))
                ->assertOk()
                ->assertDontSee('abcdefghijklmnop');
        }
    }

    public function test_only_one_account_is_active_at_a_time(): void
    {
        $this->actingAs($this->user)->post(route('v2.settings.smtp.store', $this->company), $this->payload());
        $this->actingAs($this->user)->post(route('v2.settings.smtp.store', $this->company), $this->payload([
            'provider' => 'sendgrid',
            'from_address' => 'noreply@example.com',
            'host' => 'smtp.sendgrid.net',
            'password' => 'SG.key',
            'is_active' => 1,
        ]));

        $settings = SmtpSetting::forCompany($this->company->id)->orderBy('id')->get();
        $this->assertFalse($settings[0]->is_active);
        $this->assertTrue($settings[1]->is_active);
        $this->assertSame('apikey', $settings[1]->username);

        $this->actingAs($this->user)->post(route('v2.settings.smtp.activate', [$this->company, $settings[0]]));
        $this->assertTrue($settings[0]->fresh()->is_active);
        $this->assertFalse($settings[1]->fresh()->is_active);

        $this->actingAs($this->user)->post(route('v2.settings.smtp.deactivate', [$this->company, $settings[0]]));
        $this->assertSame(0, SmtpSetting::forCompany($this->company->id)->active()->count());
    }

    public function test_update_keeps_password_when_left_blank_and_delete_works(): void
    {
        $this->actingAs($this->user)->post(route('v2.settings.smtp.store', $this->company), $this->payload());
        $setting = SmtpSetting::forCompany($this->company->id)->firstOrFail();

        $this->actingAs($this->user)
            ->put(route('v2.settings.smtp.update', [$this->company, $setting]), $this->payload([
                'password' => '',
                'from_name' => 'Renamed',
                'is_active' => 1,
            ]))
            ->assertRedirect(route('v2.settings.smtp.show', [$this->company, $setting]));

        $setting->refresh();
        $this->assertSame('Renamed', $setting->from_name);
        $this->assertSame('abcdefghijklmnop', $setting->password);

        $this->actingAs($this->user)
            ->delete(route('v2.settings.smtp.destroy', [$this->company, $setting]))
            ->assertRedirect(route('v2.settings.smtp.index', $this->company));
        $this->assertNull($setting->fresh());
    }

    public function test_accounts_of_another_company_are_not_reachable(): void
    {
        $other = $this->makeCompany();
        $foreign = SmtpSetting::create($this->payload(['company_id' => $other->id, 'name' => 'Foreign', 'username' => 'x']));

        $this->actingAs($this->user)
            ->get(route('v2.settings.smtp.show', [$this->company, $foreign]))
            ->assertNotFound();
        $this->actingAs($this->user)
            ->delete(route('v2.settings.smtp.destroy', [$this->company, $foreign]))
            ->assertNotFound();
    }

    public function test_draft_test_reports_a_friendly_connection_error(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('v2.settings.smtp.test-draft', $this->company), $this->payload([
                'provider' => 'custom',
                'host' => '127.0.0.1',
                'port' => 1,
                'test_to' => 'someone@example.com',
            ]))
            ->assertOk()
            ->assertJson(['ok' => false])
            ->assertJsonPath('message', fn ($message) => str_contains($message, "Couldn't connect to 127.0.0.1"));
    }

    public function test_forgot_password_queues_reset_email_for_users_company(): void
    {
        Queue::fake();

        $this->post(route('password.email'), ['email' => $this->user->email])
            ->assertSessionHas('status');

        Queue::assertPushed(SendMailJob::class, function (SendMailJob $job) {
            return $job->to === $this->user->email
                && $job->companyId === $this->company->id
                && $job->mailable instanceof ResetPasswordMail
                && str_contains($job->mailable->render(), '/reset-password/');
        });
    }

    public function test_forgot_password_does_not_reveal_unknown_emails(): void
    {
        Queue::fake();

        $this->post(route('password.email'), ['email' => 'nobody-'.uniqid().'@example.com'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        Queue::assertNothingPushed();
    }

    public function test_mail_service_uses_active_account_or_default_mailer(): void
    {
        $service = app(MailService::class);

        $this->assertNull($service->activeSetting($this->company->id));

        $setting = SmtpSetting::create($this->payload(['company_id' => $this->company->id, 'name' => 'Active', 'username' => 'u']));
        $setting->activate();

        $this->assertTrue($service->activeSetting($this->company->id)->is($setting));
        $this->assertStringContainsString('smtp.gmail.com:587', (string) $service->mailerFor($this->company->id)->getSymfonyTransport());
    }
}
