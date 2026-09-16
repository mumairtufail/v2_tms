<?php

namespace Tests\Feature\Customers;

use App\Enums\CustomerNotificationEvent;
use App\Jobs\SendMailJob;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Order;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CustomerNotificationPreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_in_app_goes_to_portal_people_and_email_follows_each_persons_choice(): void
    {
        Queue::fake();

        $company = Company::create([
            'name' => 'Pref Freight',
            'slug' => 'pref-freight-' . uniqid(),
            'shortcode' => 'PREF',
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $customer = Customer::create(['company_id' => $company->id, 'name' => 'Boscus', 'is_active' => true, 'is_deleted' => false]);

        $person = fn (string $email, bool $portal, bool $emailBooked) => CustomerContact::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'first_name' => strtok($email, '@'),
            'email' => $email,
            'portal_access' => $portal,
            'notification_prefs' => CustomerNotificationEvent::prefsFromEmailInput(['order_booked' => $emailBooked]),
        ]);

        $portalOptedIn = $person('portal@boscus.com', true, true);
        $contactOnly = $person('quiet@boscus.com', false, false);
        $emailOnly = $person('email@boscus.com', false, true);

        $order = Order::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'order_number' => 'PREF-B34F-1',
            'order_type' => 'point_to_point',
            'status' => 'booked',
        ]);

        app(NotificationService::class)->orderBookedForCustomer($order, $company);

        $this->assertSame(1, $portalOptedIn->unreadNotifications()->count());
        $this->assertSame(0, $contactOnly->unreadNotifications()->count());
        $this->assertSame(0, $emailOnly->unreadNotifications()->count(), 'no portal access, so nothing in-app');

        Queue::assertPushed(SendMailJob::class, 2);
        Queue::assertPushed(SendMailJob::class, fn (SendMailJob $job) => $job->to === 'portal@boscus.com');
        Queue::assertPushed(SendMailJob::class, fn (SendMailJob $job) => $job->to === 'email@boscus.com');
        Queue::assertNotPushed(SendMailJob::class, fn (SendMailJob $job) => $job->to === 'quiet@boscus.com');
    }

    public function test_inactive_customer_gets_nothing(): void
    {
        Queue::fake();

        $company = Company::create([
            'name' => 'Pref Freight',
            'slug' => 'pref-freight-' . uniqid(),
            'shortcode' => 'PREF',
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $customer = Customer::create(['company_id' => $company->id, 'name' => 'Dormant Co', 'is_active' => false, 'is_deleted' => false]);
        $contact = CustomerContact::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'first_name' => 'Dee',
            'email' => 'dee@dormant.com',
            'portal_access' => true,
            'notification_prefs' => CustomerNotificationEvent::prefsFromEmailInput(['order_quoted' => true]),
        ]);
        $order = Order::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'order_number' => 'PREF-DC-1',
            'order_type' => 'point_to_point',
            'status' => 'quoted',
        ]);

        app(NotificationService::class)->orderQuotedForCustomer($order, $company);

        $this->assertSame(0, $contact->unreadNotifications()->count());
        Queue::assertNothingPushed();
    }
}
