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

    public function test_order_status_can_jump_directly_to_any_driver_workflow_status()
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

        // Skipping straight to delivered is now allowed (no forced sequence).
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/driver/orders/{$order->id}/status", ['status' => 'delivered'])
            ->assertOk()
            ->assertJsonPath('data.status', 'delivered');

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => 'warehousing',
            'to_status' => 'delivered',
            'changed_by_user_id' => $driver->id,
        ]);

        // Moving backward is also allowed.
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/driver/orders/{$order->id}/status", ['status' => 'picked_up'])
            ->assertOk()
            ->assertJsonPath('data.status', 'picked_up');
    }

    public function test_order_status_rejects_pre_dispatch_web_only_values()
    {
        [$company, $driver, $token] = $this->makeDriver();

        $manifest = Manifest::create([
            'company_id' => $company->id,
            'code' => 'M-PREDISPATCH-'.uniqid(),
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

        foreach (['draft', 'new', 'quoted', 'no_quote'] as $status) {
            $this->withHeader('Authorization', "Bearer {$token}")
                ->postJson("/api/driver/orders/{$order->id}/status", ['status' => $status])
                ->assertStatus(422)
                ->assertJsonValidationErrors('status');
        }
    }

    public function test_driver_cannot_change_an_order_that_has_not_been_booked()
    {
        [$company, $driver, $token] = $this->makeDriver();

        $manifest = Manifest::create([
            'company_id' => $company->id,
            'code' => 'M-UNBOOKED-'.uniqid(),
            'status' => 'dispatched',
            'start_date' => '2026-07-27',
            'previous_stop' => 'Chicago, IL',
            'next_stop' => 'Detroit, MI',
        ]);
        ManifestDriver::create(['manifest_id' => $manifest->id, 'driver_id' => $driver->id]);

        foreach (['new', 'quoted'] as $status) {
            $order = Order::create([
                'company_id' => $company->id,
                'manifest_id' => $manifest->id,
                'order_type' => 'point_to_point',
                'status' => $status,
                'order_number' => 'ORD-'.uniqid(),
            ]);

            $this->withHeader('Authorization', "Bearer {$token}")
                ->postJson("/api/driver/orders/{$order->id}/status", ['status' => 'warehousing'])
                ->assertStatus(422)
                ->assertJsonPath('message', "This order hasn't been booked yet.");

            $this->withHeader('Authorization', "Bearer {$token}")
                ->postJson("/api/driver/orders/{$order->id}/cancel", ['reason' => 'Not needed'])
                ->assertStatus(422)
                ->assertJsonPath('message', "This order hasn't been booked yet.");

            $this->assertSame($status, $order->fresh()->status);
        }
    }

    public function test_booked_order_is_handed_to_the_driver_workflow()
    {
        [$company, $driver, $token] = $this->makeDriver();

        $manifest = Manifest::create([
            'company_id' => $company->id,
            'code' => 'M-BOOKED-'.uniqid(),
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
            'status' => 'booked',
            'order_number' => 'ORD-'.uniqid(),
        ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/driver/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.status_label', 'Booked')
            ->assertJsonPath('data.next_status', 'warehousing');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/driver/orders/{$order->id}/status", ['status' => 'warehousing'])
            ->assertOk()
            ->assertJsonPath('data.status', 'warehousing');

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => 'booked',
            'to_status' => 'warehousing',
        ]);
    }

    public function test_cancelled_order_can_be_moved_back_into_the_driver_workflow()
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

        // Under the relaxed rules, cancelled is no longer a hard dead end via /status.
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/driver/orders/{$order->id}/status", ['status' => 'picked_up'])
            ->assertOk()
            ->assertJsonPath('data.status', 'picked_up');
    }

    public function test_manifest_status_endpoint_allows_any_source_to_any_target()
    {
        [$company, $driver, $token] = $this->makeDriver();

        $manifest = Manifest::create([
            'company_id' => $company->id,
            'code' => 'M-STATUS-'.uniqid(),
            'status' => 'pending',
            'start_date' => '2026-07-27',
            'previous_stop' => 'Chicago, IL',
            'next_stop' => 'Detroit, MI',
        ]);
        ManifestDriver::create(['manifest_id' => $manifest->id, 'driver_id' => $driver->id]);

        $order = Order::create([
            'company_id' => $company->id,
            'manifest_id' => $manifest->id,
            'order_type' => 'point_to_point',
            'status' => 'delivered',
            'order_number' => 'ORD-'.uniqid(),
        ]);

        // Jumping straight from pending to in_transit (skipping dispatched) is allowed.
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/driver/manifests/{$manifest->id}/status", ['status' => 'in_transit', 'note' => 'Rolling now'])
            ->assertOk()
            ->assertJsonPath('data.status', 'in_transit');

        // Orders are eager-loaded on this endpoint, unlike start()/complete().
        $this->assertNotEmpty($response->json('data.orders'));

        // The "all orders resolved" guard is still enforced for completed.
        $order->update(['status' => 'in_transit']);
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/driver/manifests/{$manifest->id}/status", ['status' => 'completed'])
            ->assertStatus(422);

        $order->update(['status' => 'delivered']);
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/driver/manifests/{$manifest->id}/status", ['status' => 'completed'])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');
    }

    public function test_manifest_status_rejects_unrecognized_value()
    {
        [$company, $driver, $token] = $this->makeDriver();

        $manifest = Manifest::create([
            'company_id' => $company->id,
            'code' => 'M-BADSTATUS-'.uniqid(),
            'status' => 'pending',
            'start_date' => '2026-07-27',
            'previous_stop' => 'Chicago, IL',
            'next_stop' => 'Detroit, MI',
        ]);
        ManifestDriver::create(['manifest_id' => $manifest->id, 'driver_id' => $driver->id]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/driver/manifests/{$manifest->id}/status", ['status' => 'not_a_real_status'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');
    }
}
