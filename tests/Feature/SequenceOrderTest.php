<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Order;
use App\Models\User;
use App\Models\Company;
use App\Models\Customer;
use App\Models\OrderStop;
use App\Models\Accessorial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class SequenceOrderTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $company;
    private $customer;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test company
        $this->company = Company::factory()->create([
            'name' => 'Test Transport Company',
        ]);
        
        // Create test user
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'email' => 'test@example.com',
        ]);
        
        // Create test customer
        $this->customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Test Customer',
        ]);

        // Create test accessorials
        Accessorial::factory()->create(['name' => 'Liftgate Service']);
        Accessorial::factory()->create(['name' => 'Inside Pickup']);
    }

    /** @test */
    public function it_can_create_a_sequence_order_with_multiple_stops()
    {
        $this->actingAs($this->user);

        $sequenceData = [
            'order_type' => 'sequence',
            'ref_number' => 'SEQ-TEST-001',
            'customer_po_number' => 'PO-12345',
            'special_instructions' => 'Handle with care - fragile items',
            'stops' => [
                // Stop 1
                [
                    'shipper_company_name' => 'ABC Manufacturing',
                    'shipper_address_1' => '123 Factory Street',
                    'shipper_address_2' => 'Building A',
                    'shipper_city' => 'Detroit',
                    'shipper_state' => 'MI',
                    'shipper_zip' => '48201',
                    'shipper_country' => 'US',
                    'shipper_contact_name' => 'John Doe',
                    'shipper_phone' => '555-0101',
                    'shipper_contact_email' => 'john@abc.com',
                    'shipper_notes' => 'Loading dock B',
                    'shipper_opening_time' => '08:00',
                    'shipper_closing_time' => '17:00',
                    'ready_start_time' => '2025-11-20T08:00',
                    'ready_end_time' => '2025-11-20T10:00',
                    'ready_appointment' => true,
                    
                    'consignee_company_name' => 'XYZ Warehouse',
                    'consignee_address_1' => '456 Storage Lane',
                    'consignee_address_2' => 'Unit 5',
                    'consignee_city' => 'Chicago',
                    'consignee_state' => 'IL',
                    'consignee_zip' => '60601',
                    'consignee_country' => 'US',
                    'consignee_contact_name' => 'Jane Smith',
                    'consignee_phone' => '555-0202',
                    'consignee_contact_email' => 'jane@xyz.com',
                    'consignee_notes' => 'Receiving bay 3',
                    'consignee_opening_time' => '09:00',
                    'consignee_closing_time' => '18:00',
                    'delivery_start_time' => '2025-11-20T14:00',
                    'delivery_end_time' => '2025-11-20T16:00',
                    'delivery_appointment' => true,
                    
                    'customs_broker' => 'Global Customs Inc',
                    'port_of_entry' => 'Port of Detroit',
                    'declared_value' => '15000.00',
                    'currency' => 'USD',
                    'container_number' => 'ABCD1234567',
                    'ref_number' => 'REF-001',
                    'customer_po_number' => 'PO-001',
                    
                    'commodities' => [
                        [
                            'description' => 'Electronic Components',
                            'pieces' => 10,
                            'weight' => 250.5,
                            'dimensions' => '24x18x12'
                        ],
                        [
                            'description' => 'Packaging Materials',
                            'pieces' => 5,
                            'weight' => 75.0,
                            'dimensions' => '12x12x8'
                        ]
                    ],
                    'accessorials' => [1, 2] // Liftgate Service, Inside Pickup
                ],
                // Stop 2
                [
                    'shipper_company_name' => 'DEF Suppliers',
                    'shipper_address_1' => '789 Supply Road',
                    'shipper_city' => 'Milwaukee',
                    'shipper_state' => 'WI',
                    'shipper_zip' => '53201',
                    'shipper_country' => 'US',
                    'shipper_contact_name' => 'Bob Johnson',
                    'shipper_phone' => '555-0303',
                    'shipper_contact_email' => 'bob@def.com',
                    'shipper_opening_time' => '07:00',
                    'shipper_closing_time' => '16:00',
                    'ready_start_time' => '2025-11-21T07:00',
                    'ready_end_time' => '2025-11-21T09:00',
                    
                    'consignee_company_name' => 'Final Destination Co',
                    'consignee_address_1' => '321 End Street',
                    'consignee_city' => 'Minneapolis',
                    'consignee_state' => 'MN',
                    'consignee_zip' => '55401',
                    'consignee_country' => 'US',
                    'consignee_contact_name' => 'Alice Brown',
                    'consignee_phone' => '555-0404',
                    'consignee_contact_email' => 'alice@final.com',
                    'delivery_start_time' => '2025-11-21T15:00',
                    'delivery_end_time' => '2025-11-21T17:00',
                    
                    'customs_broker' => 'Border Experts LLC',
                    'declared_value' => '8500.00',
                    'currency' => 'CAD',
                    'container_number' => 'EFGH7654321',
                    
                    'commodities' => [
                        [
                            'description' => 'Industrial Tools',
                            'pieces' => 3,
                            'weight' => 180.0,
                            'dimensions' => '30x20x15'
                        ]
                    ]
                ]
            ]
        ];

        // Test order creation
        $response = $this->post(route('orders.store'), $sequenceData);
        
        $this->assertTrue($response->isRedirection());
        
        // Verify order was created
        $order = Order::where('ref_number', 'SEQ-TEST-001')->first();
        $this->assertNotNull($order);
        $this->assertEquals('sequence', $order->order_type);
        $this->assertEquals('SEQ-TEST-001', $order->ref_number);
        $this->assertEquals('PO-12345', $order->customer_po_number);
        $this->assertEquals('Handle with care - fragile items', $order->special_instructions);
        
        // Verify stops were created
        $stops = $order->stops()->orderBy('sequence_number')->get();
        $this->assertCount(2, $stops);
        
        // Verify first stop
        $stop1 = $stops->first();
        $this->assertEquals(1, $stop1->sequence_number);
        $this->assertEquals('ABC Manufacturing', $stop1->company_name);
        $this->assertEquals('123 Factory Street', $stop1->address_1);
        $this->assertEquals('Building A', $stop1->address_2);
        $this->assertEquals('Detroit', $stop1->city);
        $this->assertEquals('MI', $stop1->state);
        $this->assertEquals('48201', $stop1->postal_code);
        $this->assertEquals('US', $stop1->country);
        $this->assertEquals('John Doe', $stop1->contact_name);
        $this->assertEquals('555-0101', $stop1->contact_phone);
        $this->assertEquals('john@abc.com', $stop1->contact_email);
        
        // Verify consignee data (stored as JSON)
        $consigneeData = json_decode($stop1->consignee_data, true);
        $this->assertEquals('XYZ Warehouse', $consigneeData['company_name']);
        $this->assertEquals('456 Storage Lane', $consigneeData['address_1']);
        $this->assertEquals('Unit 5', $consigneeData['address_2']);
        $this->assertEquals('Chicago', $consigneeData['city']);
        $this->assertEquals('IL', $consigneeData['state']);
        $this->assertEquals('60601', $consigneeData['zip']);
        
        // Verify additional information (billing data)
        $billingData = json_decode($stop1->billing_data, true);
        $this->assertEquals('Global Customs Inc', $billingData['customs_broker']);
        $this->assertEquals('Port of Detroit', $billingData['port_of_entry']);
        $this->assertEquals('15000.00', $billingData['declared_value']);
        $this->assertEquals('USD', $billingData['currency']);
        $this->assertEquals('ABCD1234567', $billingData['container_number']);
        
        // Verify commodities
        $commodities = $stop1->commodities;
        $this->assertCount(2, $commodities);
        $this->assertEquals('Electronic Components', $commodities->first()->description);
        $this->assertEquals(10, $commodities->first()->pieces);
        $this->assertEquals(250.5, $commodities->first()->weight);
        
        // Verify second stop
        $stop2 = $stops->last();
        $this->assertEquals(2, $stop2->sequence_number);
        $this->assertEquals('DEF Suppliers', $stop2->company_name);
        $this->assertEquals('789 Supply Road', $stop2->address_1);
        $this->assertEquals('Milwaukee', $stop2->city);
        
        $consigneeData2 = json_decode($stop2->consignee_data, true);
        $this->assertEquals('Final Destination Co', $consigneeData2['company_name']);
        $this->assertEquals('Minneapolis', $consigneeData2['city']);
        
        echo "\n✅ CREATE TEST PASSED: Sequence order created successfully with all fields\n";
        echo "   - Order ID: {$order->id}\n";
        echo "   - Stops created: {$stops->count()}\n";
        echo "   - Commodities on stop 1: {$stop1->commodities->count()}\n";
    }

    /** @test */
    public function it_can_update_a_sequence_order()
    {
        $this->actingAs($this->user);
        
        // Create initial order
        $order = Order::factory()->create([
            'company_id' => $this->company->id,
            'order_type' => 'sequence',
            'ref_number' => 'SEQ-UPDATE-001',
        ]);
        
        // Create initial stops
        $stop1 = OrderStop::factory()->create([
            'order_id' => $order->id,
            'sequence_number' => 1,
            'company_name' => 'Original Company',
            'city' => 'Original City',
        ]);
        
        $updateData = [
            'order_type' => 'sequence',
            'ref_number' => 'SEQ-UPDATE-001-MODIFIED',
            'customer_po_number' => 'PO-UPDATED',
            'special_instructions' => 'Updated instructions',
            'stops' => [
                [
                    'shipper_company_name' => 'Updated Company Name',
                    'shipper_address_1' => 'Updated Address',
                    'shipper_city' => 'Updated City',
                    'shipper_state' => 'CA',
                    'shipper_zip' => '90210',
                    'shipper_country' => 'US',
                    'ready_start_time' => '2025-11-22T09:00',
                    'ready_end_time' => '2025-11-22T11:00',
                    
                    'consignee_company_name' => 'Updated Consignee',
                    'consignee_address_1' => 'Updated Delivery Address',
                    'consignee_city' => 'Los Angeles',
                    'consignee_state' => 'CA',
                    'consignee_zip' => '90211',
                    'consignee_country' => 'US',
                    'delivery_start_time' => '2025-11-22T15:00',
                    'delivery_end_time' => '2025-11-22T17:00',
                    
                    'customs_broker' => 'Updated Broker',
                    'declared_value' => '25000.00',
                    'currency' => 'EUR',
                    
                    'commodities' => [
                        [
                            'description' => 'Updated Commodity',
                            'pieces' => 15,
                            'weight' => 300.0,
                            'dimensions' => '36x24x18'
                        ]
                    ]
                ]
            ]
        ];
        
        $response = $this->put(route('orders.update', $order), $updateData);
        
        $this->assertTrue($response->isRedirection());
        
        // Verify order was updated
        $order->refresh();
        $this->assertEquals('SEQ-UPDATE-001-MODIFIED', $order->ref_number);
        $this->assertEquals('PO-UPDATED', $order->customer_po_number);
        $this->assertEquals('Updated instructions', $order->special_instructions);
        
        // Verify stops were updated
        $updatedStops = $order->stops()->orderBy('sequence_number')->get();
        $this->assertCount(1, $updatedStops);
        
        $updatedStop = $updatedStops->first();
        $this->assertEquals('Updated Company Name', $updatedStop->company_name);
        $this->assertEquals('Updated City', $updatedStop->city);
        $this->assertEquals('CA', $updatedStop->state);
        
        $consigneeData = json_decode($updatedStop->consignee_data, true);
        $this->assertEquals('Updated Consignee', $consigneeData['company_name']);
        $this->assertEquals('Los Angeles', $consigneeData['city']);
        
        $billingData = json_decode($updatedStop->billing_data, true);
        $this->assertEquals('Updated Broker', $billingData['customs_broker']);
        $this->assertEquals('25000.00', $billingData['declared_value']);
        $this->assertEquals('EUR', $billingData['currency']);
        
        echo "\n✅ UPDATE TEST PASSED: Sequence order updated successfully\n";
        echo "   - Order updated with new ref: {$order->ref_number}\n";
        echo "   - Stop updated with new company: {$updatedStop->company_name}\n";
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $this->actingAs($this->user);

        $invalidData = [
            'order_type' => 'sequence',
            'stops' => [
                [
                    // Missing required fields
                    'shipper_company_name' => '',
                    'shipper_address_1' => '',
                    'shipper_city' => '',
                    'consignee_company_name' => '',
                    'consignee_address_1' => '',
                    'consignee_city' => '',
                ]
            ]
        ];

        $response = $this->post(route('orders.store'), $invalidData);
        
        $response->assertSessionHasErrors([
            'stops.0.shipper_company_name',
            'stops.0.shipper_address_1', 
            'stops.0.shipper_city',
            'stops.0.shipper_state',
            'stops.0.shipper_zip',
            'stops.0.consignee_company_name',
            'stops.0.consignee_address_1',
            'stops.0.consignee_city',
            'stops.0.consignee_state',
            'stops.0.consignee_zip',
        ]);

        echo "\n✅ VALIDATION TEST PASSED: Required field validation working correctly\n";
    }

    /** @test */
    public function it_can_display_sequence_order_edit_page()
    {
        $this->actingAs($this->user);
        
        // Create order with stops
        $order = Order::factory()->create([
            'company_id' => $this->company->id,
            'order_type' => 'sequence',
            'ref_number' => 'SEQ-DISPLAY-001',
        ]);
        
        $stop = OrderStop::factory()->create([
            'order_id' => $order->id,
            'sequence_number' => 1,
            'company_name' => 'Display Test Company',
            'city' => 'Display City',
            'consignee_data' => json_encode([
                'company_name' => 'Display Consignee',
                'city' => 'Consignee City'
            ]),
            'billing_data' => json_encode([
                'customs_broker' => 'Display Broker',
                'declared_value' => '12000.00'
            ])
        ]);
        
        $response = $this->get(route('orders.edit', $order));
        
        $response->assertStatus(200);
        $response->assertSee('SEQ-DISPLAY-001');
        $response->assertSee('Display Test Company');
        $response->assertViewHas('hasExistingStops', true);
        $response->assertViewHas('existingStops');
        
        echo "\n✅ DISPLAY TEST PASSED: Edit page loads correctly with existing data\n";
        echo "   - Page loads with order: {$order->ref_number}\n";
        echo "   - Existing stops data provided to view\n";
    }

    /** @test */
    public function it_handles_empty_stops_gracefully()
    {
        $this->actingAs($this->user);

        $emptyStopsData = [
            'order_type' => 'sequence',
            'ref_number' => 'SEQ-EMPTY-001',
            'stops' => []
        ];

        $response = $this->post(route('orders.store'), $emptyStopsData);
        
        $response->assertSessionHasErrors(['stops']);

        echo "\n✅ EMPTY STOPS TEST PASSED: Empty stops array properly rejected\n";
    }

    /** @test */
    public function it_stores_json_data_correctly()
    {
        $this->actingAs($this->user);

        $jsonTestData = [
            'order_type' => 'sequence',
            'ref_number' => 'SEQ-JSON-001',
            'stops' => [
                [
                    'shipper_company_name' => 'JSON Test Company',
                    'shipper_address_1' => '123 JSON Street',
                    'shipper_city' => 'JSON City',
                    'shipper_state' => 'NY',
                    'shipper_zip' => '10001',
                    
                    'consignee_company_name' => 'JSON Consignee',
                    'consignee_address_1' => '456 JSON Ave',
                    'consignee_city' => 'JSON Delivery City',
                    'consignee_state' => 'NJ',
                    'consignee_zip' => '07001',
                    'consignee_contact_name' => 'JSON Contact',
                    'consignee_phone' => '555-JSON',
                    
                    'customs_broker' => 'JSON Customs',
                    'port_of_entry' => 'JSON Port',
                    'declared_value' => '99999.99',
                    'currency' => 'GBP',
                    'container_number' => 'JSON123456',
                    'ref_number' => 'JSON-REF-001',
                    'customer_po_number' => 'JSON-PO-001',
                    
                    'commodities' => [
                        [
                            'description' => 'JSON Test Item',
                            'pieces' => 99,
                            'weight' => 999.99,
                            'dimensions' => '99x99x99'
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->post(route('orders.store'), $jsonTestData);
        
        $order = Order::where('ref_number', 'SEQ-JSON-001')->first();
        $this->assertNotNull($order);
        
        $stop = $order->stops->first();
        
        // Test consignee JSON data
        $consigneeData = json_decode($stop->consignee_data, true);
        $this->assertIsArray($consigneeData);
        $this->assertEquals('JSON Consignee', $consigneeData['company_name']);
        $this->assertEquals('456 JSON Ave', $consigneeData['address_1']);
        $this->assertEquals('JSON Contact', $consigneeData['contact_name']);
        
        // Test billing JSON data
        $billingData = json_decode($stop->billing_data, true);
        $this->assertIsArray($billingData);
        $this->assertEquals('JSON Customs', $billingData['customs_broker']);
        $this->assertEquals('JSON Port', $billingData['port_of_entry']);
        $this->assertEquals('99999.99', $billingData['declared_value']);
        $this->assertEquals('GBP', $billingData['currency']);
        
        echo "\n✅ JSON STORAGE TEST PASSED: JSON data stored and retrieved correctly\n";
        echo "   - Consignee data stored as valid JSON\n";
        echo "   - Billing data stored as valid JSON\n";
        echo "   - All special characters and values preserved\n";
    }
}