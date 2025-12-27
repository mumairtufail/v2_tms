<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SequenceOrderBasicTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_access_sequence_order_form()
    {
        // Create test user and company
        $company = Company::create([
            'name' => 'Test Company',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TX',
            'zip' => '12345',
            'country' => 'US',
            'phone' => '555-0123',
            'email' => 'test@company.com',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
        ]);

        $this->actingAs($user);

        // Test creating a new order (should redirect to edit page)
        $response = $this->post(route('orders.create'));
        
        $this->assertTrue($response->isRedirection());
        
        // Follow the redirect to get to the edit page
        $order = \App\Models\Order::latest()->first();
        $editResponse = $this->get(route('orders.edit', $order));
        
        $editResponse->assertStatus(200);
        $editResponse->assertSee('sequence');
        
        echo "\n✅ BASIC ACCESS TEST PASSED: Can access sequence order form\n";
        echo "   - Order created with ID: {$order->id}\n";
        echo "   - Edit page loads successfully\n";
    }

    /** @test */
    public function it_validates_sequence_order_submission()
    {
        $company = Company::create([
            'name' => 'Test Company',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TX',
            'zip' => '12345',
            'country' => 'US',
            'phone' => '555-0123',
            'email' => 'test@company.com',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
        ]);

        $this->actingAs($user);

        // Test minimal valid sequence order data
        $validData = [
            'order_type' => 'sequence',
            'ref_number' => 'TEST-001',
            'stops' => [
                [
                    'shipper_company_name' => 'Test Shipper',
                    'shipper_address_1' => '123 Shipper St',
                    'shipper_city' => 'Shipper City',
                    'shipper_state' => 'TX',
                    'shipper_zip' => '12345',
                    'consignee_company_name' => 'Test Consignee',
                    'consignee_address_1' => '456 Consignee St',
                    'consignee_city' => 'Consignee City',
                    'consignee_state' => 'CA',
                    'consignee_zip' => '67890',
                ]
            ]
        ];

        $response = $this->post(route('orders.store'), $validData);
        
        // Should redirect after successful creation
        $this->assertTrue($response->isRedirection());
        
        // Verify order was created
        $order = \App\Models\Order::where('ref_number', 'TEST-001')->first();
        $this->assertNotNull($order);
        $this->assertEquals('sequence', $order->order_type);
        
        echo "\n✅ VALIDATION TEST PASSED: Basic sequence order created successfully\n";
        echo "   - Order created with ref: {$order->ref_number}\n";
        echo "   - Order type: {$order->order_type}\n";
    }
}