<?php

namespace Tests\Feature\Portal;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPortalOrderTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Customer $customer;

    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Test Freight Co',
            'slug' => 'test-freight-co',
            'shortcode' => 'TEST',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $this->customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Portal Customer Inc',
            'customer_email' => 'portal@customer.com',
            'portal' => true,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $this->order = Order::create([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'TEST-001',
            'order_type' => 'point_to_point',
            'status' => 'booked',
        ]);

        $otherCustomer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Other Customer',
            'customer_email' => 'other@customer.com',
            'portal' => true,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $this->otherOrder = Order::create([
            'company_id' => $this->company->id,
            'customer_id' => $otherCustomer->id,
            'order_number' => 'TEST-OTHER-001',
            'order_type' => 'point_to_point',
            'status' => 'booked',
        ]);
    }

    protected Order $otherOrder;

    public function test_customer_can_view_own_orders_list(): void
    {
        $response = $this->actingAs($this->customer, 'customer')
            ->get(route('portal.orders.index', ['company' => $this->company->slug]));

        $response->assertStatus(200);
        $response->assertSee($this->order->order_number);
        $response->assertDontSee($this->otherOrder->order_number);
    }

    public function test_customer_can_view_own_order_detail(): void
    {
        $response = $this->actingAs($this->customer, 'customer')
            ->get(route('portal.orders.show', [
                'company' => $this->company->slug,
                'order' => $this->order,
            ]));

        $response->assertStatus(200);
        $response->assertSee($this->order->order_number);
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $response = $this->actingAs($this->customer, 'customer')
            ->get(route('portal.orders.show', [
                'company' => $this->company->slug,
                'order' => $this->otherOrder,
            ]));

        $response->assertStatus(403);
    }
}
