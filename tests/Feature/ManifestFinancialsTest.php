<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Manifest;
use App\Models\Order;
use App\Models\OrderQuote;
use App\Models\OrderStop;
use App\Models\User;
use App\Services\ManifestService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ManifestFinancialsTest extends TestCase
{
    use DatabaseTransactions;

    private Company $company;

    private User $user;

    private Customer $customer;

    private ManifestService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $suffix = uniqid();

        $this->company = Company::create([
            'name' => 'Fin Co '.$suffix,
            'slug' => 'fin-co-'.$suffix,
            'shortcode' => 'FINC',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $this->user = User::create([
            'company_id' => $this->company->id,
            'name' => 'Fin User',
            'f_name' => 'Fin',
            'l_name' => 'User',
            'email' => "fin.user.{$suffix}@example.com",
            'password' => Hash::make('password'),
            'status' => 'active',
            'is_active' => true,
            'is_deleted' => false,
            'is_super_admin' => true,
        ]);

        $this->customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Fin Customer',
            'customer_email' => "fin.customer.{$suffix}@example.com",
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $this->service = app(ManifestService::class);
    }

    private function makeManifest(): Manifest
    {
        return Manifest::create([
            'company_id' => $this->company->id,
            'code' => 'FINC-M-'.uniqid(),
            'status' => 'pending',
            'start_date' => now()->toDateString(),
        ]);
    }

    private function makeOrder(): Order
    {
        return Order::create([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'FINC-'.uniqid(),
            'order_type' => 'point_to_point',
            'status' => 'quoted',
        ]);
    }

    private function addStops(Order $order, ?Manifest $manifest, int $count): void
    {
        foreach (range(1, $count) as $i) {
            OrderStop::create([
                'order_id' => $order->id,
                'manifest_id' => $manifest?->id,
                'stop_type' => 'pickup',
                'sequence_number' => $i,
                'company_name' => 'Stop '.$i,
            ]);
        }
    }

    private function addCosts(Order $order, float $customer, float $carrier): void
    {
        $quote = OrderQuote::create(['order_id' => $order->id]);

        if ($customer > 0) {
            $quote->costs()->create([
                'category' => 'customer', 'type' => 'Freight',
                'description' => 'Freight', 'qty' => 1, 'rate' => $customer, 'cost' => $customer,
            ]);
        }

        if ($carrier > 0) {
            $quote->costs()->create([
                'category' => 'carrier', 'type' => 'Freight',
                'description' => 'Freight', 'qty' => 1, 'rate' => $carrier, 'cost' => $carrier,
            ]);
        }
    }

    private function addManifestCost(Manifest $manifest, float $cost): void
    {
        $manifest->costEstimates()->create([
            'type' => 'Freight', 'description' => 'Carrier freight',
            'qty' => 1, 'rate' => $cost, 'est_cost' => $cost,
        ]);
    }

    public function test_one_manifest_with_two_orders_splits_its_cost_and_sums_revenue(): void
    {
        $manifest = $this->makeManifest();

        $orderA = $this->makeOrder();
        $this->addStops($orderA, $manifest, 2);
        $this->addCosts($orderA, 1000, 200);

        $orderB = $this->makeOrder();
        $this->addStops($orderB, $manifest, 2);
        $this->addCosts($orderB, 500, 100);

        $this->addManifestCost($manifest, 400);

        $summary = $this->service->financialSummary($manifest);

        // Both orders sit entirely on this manifest, so their full revenue counts
        $this->assertSame(1500.0, $summary['revenue']);
        // Costs are this manifest's own line items; the orders' carrier costs stay on the orders
        $this->assertSame(400.0, $summary['manifest_cost']);
        $this->assertSame(400.0, $summary['costs']);
        $this->assertSame(1100.0, $summary['profit']);

        // The manifest's own $400 is split 50/50 by stop count, never duplicated
        $shares = collect($summary['orders'])->pluck('manifest_cost_share', 'id');
        $this->assertSame(200.0, $shares[$orderA->id]);
        $this->assertSame(200.0, $shares[$orderB->id]);
        $this->assertSame(400.0, collect($summary['orders'])->sum('manifest_cost_share'));
    }

    public function test_order_split_across_two_manifests_counts_only_its_share(): void
    {
        $first = $this->makeManifest();
        $second = $this->makeManifest();

        $order = $this->makeOrder();
        $this->addStops($order, $first, 1);
        $this->addStops($order, $second, 3);
        $this->addCosts($order, 1000, 400);

        $firstSummary = $this->service->financialSummary($first);
        $secondSummary = $this->service->financialSummary($second);

        // 1 of 4 stops here, 3 of 4 there
        $this->assertSame(250.0, $firstSummary['revenue']);
        $this->assertSame(750.0, $secondSummary['revenue']);

        // The two manifests add up to the order, rather than double counting it
        $this->assertSame(1000.0, $firstSummary['revenue'] + $secondSummary['revenue']);

        $row = collect($firstSummary['orders'])->firstWhere('id', $order->id);
        $this->assertTrue($row['is_partial']);
        $this->assertSame(1, $row['stops_on_manifest']);
        $this->assertSame(4, $row['stops_total']);
        $this->assertSame(1000.0, $row['revenue_full']);
    }

    public function test_saving_manifest_costs_leaves_order_costs_untouched(): void
    {
        $manifest = $this->makeManifest();
        $other = $this->makeManifest();

        $order = $this->makeOrder();
        $this->addStops($order, $manifest, 1);
        $this->addStops($order, $other, 1);
        $this->addCosts($order, 1000, 400);

        $this->service->updateManifest($manifest, [
            'cost_estimates' => [
                ['type' => 'Freight', 'description' => 'Freight', 'qty' => 1, 'rate' => 900],
                ['type' => 'Fuel (surcharge)', 'description' => '', 'qty' => 10, 'rate' => 0],
            ],
        ]);

        // The order keeps exactly the carrier cost that was entered on the order
        $carrier = $order->fresh()->quote->costs->where('category', 'carrier');
        $this->assertCount(1, $carrier);
        $this->assertSame(400.0, (float) $carrier->sum('cost'));

        // Manifest freight 900 + 10% fuel 90, and nothing pulled in from the order
        $summary = $this->service->financialSummary($manifest->fresh());
        $this->assertSame(990.0, $summary['manifest_cost']);
        $this->assertSame(990.0, $summary['costs']);
    }

    public function test_manifest_without_orders_and_orders_without_quotes_are_zeroed(): void
    {
        $empty = $this->service->financialSummary($this->makeManifest());

        $this->assertSame([], $empty['orders']);
        $this->assertSame(0.0, $empty['revenue']);
        $this->assertSame(0.0, $empty['costs']);
        $this->assertNull($empty['margin']);

        $manifest = $this->makeManifest();
        $order = $this->makeOrder();
        $this->addStops($order, $manifest, 2);

        $summary = $this->service->financialSummary($manifest);
        $this->assertCount(1, $summary['orders']);
        $this->assertSame(0.0, $summary['revenue']);
        $this->assertSame(0.0, $summary['costs']);
    }

    public function test_financials_tab_renders_shares_for_a_shared_manifest(): void
    {
        $manifest = $this->makeManifest();

        $orderA = $this->makeOrder();
        $this->addStops($orderA, $manifest, 1);
        $this->addCosts($orderA, 1000, 0);

        $orderB = $this->makeOrder();
        $this->addStops($orderB, $manifest, 1);
        $this->addStops($orderB, null, 1); // one stop not on any manifest
        $this->addCosts($orderB, 600, 0);

        $this->actingAs($this->user)
            ->get(route('v2.manifests.edit', ['company' => $this->company->slug, 'manifest' => $manifest->id]))
            ->assertOk()
            ->assertSee('Orders on this manifest')
            ->assertSee('#'.$orderA->order_number)
            ->assertSee('#'.$orderB->order_number)
            ->assertSee('1 of 2')       // order B is only half here
            ->assertSee('$1,300.00');   // 1000 + half of 600
    }
}
