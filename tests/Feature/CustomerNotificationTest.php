<?php

namespace Tests\Feature;

use App\Enums\CustomerNotificationEvent;
use App\Jobs\SendMailJob;
use App\Mail\CustomerOrderUpdateMail;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Order;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Customer notifications: in-app for everyone with portal access, email only for the
 * people who opted in to that event, queued and sent through the company's SMTP account.
 */
class CustomerNotificationTest extends TestCase
{
    use DatabaseTransactions;

    private Company $company;

    private Customer $customer;

    private NotificationService $notifications;

    protected function setUp(): void
    {
        parent::setUp();

        $suffix = uniqid();

        $this->company = Company::create([
            'name' => 'Notify Co '.$suffix,
            'slug' => 'notify-co-'.$suffix,
            'shortcode' => 'NOTF',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $this->customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Notify Customer',
            'short_code' => strtoupper(substr(uniqid(), -4)),
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $this->notifications = app(NotificationService::class);
    }

    private function makeContact(array $overrides = []): CustomerContact
    {
        return CustomerContact::create(array_merge([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'first_name' => 'Person',
            'email' => 'person.'.uniqid().'@example.com',
            'portal_access' => true,
            'password' => 'Str0ng!Passw0rd',
            'notification_prefs' => CustomerNotificationEvent::prefsFromEmailInput([
                CustomerNotificationEvent::Quoted->value => true,
            ]),
        ], $overrides));
    }

    private function makeOrder(): Order
    {
        return Order::create([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'NOTF-'.uniqid(),
            'order_type' => 'point_to_point',
            'status' => 'quoted',
        ]);
    }

    public function test_email_is_queued_only_for_people_who_opted_in(): void
    {
        Queue::fake();

        $optedIn = $this->makeContact();
        $notOptedIn = $this->makeContact([
            'notification_prefs' => CustomerNotificationEvent::defaults(),
        ]);

        $this->notifications->orderQuotedForCustomer($this->makeOrder(), $this->company);

        Queue::assertPushed(SendMailJob::class, 1);
        Queue::assertPushed(SendMailJob::class, function (SendMailJob $job) use ($optedIn) {
            return $job->to === $optedIn->email
                && $job->companyId === $this->company->id
                && $job->mailable instanceof CustomerOrderUpdateMail;
        });

        // Everyone with portal access still gets the in-app notification
        $this->assertSame(1, $optedIn->notifications()->count());
        $this->assertSame(1, $notOptedIn->notifications()->count());
    }

    public function test_people_without_portal_access_get_no_in_app_notification(): void
    {
        Queue::fake();

        $contact = $this->makeContact(['portal_access' => false]);

        $this->notifications->orderQuotedForCustomer($this->makeOrder(), $this->company);

        $this->assertSame(0, $contact->notifications()->count());
        // but an opted-in person is still emailed
        Queue::assertPushed(SendMailJob::class, 1);
    }

    public function test_an_inactive_customer_is_never_notified(): void
    {
        Queue::fake();

        $contact = $this->makeContact();
        $this->customer->update(['is_active' => false]);

        $this->notifications->orderQuotedForCustomer($this->makeOrder(), $this->company);

        Queue::assertNothingPushed();
        $this->assertSame(0, $contact->notifications()->count());
    }

    public function test_every_module_has_its_own_log_channel(): void
    {
        foreach (['mail', 'notifications', 'customers', 'manifests', 'portal', 'quickbooks', 'orders'] as $channel) {
            $config = config("logging.channels.{$channel}");

            $this->assertIsArray($config, "Missing log channel: {$channel}");
            $this->assertNotEmpty($config['path'] ?? null, "Channel {$channel} has no log file");
        }
    }
}
