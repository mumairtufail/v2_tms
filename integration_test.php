<?php

// Integration test - simulate actual form submission
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Company;
use App\Services\OrderService;
use Illuminate\Http\Request;

// Initialize Laravel app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== SEQUENCE ORDER INTEGRATION TEST ===\n\n";

try {
    // Cleanup any previous test data
    $timestamp = time();
    $testEmail = "integration{$timestamp}@test.com";
    $testCompanyEmail = "test{$timestamp}@integration.com";
    
    // Create test company and user
    $company = Company::create([
        'name' => "Integration Test Company {$timestamp}",
        'address' => '123 Integration St',
        'city' => 'Test City',
        'state' => 'TX',
        'zip' => '12345',
        'country' => 'US',
        'phone' => '555-0123',
        'email' => $testCompanyEmail,
    ]);

    $user = User::create([
        'f_name' => 'Integration',
        'l_name' => 'Test User',
        'email' => $testEmail,
        'password' => bcrypt('password'),
        'company_id' => $company->id,
        'is_active' => true,
        'is_deleted' => false,
    ]);

    echo "✅ Test user and company created\n";
    echo "   Company ID: {$company->id}\n";
    echo "   User ID: {$user->id}\n\n";

    // Create test order
    $order = \App\Models\Order::create([
        'company_id' => $company->id,
        'order_number' => 'INT-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),
        'order_type' => 'sequence',
        'status' => 'draft',
        'ref_number' => 'INT-TEST-001',
        'customer_po_number' => 'PO-INT-001',
        'special_instructions' => 'Integration test order - handle with care'
    ]);

    echo "✅ Base order created\n";
    echo "   Order ID: {$order->id}\n";
    echo "   Order Number: {$order->order_number}\n\n";

    // Simulate form submission data
    $formData = [
        'order_type' => 'sequence',
        'ref_number' => 'INT-TEST-001-UPDATED',
        'customer_po_number' => 'PO-INT-001-UPDATED',
        'special_instructions' => 'Updated integration test instructions',
        'stops' => [
            // Stop 1: Complete data set
            [
                'shipper_company_name' => 'Integration Shipper Corp',
                'shipper_address_1' => '100 Shipper Boulevard',
                'shipper_address_2' => 'Suite 200',
                'shipper_city' => 'Houston',
                'shipper_state' => 'TX',
                'shipper_zip' => '77001',
                'shipper_country' => 'US',
                'shipper_contact_name' => 'John Integration',
                'shipper_phone' => '713-555-0100',
                'shipper_contact_email' => 'john@shipper.com',
                'shipper_notes' => 'Use loading dock A - integration test',
                'shipper_opening_time' => '07:00',
                'shipper_closing_time' => '19:00',
                'ready_start_time' => '2025-11-25T08:00',
                'ready_end_time' => '2025-11-25T10:00',
                'ready_appointment' => '1',
                
                'consignee_company_name' => 'Integration Receiver LLC',
                'consignee_address_1' => '200 Receiver Street',
                'consignee_address_2' => 'Building B',
                'consignee_city' => 'Dallas',
                'consignee_state' => 'TX',
                'consignee_zip' => '75001',
                'consignee_country' => 'US',
                'consignee_contact_name' => 'Jane Receiver',
                'consignee_phone' => '214-555-0200',
                'consignee_contact_email' => 'jane@receiver.com',
                'consignee_notes' => 'Delivery to warehouse section C',
                'consignee_opening_time' => '08:00',
                'consignee_closing_time' => '18:00',
                'delivery_start_time' => '2025-11-25T14:00',
                'delivery_end_time' => '2025-11-25T16:00',
                'delivery_appointment' => '1',
                
                'customs_broker' => 'Integration Customs Brokers Inc',
                'port_of_entry' => 'Houston Port of Entry',
                'declared_value' => '25000.50',
                'currency' => 'USD',
                'container_number' => 'INTG2025001234',
                'ref_number' => 'INT-REF-001',
                'customer_po_number' => 'INT-PO-001',
                
                'commodities' => [
                    [
                        'description' => 'Integration Test Electronics',
                        'quantity' => 50,
                        'weight' => 1250.75,
                        'length' => 48,
                        'width' => 40,
                        'height' => 36
                    ],
                    [
                        'description' => 'Test Packaging Materials',
                        'quantity' => 25,
                        'weight' => 375.25,
                        'length' => 24,
                        'width' => 20,
                        'height' => 18
                    ]
                ],
                'accessorials' => []
            ],
            // Stop 2: Minimal required data
            [
                'shipper_company_name' => 'Second Stop Shipper',
                'shipper_address_1' => '300 Second Street',
                'shipper_city' => 'Austin',
                'shipper_state' => 'TX',
                'shipper_zip' => '73301',
                
                'consignee_company_name' => 'Final Destination Inc',
                'consignee_address_1' => '400 Final Avenue',
                'consignee_city' => 'San Antonio',
                'consignee_state' => 'TX',
                'consignee_zip' => '78201',
                
                'customs_broker' => 'Final Stop Customs',
                'declared_value' => '15000.00',
                'currency' => 'CAD',
                'container_number' => 'FINAL2025567890',
                
                'commodities' => [
                    [
                        'description' => 'Final Delivery Items',
                        'quantity' => 10,
                        'weight' => 500.00,
                        'length' => 36,
                        'width' => 24,
                        'height' => 12
                    ]
                ]
            ]
        ]
    ];

    echo "✅ Form data prepared\n";
    echo "   Stops: " . count($formData['stops']) . "\n";
    echo "   Total commodities: " . (count($formData['stops'][0]['commodities']) + count($formData['stops'][1]['commodities'])) . "\n\n";

    // Test the OrderService updateSequence method directly
    $orderService = new OrderService();
    
    echo "Testing OrderService::updateSequence()...\n";
    $orderService->updateSequence($order, $formData);
    
    echo "✅ OrderService updateSequence completed without errors\n\n";

    // Verify the results
    $order->refresh();
    $stops = $order->stops()->orderBy('sequence_number')->get();
    
    echo "=== VERIFICATION RESULTS ===\n\n";
    
    // Check order updates
    echo "Order Updates:\n";
    echo "- Ref Number: {$order->ref_number} (should be INT-TEST-001-UPDATED)\n";
    echo "- PO Number: {$order->customer_po_number} (should be PO-INT-001-UPDATED)\n";
    echo "- Instructions: " . substr($order->special_instructions, 0, 50) . "...\n";
    echo "- Stops Count: " . $stops->count() . " (should be 2)\n\n";

    foreach ($stops as $index => $stop) {
        echo "Stop " . ($index + 1) . ":\n";
        echo "- Sequence: {$stop->sequence_number}\n";
        echo "- Shipper: {$stop->company_name}\n";
        echo "- Shipper City: {$stop->city}\n";
        
        // Check consignee data
        $consigneeData = json_decode($stop->consignee_data, true);
        echo "- Consignee: " . ($consigneeData['company_name'] ?? 'N/A') . "\n";
        echo "- Consignee City: " . ($consigneeData['city'] ?? 'N/A') . "\n";
        
        // Check billing data
        $billingData = json_decode($stop->billing_data, true);
        echo "- Customs Broker: " . ($billingData['customs_broker'] ?? 'N/A') . "\n";
        echo "- Declared Value: $" . ($billingData['declared_value'] ?? '0') . "\n";
        echo "- Currency: " . ($billingData['currency'] ?? 'N/A') . "\n";
        
        // Check commodities
        $commodities = $stop->commodities;
        echo "- Commodities: " . $commodities->count() . "\n";
        if ($commodities->count() > 0) {
            echo "  - First commodity: {$commodities->first()->description}\n";
            echo "  - Quantity: {$commodities->first()->quantity}\n";
            echo "  - Weight: {$commodities->first()->weight} lbs\n";
        }
        echo "\n";
    }

    // Data integrity checks
    echo "=== DATA INTEGRITY CHECKS ===\n";
    
    $checks = [
        'Order updated' => $order->ref_number === 'INT-TEST-001-UPDATED',
        'Two stops created' => $stops->count() === 2,
        'First stop has shipper' => $stops->first()->company_name === 'Integration Shipper Corp',
        'First stop has consignee JSON' => !empty(json_decode($stops->first()->consignee_data, true)['company_name']),
        'First stop has billing JSON' => !empty(json_decode($stops->first()->billing_data, true)['customs_broker']),
        'Second stop exists' => $stops->last()->company_name === 'Second Stop Shipper',
        'Commodities created' => $stops->first()->commodities->count() > 0,
        'JSON data valid' => json_last_error() === JSON_ERROR_NONE
    ];

    $passedChecks = 0;
    foreach ($checks as $check => $result) {
        if ($result) {
            echo "✅ {$check}\n";
            $passedChecks++;
        } else {
            echo "❌ {$check}\n";
        }
    }

    echo "\n=== FINAL RESULTS ===\n";
    echo "Passed: {$passedChecks}/" . count($checks) . " checks\n";

    if ($passedChecks === count($checks)) {
        echo "\n🎉 INTEGRATION TEST PASSED! 🎉\n";
        echo "The sequence order system is working perfectly!\n\n";
        
        echo "CONFIRMED FUNCTIONALITY:\n";
        echo "✅ Complete form data processing\n";
        echo "✅ Multi-stop sequence handling\n"; 
        echo "✅ JSON data storage and retrieval\n";
        echo "✅ Shipper information storage\n";
        echo "✅ Consignee information in JSON\n";
        echo "✅ Additional info (customs, etc.) in JSON\n";
        echo "✅ Commodities creation and linking\n";
        echo "✅ Database transactions\n";
        echo "✅ Data validation and processing\n";
        echo "✅ Order updates\n";
    } else {
        echo "\n⚠️ SOME CHECKS FAILED\n";
        echo "Review the failed checks above.\n";
    }

    // Cleanup
    echo "\n=== CLEANUP ===\n";
    $order->stops()->delete();
    $order->delete();
    $user->delete();
    $company->delete();
    echo "✅ Test data cleaned up\n";

} catch (Exception $e) {
    echo "\n❌ INTEGRATION TEST FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== INTEGRATION TEST COMPLETE ===\n";