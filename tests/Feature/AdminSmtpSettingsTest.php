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

/**
 * Platform-wide email accounts (null company_id), managed by the super admin.
 * They send super admin email and act as the fallback for any company that has
 * not connected an account of its own.
 */
class AdminSmtpSettingsTest extends TestCase
{
    use DatabaseTransactions;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        // The admin route group is behind auth + verified + IsSuperAdmin
        $this->superAdmin = User::create([
            'company_id' => null,
            'name' => 'Root Admin',
            'f_name' => 'Root',
            'l_name' => 'Admin',
            'email' => 'root.admin.'.uniqid().'@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'status' => 'active',
            'is_active' => true,
            'is_deleted' => false,
            'is_super_admin' => true,
        ]);

        // Existing platform accounts would skew the "first account" assertions
        SmtpSetting::forCompany(null)->delete();
    }

    private function makeCompany(): Company
    {
        $suffix = uniqid();

        return Company::create([
            'name' => 'Fallback Co '.$suffix,
            'slug' => 'fallback-co-'.$suffix,
            'shortcode' => 'FBC',
            'is_active' => true,
            'is_deleted' => false,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'provider' => 'gmail',
            'from_address' => 'platform@example.com',
            'from_name' => 'Platform',
            'username' => '',
            'password' => 'abcd efgh ijkl mnop',
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'encryption' => 'tls',
            'is_active' => 0,
        ], $overrides);
    }

    public function test_settings_hub_links_to_email_and_pages_render_empty(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('Email (SMTP)', false);

        $this->actingAs($this->superAdmin)
            ->get(route('admin.settings.smtp.index'))
            ->assertOk()
            ->assertSee('Connect your email account');

        $this->actingAs($this->superAdmin)
            ->get(route('admin.settings.smtp.create'))
            ->assertOk()
            ->assertSee('Which email service do you use?');
    }

    public function test_first_platform_account_is_activated_and_password_never_surfaces(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.settings.smtp.store'), $this->payload())
            ->assertRedirect();

        $setting = SmtpSetting::forCompany(null)->firstOrFail();

        $this->assertNull($setting->company_id);
        $this->assertTrue($setting->is_active);
        $this->assertSame('platform@example.com', $setting->username);
        $this->assertSame('abcdefghijklmnop', $setting->password);
        // Encrypted at rest, so the raw column must not hold the plaintext
        $this->assertNotSame('abcdefghijklmnop', DB::table('smtp_settings')->where('id', $setting->id)->value('password'));

        foreach (['index', 'show', 'edit'] as $page) {
            $this->actingAs($this->superAdmin)
                ->get(route("admin.settings.smtp.{$page}", $page === 'index' ? [] : $setting))
                ->assertOk()
                ->assertDontSee('abcdefghijklmnop');
        }
    }

    public function test_update_keeps_password_when_blank_and_delete_works(): void
    {
        $this->actingAs($this->superAdmin)->post(route('admin.settings.smtp.store'), $this->payload());
        $setting = SmtpSetting::forCompany(null)->firstOrFail();

        $this->actingAs($this->superAdmin)
            ->put(route('admin.settings.smtp.update', $setting), $this->payload([
                'password' => '',
                'from_name' => 'Renamed Platform',
                'is_active' => 1,
            ]))
            ->assertRedirect(route('admin.settings.smtp.show', $setting));

        $setting->refresh();
        $this->assertSame('Renamed Platform', $setting->from_name);
        $this->assertSame('abcdefghijklmnop', $setting->password);

        $this->actingAs($this->superAdmin)
            ->delete(route('admin.settings.smtp.destroy', $setting))
            ->assertRedirect(route('admin.settings.smtp.index'));
        $this->assertNull($setting->fresh());
    }

    public function test_company_owned_accounts_are_not_reachable_from_the_platform_scope(): void
    {
        $company = $this->makeCompany();
        $owned = SmtpSetting::create($this->payload([
            'company_id' => $company->id,
            'name' => 'Company owned',
            'username' => 'x',
        ]));

        $this->actingAs($this->superAdmin)
            ->get(route('admin.settings.smtp.show', $owned))
            ->assertNotFound();

        $this->actingAs($this->superAdmin)
            ->delete(route('admin.settings.smtp.destroy', $owned))
            ->assertNotFound();

        $this->assertNotNull($owned->fresh());
    }

    public function test_non_super_admin_cannot_reach_platform_email_settings(): void
    {
        $company = $this->makeCompany();
        $regular = User::create([
            'company_id' => $company->id,
            'name' => 'Reg User',
            'f_name' => 'Reg',
            'l_name' => 'User',
            'email' => 'reg.user.'.uniqid().'@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'status' => 'active',
            'is_active' => true,
            'is_deleted' => false,
            'is_super_admin' => false,
        ]);

        $this->actingAs($regular)
            ->get(route('admin.settings.smtp.index'))
            ->assertRedirect();
    }

    public function test_platform_account_is_the_fallback_for_a_company_without_its_own(): void
    {
        $service = app(MailService::class);
        $company = $this->makeCompany();

        // Nothing configured anywhere yet
        $this->assertNull($service->activeSetting($company->id));

        $platform = SmtpSetting::create($this->payload([
            'company_id' => null,
            'name' => 'Platform',
            'username' => 'platform@example.com',
            'host' => 'smtp.platform.test',
        ]));
        $platform->activate();

        // The company has no account, so it falls back to the platform one
        $this->assertTrue($service->activeSetting($company->id)->is($platform));

        $own = SmtpSetting::create($this->payload([
            'company_id' => $company->id,
            'name' => 'Company own',
            'username' => 'own@example.com',
            'host' => 'smtp.own.test',
        ]));
        $own->activate();

        // Its own account wins once connected, and the platform one is untouched
        $this->assertTrue($service->activeSetting($company->id)->is($own));
        $this->assertTrue($platform->fresh()->is_active);
        $this->assertTrue($service->activeSetting(null)->is($platform));
    }

    public function test_super_admin_password_reset_queues_against_the_platform_scope(): void
    {
        Queue::fake();

        $this->post(route('password.email'), ['email' => $this->superAdmin->email])
            ->assertSessionHas('status');

        Queue::assertPushed(SendMailJob::class, function (SendMailJob $job) {
            // A null companyId is what routes the email to the platform account
            return $job->to === $this->superAdmin->email
                && $job->companyId === null
                && $job->mailable instanceof ResetPasswordMail;
        });
    }
}
