<?php

namespace Tests\Feature;

use App\Enums\CustomerNotificationEvent;
use App\Jobs\SendMailJob;
use App\Mail\CustomerOrderUpdateMail;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Whichever part of the app changes an order's status, every person at the customer
 * gets the in-app notification, and only those who opted in to that status get email.
 */
class OrderStatusNotificationTest extends TestCase
{
    use DatabaseTransactions;

    private Company $company;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $suffix = uniqid();

        $this->company = Company::create([
            'name' => 'Status Co '.$suffix,
            'slug' => 'status-co-'.$suffix,
            'shortcode' => 'STAT',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $this->customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Status Customer',
            'short_code' => strtoupper(substr(uniqid(), -4)),
            'is_active' => true,
            'is_deleted' => false,
        ]);
    }

    private function makeContact(array $emailEvents, bool $portalAccess = true): CustomerContact
    {
        return CustomerContact::create([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'first_name' => 'Person',
            'email' => 'person.'.uniqid().'@example.com',
            'portal_access' => $portalAccess,
            'password' => 'Str0ng!Passw0rd',
            'notification_prefs' => CustomerNotificationEvent::prefsFromEmailInput(
                array_fill_keys($emailEvents, true)
            ),
        ]);
    }

    private function makeOrder(string $status = 'new'): Order
    {
        return Order::create([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'STAT-'.uniqid(),
            'order_type' => 'point_to_point',
            'status' => $status,
        ]);
    }

    public function test_everyone_in_the_organisation_is_notified_in_app_but_email_follows_each_persons_choice(): void
    {
        Queue::fake();

        $wantsDelivered = $this->makeContact([CustomerNotificationEvent::Delivered->value]);
        $wantsBooked = $this->makeContact([CustomerNotificationEvent::Booked->value]);
        $wantsNothing = $this->makeContact([]);

        $order = $this->makeOrder('in_transit');
        $order->update(['status' => 'delivered']);

        // All three are in the organisation, so all three see it in the portal
        $this->assertSame(1, $wantsDelivered->notifications()->count());
        $this->assertSame(1, $wantsBooked->notifications()->count());
        $this->assertSame(1, $wantsNothing->notifications()->count());

        // Only the person who asked for "Delivered" gets an email
        Queue::assertPushed(SendMailJob::class, 1);
        Queue::assertPushed(SendMailJob::class, function (SendMailJob $job) use ($wantsDelivered) {
            return $job->to === $wantsDelivered->email
                && $job->mailable instanceof CustomerOrderUpdateMail;
        });
    }

    public function test_status_changed_from_the_web_app_notifies_the_customer(): void
    {
        Queue::fake();

        $contact = $this->makeContact([CustomerNotificationEvent::Booked->value]);
        $order = $this->makeOrder('quoted');

        // The same path the Mark as Booked button takes
        $order->update(['status' => 'booked']);

        $this->assertSame(1, $contact->notifications()->count());
        Queue::assertPushed(SendMailJob::class, 1);
    }

    public function test_internal_statuses_notify_nobody(): void
    {
        Queue::fake();

        $contact = $this->makeContact([CustomerNotificationEvent::Delivered->value]);
        $order = $this->makeOrder('draft');

        $order->update(['status' => 'new']);
        $order->update(['status' => 'warehousing']);

        $this->assertSame(0, $contact->notifications()->count());
        Queue::assertNothingPushed();
    }

    public function test_saving_an_order_without_changing_status_notifies_nobody(): void
    {
        Queue::fake();

        $contact = $this->makeContact([CustomerNotificationEvent::Delivered->value]);
        $order = $this->makeOrder('delivered');

        $order->update(['ref_number' => 'REF-123']);

        $this->assertSame(0, $contact->notifications()->count());
        Queue::assertNothingPushed();
    }

    public function test_a_person_without_portal_access_gets_email_only(): void
    {
        Queue::fake();

        $emailOnly = $this->makeContact([CustomerNotificationEvent::Delivered->value], portalAccess: false);

        $this->makeOrder('in_transit')->update(['status' => 'delivered']);

        $this->assertSame(0, $emailOnly->notifications()->count());
        Queue::assertPushed(SendMailJob::class, 1);
    }
}
