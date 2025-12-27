<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Accessorial;

class SingleShipperOrderTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $company;
    protected $customer;
    protected $order;
    protected $accessorials;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test company
        $this->company = Company::factory()->create([
            'name' => 'Test Company',
            'email' => 'test@company.com'
        ]);

        // Create test user
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'email' => 'testuser@company.com'
        ]);

        // Create test customer
        $this->customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'company_name' => 'Test Customer Inc.'
        ]);

        // Create accessorials for testing
        $this->accessorials = Accessorial::factory()->count(5)->create();

        // Create a draft order
        $this->order = Order::create([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'order_type' => 'single_shipper',
            'status' => 'draft',
            'order_number' => 'ORD-000001'
        ]);
    }

    public function test_single_shipper_form_saves_correctly_with_multiple_stops()
    {
        // Act as the authenticated user
        $this->actingAs($this->user);

        // Prepare test data that mimics what the form would send
        $formData = [
            // Shipper data
            'ss_company_name' => 'Main Shipper Co.',
            'ss_address_1' => '123 Shipper Street',
            'ss_address_2' => 'Suite 100',
            'ss_city' => 'Shipper City',
            'ss_state' => 'CA',
            'ss_zip' => '90210',
            'ss_country' => 'US',
            'ss_notes' => 'Special shipper instructions',
            'ss_opening_time' => '08:00',
            'ss_closing_time' => '17:00',
            'ss_contact_name' => 'John Shipper',
            'ss_phone' => '555-0001',
            'ss_contact_email' => 'john@shipper.com',
            'ss_ready_start_time' => '2025-10-06T08:00',
            'ss_ready_end_time' => '2025-10-06T09:00',
            'ss_make_appointment' => '1',

            // Multiple delivery stops
            'stops' => [
                // First delivery stop
                [
                    'consignee_company_name' => 'First Consignee LLC',
                    'consignee_address_1' => '456 First Street',
                    'consignee_address_2' => '',
                    'consignee_city' => 'First City',
                    'consignee_state' => 'NY',
                    'consignee_zip' => '10001',
                    'consignee_country' => 'US',
                    'consignee_notes' => 'First delivery notes',
                    'consignee_opening_time' => '09:00',
                    'consignee_closing_time' => '18:00',
                    'consignee_contact_name' => 'Jane First',
                    'consignee_phone' => '555-0002',
                    'consignee_contact_email' => 'jane@first.com',
                    'delivery_start_time' => '2025-10-07T10:00',
                    'delivery_end_time' => '2025-10-07T11:00',
                    'delivery_appointment' => '0',
                    'commodities' => [
                        [
                            'description' => 'First Stop Commodity 1',
                            'quantity' => '10',
                            'weight' => '100.5',
                            'length' => '12',
                            'width' => '8',
                            'height' => '6'
                        ],
                        [
                            'description' => 'First Stop Commodity 2',
                            'quantity' => '5',
                            'weight' => '50.0',
                            'length' => '10',
                            'width' => '6',
                            'height' => '4'
                        ]
                    ],
                    'accessorials' => [$this->accessorials[0]->id, $this->accessorials[1]->id]
                ],
                // Second delivery stop
                [
                    'consignee_company_name' => 'Second Consignee Corp',
                    'consignee_address_1' => '789 Second Avenue',
                    'consignee_address_2' => 'Floor 3',
                    'consignee_city' => 'Second City',
                    'consignee_state' => 'TX',
                    'consignee_zip' => '75201',
                    'consignee_country' => 'US',
                    'consignee_notes' => 'Second delivery notes',
                    'consignee_opening_time' => '07:00',
                    'consignee_closing_time' => '16:00',
                    'consignee_contact_name' => 'Bob Second',
                    'consignee_phone' => '555-0003',
                    'consignee_contact_email' => 'bob@second.com',
                    'delivery_start_time' => '2025-10-08T14:00',
                    'delivery_end_time' => '2025-10-08T15:00',
                    'delivery_appointment' => '1',
                    'commodities' => [
                        [
                            'description' => 'Second Stop Commodity',
                            'quantity' => '20',
                            'weight' => '200.0',
                            'length' => '15',
                            'width' => '10',
                            'height' => '8'
                        ]
                    ],
                    'accessorials' => [$this->accessorials[2]->id]
                ]
            ]
        ];

        // Submit the form
        $response = $this->post(route('orders.saveSingleShipper', $this->order->id), $formData);

        // Assert the response is successful
        $response->assertStatus(302); // Redirect after successful save
        $response->assertSessionHas('success');

        // Refresh the order to get updated data
        $this->order->refresh();

        // Assert the order was updated correctly
        $this->assertEquals('single_shipper', $this->order->order_type);
        $this->assertEquals('new', $this->order->status);

        // Assert stops were created correctly
        $stops = $this->order->stops()->orderBy('sequence_number')->get();
        $this->assertCount(3, $stops); // 1 pickup + 2 delivery stops

        // Assert pickup stop (shipper)
        $pickupStop = $stops->where('stop_type', 'pickup')->first();
        $this->assertNotNull($pickupStop);
        $this->assertEquals('Main Shipper Co.', $pickupStop->company_name);
        $this->assertEquals('123 Shipper Street', $pickupStop->address_1);
        $this->assertEquals('Shipper City', $pickupStop->city);
        $this->assertEquals('CA', $pickupStop->state);
        $this->assertEquals('90210', $pickupStop->postal_code);
        $this->assertEquals('John Shipper', $pickupStop->contact_name);
        $this->assertEquals(1, $pickupStop->sequence_number);
        $this->assertTrue($pickupStop->is_appointment);

        // Assert first delivery stop
        $deliveryStops = $stops->where('stop_type', 'delivery');
        $firstDelivery = $deliveryStops->where('sequence_number', 2)->first();
        $this->assertNotNull($firstDelivery);
        $this->assertEquals('First Consignee LLC', $firstDelivery->company_name);
        $this->assertEquals('456 First Street', $firstDelivery->address_1);
        $this->assertEquals('First City', $firstDelivery->city);
        $this->assertEquals('NY', $firstDelivery->state);
        $this->assertEquals('10001', $firstDelivery->postal_code);
        $this->assertEquals('Jane First', $firstDelivery->contact_name);
        $this->assertFalse($firstDelivery->is_appointment);

        // Assert second delivery stop
        $secondDelivery = $deliveryStops->where('sequence_number', 3)->first();
        $this->assertNotNull($secondDelivery);
        $this->assertEquals('Second Consignee Corp', $secondDelivery->company_name);
        $this->assertEquals('789 Second Avenue', $secondDelivery->address_1);
        $this->assertEquals('Second City', $secondDelivery->city);
        $this->assertEquals('TX', $secondDelivery->state);
        $this->assertEquals('75201', $secondDelivery->postal_code);
        $this->assertEquals('Bob Second', $secondDelivery->contact_name);
        $this->assertTrue($secondDelivery->is_appointment);

        // Assert commodities were saved correctly
        $firstDeliveryCommodities = $firstDelivery->commodities;
        $this->assertCount(2, $firstDeliveryCommodities);
        
        $commodity1 = $firstDeliveryCommodities->first();
        $this->assertEquals('First Stop Commodity 1', $commodity1->description);
        $this->assertEquals(10, $commodity1->quantity);
        $this->assertEquals(100.5, $commodity1->weight);

        $secondDeliveryCommodities = $secondDelivery->commodities;
        $this->assertCount(1, $secondDeliveryCommodities);
        
        $commodity3 = $secondDeliveryCommodities->first();
        $this->assertEquals('Second Stop Commodity', $commodity3->description);
        $this->assertEquals(20, $commodity3->quantity);
        $this->assertEquals(200.0, $commodity3->weight);

        // Assert accessorials were saved correctly
        $firstDeliveryAccessorials = $firstDelivery->accessorials;
        $this->assertCount(2, $firstDeliveryAccessorials);
        $this->assertTrue($firstDeliveryAccessorials->contains('id', $this->accessorials[0]->id));
        $this->assertTrue($firstDeliveryAccessorials->contains('id', $this->accessorials[1]->id));

        $secondDeliveryAccessorials = $secondDelivery->accessorials;
        $this->assertCount(1, $secondDeliveryAccessorials);
        $this->assertTrue($secondDeliveryAccessorials->contains('id', $this->accessorials[2]->id));
    }

    public function test_single_shipper_form_requires_minimum_data()
    {
        $this->actingAs($this->user);

        // Test with incomplete data
        $incompleteData = [
            'ss_company_name' => '', // Missing required field
            'stops' => []
        ];

        $response = $this->post(route('orders.saveSingleShipper', $this->order->id), $incompleteData);

        // Should fail validation
        $response->assertStatus(302);
        $response->assertSessionHasErrors();
    }

    public function test_unauthorized_user_cannot_access_order()
    {
        // Create another company and user
        $otherCompany = Company::factory()->create();
        $otherUser = User::factory()->create(['company_id' => $otherCompany->id]);

        $this->actingAs($otherUser);

        $response = $this->post(route('orders.saveSingleShipper', $this->order->id), []);

        // Should be forbidden
        $response->assertStatus(403);
    }
}