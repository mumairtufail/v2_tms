<?php

namespace Tests\Feature;

use App\Models\ActivityLogs;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrderActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected User $user;

    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Order Log Co',
            'slug' => 'order-log-co',
            'shortcode' => 'OLC',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $this->user = User::create([
            'company_id' => $this->company->id,
            'name' => 'John Dispatcher',
            'f_name' => 'John',
            'l_name' => 'Dispatcher',
            'email' => 'john@orderlog.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'is_active' => true,
            'is_deleted' => false,
            'is_super_admin' => true,
        ]);

        $customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Acme Shipping',
            'customer_email' => 'acme@example.com',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $this->order = Order::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'order_number' => 'OLC-ACME-1',
            'order_type' => 'point_to_point',
            'status' => 'draft',
        ]);
    }

    public function test_order_activity_logs_endpoint_returns_order_logs(): void
    {
        ActivityLogs::create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'order_id' => $this->order->id,
            'action' => 'v2.orders.store',
            'method' => 'POST',
            'is_successful' => true,
            'data' => [
                'description' => 'Added the order',
                'actor_name' => 'John Dispatcher',
            ],
        ]);

        ActivityLogs::create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'order_id' => $this->order->id,
            'action' => 'v2.orders.update',
            'method' => 'PATCH',
            'is_successful' => true,
            'data' => [
                'description' => 'Updated the order',
                'actor_name' => 'John Dispatcher',
            ],
        ]);

        // Unrelated order log should not appear
        $otherOrder = Order::create([
            'company_id' => $this->company->id,
            'customer_id' => $this->order->customer_id,
            'order_number' => 'OLC-ACME-2',
            'order_type' => 'point_to_point',
            'status' => 'draft',
        ]);

        ActivityLogs::create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'order_id' => $otherOrder->id,
            'action' => 'v2.orders.update',
            'method' => 'PATCH',
            'is_successful' => true,
            'data' => ['description' => 'Updated another order'],
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('v2.orders.activity-logs', [
                'company' => $this->company->slug,
                'order' => $this->order->id,
            ]));

        $response->assertOk();
        $response->assertJsonPath('order_number', 'OLC-ACME-1');
        $response->assertJsonCount(2, 'logs');
        $response->assertJsonFragment(['description' => 'Added the order']);
        $response->assertJsonFragment(['description' => 'Updated the order']);
        $response->assertJsonMissing(['description' => 'Updated another order']);
    }

    public function test_creating_order_attaches_order_id_to_activity_log(): void
    {
        $customer = Customer::find($this->order->customer_id);

        $response = $this->actingAs($this->user)
            ->post(route('v2.orders.store', ['company' => $this->company->slug]), [
                'customer_id' => $customer->id,
                'order_type' => 'point_to_point',
            ]);

        $response->assertRedirect();

        $created = Order::query()
            ->where('company_id', $this->company->id)
            ->where('id', '!=', $this->order->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($created);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'v2.orders.store',
            'order_id' => $created->id,
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_orders_index_includes_activity_log_panel(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('v2.orders.index', ['company' => $this->company->slug]));

        $response->assertOk();
        $response->assertSee('openLogs', false);
        $response->assertSee('orderLogsPanel', false);
        $response->assertSee('View activity logs', false);
    }
}
