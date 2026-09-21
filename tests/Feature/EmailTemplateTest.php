<?php

namespace Tests\Feature;

use App\Mail\CustomerOrderUpdateMail;
use App\Mail\PortalWelcomeMail;
use App\Mail\ResetPasswordMail;
use App\Mail\SmtpTestMail;
use Tests\TestCase;

/**
 * Every email renders through the shared layout. Templates are otherwise only
 * exercised when a real email goes out, where a mistake is expensive to spot.
 */
class EmailTemplateTest extends TestCase
{
    /** Markers that only exist in resources/views/emails/layout.blade.php */
    private function assertUsesSharedLayout(string $html, string $context): void
    {
        $this->assertStringContainsString('background-color:#f6f7f9', $html, "{$context}: not the shared layout");
        $this->assertStringContainsString('border-radius:12px', $html, "{$context}: missing the layout card");
    }

    public function test_password_reset_email_renders(): void
    {
        $html = (new ResetPasswordMail(
            userName: 'Sam',
            url: 'https://example.test/reset-password/token-123',
            expireMinutes: 60,
            brandName: 'Acme Freight',
        ))->render();

        $this->assertUsesSharedLayout($html, 'reset password');
        $this->assertStringContainsString('Reset your password', $html);
        $this->assertStringContainsString('Hi Sam', $html);
        $this->assertStringContainsString('https://example.test/reset-password/token-123', $html);
        $this->assertStringContainsString('60 minutes', $html);
        $this->assertStringContainsString('Acme Freight', $html);
    }

    public function test_smtp_test_email_renders(): void
    {
        $html = (new SmtpTestMail(
            fromAddress: 'dispatch@acme.test',
            providerLabel: 'Gmail',
            host: 'smtp.gmail.com',
            brandName: 'Acme Freight',
        ))->render();

        $this->assertUsesSharedLayout($html, 'smtp test');
        $this->assertStringContainsString('Your email settings work', $html);
        $this->assertStringContainsString('dispatch@acme.test', $html);
        $this->assertStringContainsString('smtp.gmail.com', $html);
    }

    public function test_order_update_email_renders_with_and_without_a_link(): void
    {
        $withLink = (new CustomerOrderUpdateMail(
            recipientName: 'Pat',
            title: 'Your order has been delivered',
            body: 'Order #ACME-1 is now Delivered.',
            url: 'https://example.test/acme/portal/orders/1',
            brandName: 'Acme Freight',
        ))->render();

        $this->assertUsesSharedLayout($withLink, 'order update');
        $this->assertStringContainsString('Your order has been delivered', $withLink);
        $this->assertStringContainsString('View order', $withLink);
        $this->assertStringContainsString('https://example.test/acme/portal/orders/1', $withLink);

        // People without portal access get the same email minus the button
        $withoutLink = (new CustomerOrderUpdateMail(
            recipientName: 'Pat',
            title: 'Your order has been delivered',
            body: 'Order #ACME-1 is now Delivered.',
            url: null,
            brandName: 'Acme Freight',
        ))->render();

        $this->assertStringNotContainsString('View order', $withoutLink);
    }

    public function test_portal_welcome_email_carries_the_app_name_and_portal_link(): void
    {
        $html = (new PortalWelcomeMail(
            recipientName: 'Pat',
            appName: config('app.name'),
            companyName: 'Acme Freight',
            portalUrl: 'https://example.test/acme/portal/login',
            signInEmail: 'pat@acme.test',
        ))->render();

        $this->assertUsesSharedLayout($html, 'portal welcome');
        $this->assertStringContainsString('Welcome to '.config('app.name'), $html);
        $this->assertStringContainsString('Acme Freight', $html);
        $this->assertStringContainsString('https://example.test/acme/portal/login', $html);
        $this->assertStringContainsString('pat@acme.test', $html);
        $this->assertStringContainsString('Open your portal', $html);
    }

    public function test_the_welcome_email_includes_the_password_when_one_was_set(): void
    {
        $html = (new PortalWelcomeMail(
            recipientName: 'Pat',
            appName: 'TMS',
            companyName: 'Acme Freight',
            portalUrl: 'https://example.test/acme/portal/login',
            signInEmail: 'pat@acme.test',
            password: 'Str0ng!Pass',
        ))->render();

        $this->assertStringContainsString('pat@acme.test', $html);
        $this->assertStringContainsString(e('Str0ng!Pass'), $html);
        $this->assertStringNotContainsString('set by Acme Freight', $html);
    }

    public function test_the_welcome_email_without_a_password_says_where_to_get_it(): void
    {
        $html = (new PortalWelcomeMail(
            recipientName: 'Pat',
            appName: 'TMS',
            companyName: 'Acme Freight',
            portalUrl: 'https://example.test/acme/portal/login',
            signInEmail: 'pat@acme.test',
        ))->render();

        $this->assertStringContainsString('set by Acme Freight', $html);
    }
}
