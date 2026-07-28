<?php

namespace Tests\Feature\Driver;

use App\Models\Company;
use App\Models\Manifest;
use App\Models\ManifestDriver;
use App\Models\Order;
use App\Models\OrderStop;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DriverManifestViaStopsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_order_attached_only_via_order_stops_manifest_id_shows_up_with_split_pickup_delivery_stops()
    {
        $company = Company::create([
            'name' => 'Stops Test Co '.uniqid(),
            'slug' => 'stops-test-co-'.uniqid(),
            'address' => '1 Test St',
            'is_active' => true,
            'phone' => '555-0000',
            'is_deleted' => false,
        ]);

        $driverRole = Role::create([
            'name' => 'driver',
            'company_id' => $company->id,
            'is_active' => true,
        ]);

        $driver = User::create([
            'f_name' => 'Dana',
            'l_name' => 'Driver',
            'email' => 'dana.stops.'.uniqid().'@example.com',
            'password' => bcrypt('password123'),
            'company_id' => $company->id,
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $driver->roles()->attach($driverRole->id);
        $token = $driver->createToken('driver-app')->plainTextToken;

        $manifest = Manifest::create([
            'company_id' => $company->id,
            'code' => 'UMS-M-0043',
            'status' => 'dispatched',
            'start_date' => '2026-07-27',
        ]);
        ManifestDriver::create(['manifest_id' => $manifest->id, 'driver_id' => $driver->id]);

        // Order attached the way the live portal save flow actually does it:
        // manifest_id lives on the STOP, orders.manifest_id is never touched.
        $order = Order::create([
            'company_id' => $company->id,
            'order_type' => 'point_to_point',
            'status' => 'warehousing',
            'order_number' => 'ORD-'.uniqid(),
            'ref_number' => 'PO-9981',
        ]);

        OrderStop::create([
            'order_id' => $order->id,
            'manifest_id' => $manifest->id,
            'stop_type' => 'mixed',
            'sequence_number' => 1,
            'company_name' => 'Acme Warehouse',
            'address_1' => '100 Industrial Pkwy',
            'city' => 'Chicago',
            'state' => 'IL',
            'postal_code' => '60601',
            'contact_name' => 'Jim Reyes',
            'contact_phone' => '555-0199',
            'start_time' => '2026-07-27 08:00:00',
            'end_time' => '2026-07-27 10:00:00',
            'consignee_data' => [
                'company_name' => 'Acme Retail Store',
                'address_1' => '200 Retail Rd',
                'city' => 'Detroit',
                'state' => 'MI',
                'zip' => '48201',
                'contact_name' => 'Sam Buyer',
                'phone' => '555-0222',
                'requested_start_at' => '2026-07-27 14:00:00',
                'requested_end_at' => '2026-07-27 16:00:00',
            ],
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/driver/manifests/{$manifest->id}");

        $response->assertOk();
        $response->assertJsonPath('data.orders_count', 1);
        $response->assertJsonPath('data.orders.0.id', $order->id);
        $response->assertJsonPath('data.orders.0.order_type', 'point_to_point');

        $stops = $response->json('data.orders.0.stops');
        $this->assertCount(2, $stops);
        $this->assertSame('pickup', $stops[0]['type']);
        $this->assertSame('Acme Warehouse', $stops[0]['company_name']);
        $this->assertSame('delivery', $stops[1]['type']);
        $this->assertSame('Acme Retail Store', $stops[1]['company_name']);
        $this->assertSame('200 Retail Rd, Detroit, MI, 48201', $stops[1]['address']);

        // Individual order endpoint must also resolve via the stops-based attachment.
        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/driver/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.manifest_id', $manifest->id);
    }
}
