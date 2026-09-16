<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Order;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_order_submission_notifies_company_users(): void
    {
        $company = Company::create([
            'name' => 'Notify Co',
            'slug' => 'notify-co',
            'shortcode' => 'NTFY',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $staff = User::factory()->create([
            'company_id' => $company->id,
            'is_active' => true,
            'is_deleted' => false,
            'is_super_admin' => true,
        ]);

        $customer = Customer::create([
            'company_id' => $company->id,
            'name' => 'Notify Customer',
            'customer_email' => 'notify-customer@example.com',
            'portal' => true,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $order = Order::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'order_number' => 'NTFY-001',
            'order_type' => 'point_to_point',
            'status' => 'new',
        ]);

        app(NotificationService::class)->customerSubmittedOrder($order, $company);

        $this->assertSame(1, $staff->fresh()->unreadNotifications()->count());
        $this->assertSame(
            'Customer submitted an order',
            $staff->fresh()->unreadNotifications()->first()->data['title']
        );
    }

    public function test_driver_status_update_notifies_customer(): void
    {
        $company = Company::create([
            'name' => 'Driver Notify Co',
            'slug' => 'driver-notify-co',
            'shortcode' => 'DRNT',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $driver = User::factory()->create([
            'company_id' => $company->id,
            'name' => 'Driver One',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $customer = Customer::create([
            'company_id' => $company->id,
            'name' => 'Shipment Customer',
            'customer_email' => 'shipment@example.com',
            'portal' => true,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $contact = CustomerContact::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'first_name' => 'Shipment',
            'email' => 'shipment@example.com',
            'portal_access' => true,
        ]);

        $order = Order::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'order_number' => 'DRNT-001',
            'order_type' => 'point_to_point',
            'status' => 'in_transit',
        ]);

        app(NotificationService::class)->driverUpdatedOrderStatus(
            $order,
            $driver,
            'booked',
            'in_transit'
        );

        $this->assertSame(1, $contact->fresh()->unreadNotifications()->count());
        $this->assertStringContainsString(
            'in transit',
            strtolower($contact->fresh()->unreadNotifications()->first()->data['body'])
        );
    }

    public function test_company_notification_api_returns_unread_items(): void
    {
        $company = Company::create([
            'name' => 'API Notify Co',
            'slug' => 'api-notify-co',
            'shortcode' => 'APIN',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $staff = User::factory()->create([
            'company_id' => $company->id,
            'is_active' => true,
            'is_deleted' => false,
            'is_super_admin' => true,
        ]);

        $staff->notify(new \App\Notifications\TmsDatabaseNotification([
            'type' => 'test',
            'title' => 'Test notification',
            'body' => 'Hello from tests',
            'icon' => 'info',
            'url' => null,
        ]));

        $response = $this->actingAs($staff)
            ->getJson(route('v2.notifications.index', ['company' => $company->slug]));

        $response->assertOk();
        $response->assertJsonPath('unread_count', 1);
        $response->assertJsonPath('notifications.0.title', 'Test notification');
    }
}
