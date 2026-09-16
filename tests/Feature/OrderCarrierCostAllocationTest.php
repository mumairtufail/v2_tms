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
use App\Services\OrderFormDataBuilder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Carrier cost is stored on the manifest, never on the order. An order may edit it only
 * when every stop sits on the same manifest; otherwise it reads back its allocated share.
 */
class OrderCarrierCostAllocationTest extends TestCase
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
            'name' => 'Alloc Co '.$suffix,
            'slug' => 'alloc-co-'.$suffix,
            'shortcode' => 'ALOC',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $this->user = User::create([
            'company_id' => $this->company->id,
            'name' => 'Alloc User',
            'f_name' => 'Alloc',
            'l_name' => 'User',
            'email' => "alloc.user.{$suffix}@example.com",
            'password' => Hash::make('password'),
            'status' => 'active',
            'is_active' => true,
            'is_deleted' => false,
            'is_super_admin' => true,
        ]);

        $this->customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Alloc Customer',
            'short_code' => 'ALC1',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $this->service = app(ManifestService::class);
    }

    private function makeManifest(float $cost = 0): Manifest
    {
        $manifest = Manifest::create([
            'company_id' => $this->company->id,
            'code' => 'ALOC-M-'.uniqid(),
            'status' => 'pending',
            'start_date' => now()->toDateString(),
        ]);

        if ($cost > 0) {
            $manifest->costEstimates()->create([
                'type' => 'Freight', 'description' => 'Carrier freight',
                'qty' => 1, 'rate' => $cost, 'est_cost' => $cost,
            ]);
        }

        return $manifest;
    }

    private function makeOrder(): Order
    {
        return Order::create([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'ALOC-'.uniqid(),
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

    private function build(Order $order): array
    {
        return app(OrderFormDataBuilder::class)->build($this->company, $order->fresh());
    }

    /** Save the order the way the form does, with only the quote payload. */
    private function saveOrderWithCarrierRows(Order $order, array $carrierRows)
    {
        return $this->actingAs($this->user)->patch(
            route('v2.orders.update', ['company' => $this->company->slug, 'order' => $order->id]),
            [
                'order_type' => 'point_to_point',
                'stops' => '[]', // stops already exist; the form only re-sends them when edited
                'submission_mode' => 'quote',
                'save_as_draft' => '0',
                'quote_data' => json_encode([
                    'customer_rows' => [['type' => 'Freight', 'description' => 'Freight', 'qty' => 1, 'rate' => 1000]],
                    'carrier_rows' => $carrierRows,
                ]),
            ]
        );
    }

    // ── Allocation maths ────────────────────────────────────────────────

    public function test_order_on_one_manifest_carries_the_whole_manifest_cost(): void
    {
        $manifest = $this->makeManifest(900);
        $order = $this->makeOrder();
        $this->addStops($order, $manifest, 3);

        $allocation = $this->service->carrierCostForOrder($order);

        $this->assertSame(900.0, $allocation['total']);
        $this->assertCount(1, $allocation['rows']);
        $this->assertFalse($allocation['rows'][0]['is_shared']);
    }

    public function test_cost_of_a_shared_manifest_is_split_between_its_orders(): void
    {
        $manifest = $this->makeManifest(1000);

        $mine = $this->makeOrder();
        $this->addStops($mine, $manifest, 1);

        $theirs = $this->makeOrder();
        $this->addStops($theirs, $manifest, 3);

        $this->assertSame(250.0, $this->service->carrierCostForOrder($mine)['total']);
        $this->assertSame(750.0, $this->service->carrierCostForOrder($theirs)['total']);
    }

    public function test_order_across_two_manifests_adds_up_both_costs(): void
    {
        $first = $this->makeManifest(400);
        $second = $this->makeManifest(600);

        $order = $this->makeOrder();
        $this->addStops($order, $first, 1);
        $this->addStops($order, $second, 1);

        $allocation = $this->service->carrierCostForOrder($order);

        $this->assertSame(1000.0, $allocation['total']);
        $this->assertEqualsCanonicalizing([400.0, 600.0], array_column($allocation['rows'], 'amount'));
    }

    public function test_order_with_no_manifest_has_no_carrier_cost(): void
    {
        $order = $this->makeOrder();
        $this->addStops($order, null, 2);

        $allocation = $this->service->carrierCostForOrder($order);

        $this->assertSame([], $allocation['rows']);
        $this->assertSame(0.0, $allocation['total']);
    }

    // ── What the order form is given ────────────────────────────────────

    public function test_rows_come_from_the_manifest_when_one_manifest_covers_the_order(): void
    {
        $manifest = $this->makeManifest(500);
        $order = $this->makeOrder();
        $this->addStops($order, $manifest, 2);

        // A carrier row left on the order by an older save must be ignored
        $quote = OrderQuote::create(['order_id' => $order->id]);
        $quote->costs()->create([
            'category' => 'carrier', 'type' => 'Freight',
            'description' => 'Legacy carrier row', 'qty' => 1, 'rate' => 123, 'cost' => 123,
        ]);

        $data = $this->build($order);

        $this->assertTrue($data['carrierEditable']);
        $this->assertSame($manifest->id, $data['carrierTargetManifest']->id);
        $this->assertCount(1, $data['quoteData']['carrier_rows']);
        $this->assertSame('Carrier freight', $data['quoteData']['carrier_rows'][0]['description']);
        $this->assertSame(500.0, (float) $data['quoteData']['carrier_rows'][0]['cost']);
    }

    public function test_panel_is_read_only_when_the_order_spans_two_manifests(): void
    {
        $order = $this->makeOrder();
        $this->addStops($order, $this->makeManifest(400), 1);
        $this->addStops($order, $this->makeManifest(600), 1);

        $data = $this->build($order);

        $this->assertFalse($data['carrierEditable']);
        $this->assertNull($data['carrierTargetManifest']);
        $this->assertSame([], $data['quoteData']['carrier_rows']);
        $this->assertSame(1000.0, $data['carrierAllocation']['total']);
    }

    public function test_modal_is_editable_on_one_manifest_and_read_only_across_two(): void
    {
        $manifest = $this->makeManifest(500);
        $single = $this->makeOrder();
        $this->addStops($single, $manifest, 2);

        $this->actingAs($this->user)
            ->get(route('v2.orders.edit', ['company' => $this->company->slug, 'order' => $single->id]))
            ->assertOk()
            ->assertSee('Saves to '.$manifest->code)
            ->assertSee("addQuoteRow('carrier')", false);

        $split = $this->makeOrder();
        $this->addStops($split, $this->makeManifest(400), 1);
        $this->addStops($split, $this->makeManifest(600), 1);

        $this->actingAs($this->user)
            ->get(route('v2.orders.edit', ['company' => $this->company->slug, 'order' => $split->id]))
            ->assertOk()
            ->assertSee('From manifests')
            ->assertDontSee("addQuoteRow('carrier')", false);
    }

    // ── Saving from the order ───────────────────────────────────────────

    public function test_saving_the_order_writes_carrier_cost_to_its_manifest(): void
    {
        $manifest = $this->makeManifest(500);
        $order = $this->makeOrder();
        $this->addStops($order, $manifest, 2);

        $this->saveOrderWithCarrierRows($order, [
            ['type' => 'Freight', 'description' => 'Partner Freight', 'qty' => 1, 'rate' => 800],
            ['type' => 'Fuel (surcharge)', 'description' => '', 'qty' => 10, 'rate' => 0],
        ])->assertSessionHasNoErrors()->assertRedirect();

        // The manifest now holds exactly what was typed: 800 freight + 10% fuel
        $manifest->refresh()->load('costEstimates');
        $this->assertSame(880.0, (float) $manifest->costEstimates->sum('est_cost'));
        $this->assertSame(880.0, $this->service->carrierCostForOrder($order->fresh())['total']);

        // and the order itself still stores no carrier costs
        $this->assertSame(0, $order->fresh()->quote->costs->where('category', 'carrier')->count());
    }

    public function test_saving_an_order_across_two_manifests_leaves_both_alone(): void
    {
        $first = $this->makeManifest(400);
        $second = $this->makeManifest(600);

        $order = $this->makeOrder();
        $this->addStops($order, $first, 1);
        $this->addStops($order, $second, 1);

        $this->saveOrderWithCarrierRows($order, [
            ['type' => 'Freight', 'description' => 'Should be ignored', 'qty' => 1, 'rate' => 999],
        ])->assertRedirect();

        $this->assertSame(400.0, (float) $first->fresh()->costEstimates->sum('est_cost'));
        $this->assertSame(600.0, (float) $second->fresh()->costEstimates->sum('est_cost'));
    }
}
