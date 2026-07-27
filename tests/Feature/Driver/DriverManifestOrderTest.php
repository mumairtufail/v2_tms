<?php

namespace Tests\Feature\Driver;

use App\Models\Company;
use App\Models\Manifest;
use App\Models\ManifestDriver;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DriverManifestOrderTest extends TestCase
{
    use DatabaseTransactions;

    private function makeDriver(): array
    {
        $company = Company::create([
            'name' => 'Manifest Test Co '.uniqid(),
            'slug' => 'manifest-test-co-'.uniqid(),
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
            'email' => 'dana.manifest.'.uniqid().'@example.com',
            'password' => bcrypt('password123'),
            'company_id' => $company->id,
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $driver->roles()->attach($driverRole->id);

        $token = $driver->createToken('driver-app')->plainTextToken;

        return [$company, $driver, $token];
    }

    public function test_driver_only_sees_manifests_assigned_to_them()
    {
        [$company, $driver, $token] = $this->makeDriver();

        $ownManifest = Manifest::create([
            'company_id' => $company->id,
            'code' => 'M-OWN-'.uniqid(),
            'status' => 'dispatched',
            'start_date' => '2026-07-27',
            'previous_stop' => 'Chicago, IL',
            'next_stop' => 'Detroit, MI',
        ]);
        ManifestDriver::create(['manifest_id' => $ownManifest->id, 'driver_id' => $driver->id]);

        $otherManifest = Manifest::create([
            'company_id' => $company->id,
            'code' => 'M-OTHER-'.uniqid(),
            'status' => 'dispatched',
            'start_date' => '2026-07-27',
            'previous_stop' => 'Denver, CO',
            'next_stop' => 'Salt Lake City, UT',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/driver/manifests');

        $response->assertOk();
        $codes = collect($response->json('data'))->pluck('code');
        $this->assertTrue($codes->contains($ownManifest->code));
        $this->assertFalse($codes->contains($otherManifest->code));

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/driver/manifests/{$otherManifest->id}")
            ->assertStatus(404);
    }

    public function test_manifest_can_be_started_then_completed_once_orders_are_resolved()
    {
        [$company, $driver, $token] = $this->makeDriver();

        $manifest = Manifest::create([
            'company_id' => $company->id,
            'code' => 'M-FLOW-'.uniqid(),
            'status' => 'dispatched',
            'start_date' => '2026-07-27',
            'previous_stop' => 'Chicago, IL',
            'next_stop' => 'Detroit, MI',
        ]);
        ManifestDriver::create(['manifest_id' => $manifest->id, 'driver_id' => $driver->id]);

        $order = Order::create([
            'company_id' => $company->id,
            'manifest_id' => $manifest->id,
            'order_type' => 'point_to_point',
            'status' => 'warehousing',
            'order_number' => 'ORD-'.uniqid(),
        ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/driver/manifests/{$manifest->id}/start")
            ->assertOk()
            ->assertJsonPath('data.status', 'in_transit');

        // Can't complete while the order is still open.
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/driver/manifests/{$manifest->id}/complete")
            ->assertStatus(422);

        $order->update(['status' => 'delivered']);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/driver/manifests/{$manifest->id}/complete")
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');
    }

    public function test_order_status_only_advances_one_legal_step_at_a_time()
    {
        [$company, $driver, $token] = $this->makeDriver();

        $manifest = Manifest::create([
            'company_id' => $company->id,
            'code' => 'M-ORDER-'.uniqid(),
            'status' => 'dispatched',
            'start_date' => '2026-07-27',
            'previous_stop' => 'Chicago, IL',
            'next_stop' => 'Detroit, MI',
        ]);
        ManifestDriver::create(['manifest_id' => $manifest->id, 'driver_id' => $driver->id]);

        $order = Order::create([
            'company_id' => $company->id,
            'manifest_id' => $manifest->id,
            'order_type' => 'point_to_point',
            'status' => 'warehousing',
            'order_number' => 'ORD-'.uniqid(),
        ]);

        // Illegal jump: warehousing -> delivered directly.
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/driver/orders/{$order->id}/status", ['status' => 'delivered'])
            ->assertStatus(422);

        // Legal step: warehousing -> picked_up.
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/driver/orders/{$order->id}/status", ['status' => 'picked_up'])
            ->assertOk()
            ->assertJsonPath('data.status', 'picked_up');

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => 'warehousing',
            'to_status' => 'picked_up',
            'changed_by_user_id' => $driver->id,
        ]);
    }

    public function test_cancelled_order_can_no_longer_change_status()
    {
        [$company, $driver, $token] = $this->makeDriver();

        $manifest = Manifest::create([
            'company_id' => $company->id,
            'code' => 'M-CANCEL-'.uniqid(),
            'status' => 'dispatched',
            'start_date' => '2026-07-27',
            'previous_stop' => 'Chicago, IL',
            'next_stop' => 'Detroit, MI',
        ]);
        ManifestDriver::create(['manifest_id' => $manifest->id, 'driver_id' => $driver->id]);

        $order = Order::create([
            'company_id' => $company->id,
            'manifest_id' => $manifest->id,
            'order_type' => 'point_to_point',
            'status' => 'warehousing',
            'order_number' => 'ORD-'.uniqid(),
        ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/driver/orders/{$order->id}/cancel", ['reason' => 'Customer refused delivery'])
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/driver/orders/{$order->id}/status", ['status' => 'picked_up'])
            ->assertStatus(422);
    }
}
