<?php

namespace Tests\Feature;

use App\Models\Accessorial;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerCommodity;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderFormDataBuilder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The order form may only offer the selected customer's own accessorials and
 * commodities — never another customer's, and never the whole company list.
 */
class OrderCommodityPresetsTest extends TestCase
{
    use DatabaseTransactions;

    private Company $company;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $suffix = uniqid();

        $this->company = Company::create([
            'name' => 'Preset Co '.$suffix,
            'slug' => 'preset-co-'.$suffix,
            'shortcode' => 'PRST',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $this->user = User::create([
            'company_id' => $this->company->id,
            'name' => 'Preset User',
            'f_name' => 'Preset',
            'l_name' => 'User',
            'email' => "preset.user.{$suffix}@example.com",
            'password' => Hash::make('password'),
            'status' => 'active',
            'is_active' => true,
            'is_deleted' => false,
            'is_super_admin' => true,
        ]);
    }

    private function makeCustomer(string $name): Customer
    {
        return Customer::create([
            'company_id' => $this->company->id,
            'name' => $name,
            'short_code' => strtoupper(substr(uniqid(), -4)),
            'is_active' => true,
            'is_deleted' => false,
        ]);
    }

    private function makeOrder(?Customer $customer): Order
    {
        return Order::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer?->id,
            'order_number' => 'PRST-'.uniqid(),
            'order_type' => 'point_to_point',
            'status' => 'draft',
        ]);
    }

    private function build(Order $order): array
    {
        return app(OrderFormDataBuilder::class)->build($this->company, $order);
    }

    public function test_only_the_selected_customers_commodities_are_offered(): void
    {
        $customer = $this->makeCustomer('Atlas Granite');
        $other = $this->makeCustomer('Rival Freight');

        CustomerCommodity::create([
            'company_id' => $this->company->id, 'customer_id' => $customer->id,
            'description' => 'Coconut Oil', 'type' => 'skid', 'measurement_unit' => 'in_lbs',
            'weight' => 15000, 'length' => 40, 'width' => 48, 'height' => 40,
            'freight_class' => '70', 'sku' => 'CO-500', 'nmfc' => '12345',
        ]);
        CustomerCommodity::create([
            'company_id' => $this->company->id, 'customer_id' => $other->id,
            'description' => 'Steel Coil', 'type' => 'crate', 'measurement_unit' => 'in_lbs',
        ]);

        $presets = collect($this->build($this->makeOrder($customer))['customerCommodities']);

        $this->assertCount(1, $presets);
        $this->assertSame('Coconut Oil', $presets->first()['description']);
        $this->assertNotContains('Steel Coil', $presets->pluck('description')->all());
    }

    public function test_preset_carries_every_field_the_order_row_fills_plus_search_keys(): void
    {
        $customer = $this->makeCustomer('Atlas Granite');

        CustomerCommodity::create([
            'company_id' => $this->company->id, 'customer_id' => $customer->id,
            'description' => 'Coconut Oil', 'type' => 'skid', 'measurement_unit' => 'cm_kg',
            'weight' => 6800, 'length' => 100, 'width' => 120, 'height' => 100,
            'linear_feet' => 4, 'volume' => 2.5, 'freight_class' => '70',
            'sku' => 'CO-500', 'nmfc' => '12345',
        ]);

        $preset = collect($this->build($this->makeOrder($customer))['customerCommodities'])->first();

        // Copied into the row
        $this->assertSame('skid', $preset['type']);
        $this->assertSame(6800.0, $preset['weight']);
        $this->assertSame(100.0, $preset['length']);
        $this->assertSame(120.0, $preset['width']);
        $this->assertSame(100.0, $preset['height']);
        $this->assertSame(4.0, $preset['lf']);
        $this->assertSame(2.5, $preset['cube']);
        $this->assertSame('70', $preset['freight_class']);

        // Used for searching and the suggestion line, not copied
        $this->assertSame('CO-500', $preset['sku']);
        $this->assertSame('12345', $preset['nmfc']);
        $this->assertSame('cm_kg', $preset['unit']);
    }

    public function test_only_the_customers_accessorials_are_offered(): void
    {
        $customer = $this->makeCustomer('Atlas Granite');

        $allowed = Accessorial::create(['company_id' => $this->company->id, 'name' => 'Tailgate delivery '.uniqid()]);
        $notEnabled = Accessorial::create(['company_id' => $this->company->id, 'name' => 'Reefer '.uniqid()]);
        $customer->accessorials()->sync([$allowed->id]);

        $ids = $this->build($this->makeOrder($customer))['allAccessorials']->pluck('id')->all();

        $this->assertContains($allowed->id, $ids);
        $this->assertNotContains($notEnabled->id, $ids);
    }

    public function test_order_without_a_customer_offers_no_commodities(): void
    {
        $data = $this->build($this->makeOrder(null));

        $this->assertCount(0, $data['customerCommodities']);
    }

    public function test_order_form_ships_the_presets_and_the_search_wiring(): void
    {
        $customer = $this->makeCustomer('Atlas Granite');
        CustomerCommodity::create([
            'company_id' => $this->company->id, 'customer_id' => $customer->id,
            'description' => 'Coconut Oil', 'type' => 'skid', 'measurement_unit' => 'in_lbs', 'sku' => 'CO-500',
        ]);

        $this->actingAs($this->user)
            ->get(route('v2.orders.edit', ['company' => $this->company->slug, 'order' => $this->makeOrder($customer)->id]))
            ->assertOk()
            ->assertSee('Coconut Oil')
            ->assertSee('commodityMatches', false)
            ->assertDontSee('customer-commodity-presets'); // the old datalist is gone
    }
}
