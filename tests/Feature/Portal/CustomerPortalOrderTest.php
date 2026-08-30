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

    public function test_customer_can_create_draft_order(): void
    {
        $response = $this->actingAs($this->customer, 'customer')
            ->post(route('portal.orders.store', ['company' => $this->company->slug]));

        $response->assertRedirect();

        $order = Order::query()
            ->where('customer_id', $this->customer->id)
            ->where('status', 'draft')
            ->latest('id')
            ->first();

        $this->assertNotNull($order);
        $this->assertSame('point_to_point', $order->order_type);
        $response->assertRedirect(route('portal.orders.edit', [
            'company' => $this->company->slug,
            'order' => $order->id,
        ]));
    }

    public function test_customer_can_edit_draft_order(): void
    {
        $draft = Order::create([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'TEST-DRAFT-001',
            'order_type' => 'point_to_point',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->customer, 'customer')
            ->get(route('portal.orders.edit', [
                'company' => $this->company->slug,
                'order' => $draft,
            ]));

        $response->assertStatus(200);
        $response->assertSee('Origin-to-Destination');
        $response->assertSee('Submit Order');
    }

    public function test_customer_cannot_edit_non_draft_order(): void
    {
        $response = $this->actingAs($this->customer, 'customer')
            ->get(route('portal.orders.edit', [
                'company' => $this->company->slug,
                'order' => $this->order,
            ]));

        $response->assertStatus(403);
    }

    public function test_invalid_lat_lng_values_are_ignored_when_saving(): void
    {
        $draft = Order::create([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'TEST-DRAFT-LAT',
            'order_type' => 'point_to_point',
            'status' => 'draft',
        ]);

        $stops = json_encode([[
            'service_type' => 'truckload',
            'measurements' => 'in_lbs',
            'shipper' => [
                'company_name' => 'Ship From Co',
                'address_1' => '123 Main',
                'city' => 'Toronto',
                'state' => 'ON',
                'zip' => 'M5V 1A1',
                'country' => 'CA',
                'lat' => 'not-a-coordinate',
                'lng' => 'also invalid',
            ],
            'consignee' => [
                'company_name' => 'Ship To Co',
                'address_1' => '456 Oak',
                'city' => 'Montreal',
                'state' => 'QC',
                'zip' => 'H2Y 1C6',
                'country' => 'CA',
                'lat' => 'bad lat',
                'lng' => 'bad lng',
            ],
            'billing' => [],
            'commodities' => [[
                'description' => 'Widgets',
                'qty' => 1,
                'type' => 'skid',
                'weight' => 100,
            ]],
            'accessorials' => [],
            'special_instructions' => 'Handle with care',
        ]]);

        $response = $this->actingAs($this->customer, 'customer')
            ->patch(route('portal.orders.update', [
                'company' => $this->company->slug,
                'order' => $draft,
            ]), [
                'order_type' => 'point_to_point',
                'stops' => $stops,
                'quote_data' => '{}',
                'save_as_draft' => '1',
                'submission_mode' => 'draft',
            ]);

        $response->assertRedirect();

        $stop = $draft->fresh()->stops->first();
        $this->assertNotNull($stop);
        $this->assertNull($stop->lat);
        $this->assertNull($stop->lng);
        $this->assertNull($stop->consignee_data['lat'] ?? null);
        $this->assertNull($stop->consignee_data['lng'] ?? null);
    }

    public function test_customer_can_save_draft_order(): void
    {
        $draft = Order::create([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'TEST-DRAFT-002',
            'order_type' => 'point_to_point',
            'status' => 'draft',
        ]);

        $stops = json_encode([[
            'service_type' => 'truckload',
            'measurements' => 'in_lbs',
            'shipper' => [
                'company_name' => 'Ship From Co',
                'address_1' => '123 Main',
                'city' => 'Toronto',
                'state' => 'ON',
                'zip' => 'M5V 1A1',
                'country' => 'CA',
            ],
            'consignee' => [
                'company_name' => 'Ship To Co',
                'address_1' => '456 Oak',
                'city' => 'Montreal',
                'state' => 'QC',
                'zip' => 'H2Y 1C6',
                'country' => 'CA',
            ],
            'billing' => [],
            'commodities' => [[
                'description' => 'Widgets',
                'qty' => 1,
                'type' => 'skid',
                'weight' => 100,
            ]],
            'accessorials' => [],
            'special_instructions' => 'Handle with care',
        ]]);

        $response = $this->actingAs($this->customer, 'customer')
            ->patch(route('portal.orders.update', [
                'company' => $this->company->slug,
                'order' => $draft,
            ]), [
                'order_type' => 'point_to_point',
                'ref_number' => 'REF-123',
                'stops' => $stops,
                'quote_data' => '{}',
                'save_as_draft' => '1',
                'submission_mode' => 'draft',
            ]);

        $response->assertRedirect(route('portal.orders.edit', [
            'company' => $this->company->slug,
            'order' => $draft->id,
        ]));

        $draft->refresh();
        $this->assertSame('draft', $draft->status);
        $this->assertSame('REF-123', $draft->ref_number);
        $this->assertCount(1, $draft->stops);
    }

    public function test_customer_can_submit_draft_order(): void
    {
        $draft = Order::create([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'TEST-DRAFT-003',
            'order_type' => 'point_to_point',
            'status' => 'draft',
        ]);

        $stops = json_encode([[
            'service_type' => 'truckload',
            'measurements' => 'in_lbs',
            'shipper' => [
                'company_name' => 'Ship From Co',
                'address_1' => '123 Main',
                'city' => 'Toronto',
                'state' => 'ON',
                'zip' => 'M5V 1A1',
                'country' => 'CA',
            ],
            'consignee' => [
                'company_name' => 'Ship To Co',
                'address_1' => '456 Oak',
                'city' => 'Montreal',
                'state' => 'QC',
                'zip' => 'H2Y 1C6',
                'country' => 'CA',
            ],
            'billing' => [],
            'commodities' => [[
                'description' => 'Widgets',
                'qty' => 1,
                'type' => 'skid',
                'weight' => 100,
            ]],
            'accessorials' => [],
            'special_instructions' => 'Handle with care',
        ]]);

        $response = $this->actingAs($this->customer, 'customer')
            ->patch(route('portal.orders.update', [
                'company' => $this->company->slug,
                'order' => $draft,
            ]), [
                'order_type' => 'point_to_point',
                'stops' => $stops,
                'quote_data' => '{}',
                'save_as_draft' => '0',
                'submission_mode' => 'new',
            ]);

        $response->assertRedirect(route('portal.orders.show', [
            'company' => $this->company->slug,
            'order' => $draft->id,
        ]));

        $draft->refresh();
        $this->assertSame('new', $draft->status);
    }
}
