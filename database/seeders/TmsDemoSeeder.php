<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Manifest;
use App\Models\Order;
use App\Models\OrderStop;
use App\Models\OrderStopCommodity;
use App\Models\OrderQuote;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TmsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('🚚 Seeding TMS Demo Data (Innovations Freight Solutions)...');

        // ── 1. Company ──────────────────────────────────────────────────────────
        $company = Company::firstOrCreate(
            ['slug' => 'innovations-freight-solutions'],
            [
                'name'      => 'Innovations Freight Solutions',
                'shortcode' => 'INVO',
                'address'   => '200 Industrial Blvd, Chicago, IL 60601',
                'phone'     => '+1-312-555-0100',
                'is_active' => true,
                'is_deleted' => false,
            ]
        );

        if (! $company->wasRecentlyCreated) {
            $company->update(['shortcode' => 'INVO']);
        }

        $this->command->info("  ✔ Company: {$company->name} (shortcode: {$company->shortcode})");

        // ── 2. Permissions & Admin Role ─────────────────────────────────────────
        $permissions = Permission::all();

        $adminRole = Role::firstOrCreate(
            ['name' => 'company_admin', 'company_id' => $company->id],
            ['is_active' => true]
        );

        $rolePermissions = [];
        foreach ($permissions as $perm) {
            $rolePermissions[$perm->id] = [
                'create' => true, 'update' => true, 'view' => true,
                'delete' => true, 'logs' => true, 'others' => true,
            ];
        }
        $adminRole->permissions()->sync($rolePermissions);

        // ── 3. Demo Admin User ──────────────────────────────────────────────────
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@innovations-freight.com'],
            [
                'name'               => 'Admin Innovations',
                'f_name'             => 'Admin',
                'l_name'             => 'Innovations',
                'password'           => Hash::make('password'),
                'company_id'         => $company->id,
                'is_active'          => true,
                'is_deleted'         => false,
                'email_verified_at'  => now(),
                'address'            => '200 Industrial Blvd, Chicago, IL 60601',
                'phone'              => '+1-312-555-0101',
            ]
        );

        if (! $adminUser->roles()->where('role_id', $adminRole->id)->exists()) {
            $adminUser->roles()->attach($adminRole->id);
        }

        $this->command->info("  ✔ Admin user: {$adminUser->email} / password: password");

        // ── 4. Customer ─────────────────────────────────────────────────────────
        $customer = Customer::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'Apex Freight Solutions Ltd'],
            [
                'short_code'             => 'AFSL',
                'customer_type'          => 'shipper',
                'default_billing_option' => 'shipper',
                'customer_email'         => 'dispatch@apexfreight.com',
                'address'                => '450 Commerce Parkway',
                'city'                   => 'Toronto',
                'state'                  => 'ON',
                'postal_code'            => 'M5V 2T6',
                'country'                => 'CA',
                'currency'               => 'CAD',
                'is_active'              => true,
                'is_deleted'             => false,
                'quote_required'         => true,
                'portal'                 => true,
                'password'               => Hash::make('password'),
            ]
        );

        if (! $customer->portal) {
            $customer->update(['portal' => true]);
        }

        if (! $customer->password) {
            $customer->update(['password' => Hash::make('password')]);
        }

        $this->command->info("  ✔ Customer: {$customer->name} (short_code: {$customer->short_code})");
        $this->command->info("  ✔ Portal login: {$customer->customer_email} / password: password");

        // ── 5. Manifests ────────────────────────────────────────────────────────
        $manifests = $this->seedManifests($company);

        // ── 6. Orders with Stops ────────────────────────────────────────────────
        $this->seedOrders($company, $customer, $manifests);

        $this->command->info('');
        $this->command->info('✅ TMS Demo seeding complete!');
        $this->command->info("   Login → admin@innovations-freight.com / password");
        $this->command->line('   Company slug: ' . $company->slug);
    }

    // ── Manifests ───────────────────────────────────────────────────────────────
    private function seedManifests(Company $company): array
    {
        $manifestDefs = [
            [
                'status'     => 'active',
                'start_date' => Carbon::now()->addDays(1)->toDateString(),
                'freight'    => 'General Freight — Chicago to Toronto corridor',
            ],
            [
                'status'     => 'active',
                'start_date' => Carbon::now()->addDays(3)->toDateString(),
                'freight'    => 'Refrigerated Goods — Detroit loop',
            ],
            [
                'status'     => 'draft',
                'start_date' => Carbon::now()->addDays(7)->toDateString(),
                'freight'    => 'Hazmat Class 3 — Cross-border run',
            ],
            [
                'status'     => 'active',
                'start_date' => Carbon::now()->addDays(2)->toDateString(),
                'freight'    => 'Retail Distribution — Midwest milk run',
            ],
        ];

        $manifests = [];
        foreach ($manifestDefs as $def) {
            $tempCode = 'TEMP-' . uniqid();
            $manifest = Manifest::create(array_merge($def, [
                'company_id' => $company->id,
                'code'       => $tempCode,
                'draft'      => $def['status'] === 'draft',
            ]));

            // Shortcode-based manifest ID: {SC}M{zero-padded-id}
            // e.g. INVOM0001 — company shortcode + M + 4-digit id
            $manifest->update([
                'code' => strtoupper($company->shortcode) . 'M' . str_pad($manifest->id, 4, '0', STR_PAD_LEFT),
            ]);

            $manifests[] = $manifest;
            $this->command->info("  ✔ Manifest: {$manifest->code}");
        }

        return $manifests;
    }

    // ── Orders ──────────────────────────────────────────────────────────────────
    private function seedOrders(Company $company, Customer $customer, array $manifests): void
    {
        $orderDefs = [
            // 1 — point_to_point: Chicago → Toronto (5 stops)
            [
                'order_type'   => 'point_to_point',
                'status'       => 'booked',
                'ref_number'   => 'REF-CHI-TOR-001',
                'manifest_idx' => 0,
                'stops'        => [
                    $this->stop('pickup',   'Innovations Chicago Hub',       '200 Industrial Blvd',        'Chicago',       'IL', '60601', 'US', '2026-05-08 07:00', '2026-05-08 09:00'),
                    $this->stop('delivery', 'Midwest Auto Parts',            '1800 Harlem Ave',            'Berwyn',        'IL', '60402', 'US', '2026-05-08 10:00', '2026-05-08 11:30'),
                    $this->stop('pickup',   'Gary Steel Works',              '3400 Broadway',              'Gary',          'IN', '46408', 'US', '2026-05-08 13:00', '2026-05-08 14:30'),
                    $this->stop('delivery', 'Detroit Receiving Center',      '1000 Woodward Ave',          'Detroit',       'MI', '48226', 'US', '2026-05-08 17:00', '2026-05-08 18:30'),
                    $this->stop('delivery', 'Apex Freight Toronto Dock',     '450 Commerce Pkwy',          'Toronto',       'ON', 'M5V2T6', 'CA', '2026-05-09 10:00', '2026-05-09 12:00'),
                ],
            ],

            // 2 — single_shipper: One origin, multiple drops (4 stops)
            [
                'order_type'   => 'single_shipper',
                'status'       => 'in_transit',
                'ref_number'   => 'REF-MDW-DIST-002',
                'manifest_idx' => 3,
                'stops'        => [
                    $this->stop('pickup',   'Midwest Distribution Hub',      '5500 W Lake St',             'Chicago',       'IL', '60644', 'US', '2026-05-07 06:00', '2026-05-07 08:00'),
                    $this->stop('delivery', 'Rockford Retail DC',            '2200 N Main St',             'Rockford',      'IL', '61103', 'US', '2026-05-07 10:30', '2026-05-07 12:00'),
                    $this->stop('delivery', 'Milwaukee Store #4',            '700 N Water St',             'Milwaukee',     'WI', '53202', 'US', '2026-05-07 14:00', '2026-05-07 15:30'),
                    $this->stop('delivery', 'Madison Warehouse',             '100 State St',               'Madison',       'WI', '53703', 'US', '2026-05-07 17:00', '2026-05-07 18:00'),
                ],
            ],

            // 3 — single_consignee: Milk run (5 stops → 1 delivery)
            [
                'order_type'        => 'single_consignee',
                'status'            => 'quoted',
                'ref_number'        => 'REF-MILK-DET-003',
                'customer_po_number' => 'PO-DET-9821',
                'manifest_idx'      => 1,
                'stops'             => [
                    $this->stop('pickup',   'Ford Stamping Plant',           '1700 Rotunda Dr',            'Dearborn',      'MI', '48124', 'US', '2026-05-09 06:00', '2026-05-09 07:30'),
                    $this->stop('pickup',   'GM Component Supplier',         '400 Renaissance Ctr',        'Detroit',       'MI', '48243', 'US', '2026-05-09 08:30', '2026-05-09 09:30'),
                    $this->stop('pickup',   'Stellantis Parts Plant',        '800 Chrysler Dr',            'Auburn Hills',  'MI', '48326', 'US', '2026-05-09 10:30', '2026-05-09 11:30'),
                    $this->stop('pickup',   'Tier-1 Supplier — Flint',       '310 Court St',               'Flint',         'MI', '48502', 'US', '2026-05-09 13:00', '2026-05-09 14:00'),
                    $this->stop('delivery', 'Apex Assembly Toronto',         '350 Carlingview Dr',         'Toronto',       'ON', 'M9W5G7', 'CA', '2026-05-10 09:00', '2026-05-10 11:00'),
                ],
            ],

            // 4 — sequence: Shuttle loop (4 stops)
            [
                'order_type'   => 'sequence',
                'status'       => 'new',
                'ref_number'   => 'REF-XBDR-004',
                'manifest_idx' => 2,
                'stops'        => [
                    $this->stop('pickup',   'Innovations Chicago HQ',        '200 Industrial Blvd',        'Chicago',       'IL', '60601', 'US', '2026-05-12 07:00', '2026-05-12 08:00'),
                    $this->stop('mixed',    'Windsor Cross-Dock',            '400 Walker Rd',              'Windsor',       'ON', 'N8Y 2N5', 'CA', '2026-05-12 14:00', '2026-05-12 16:00'),
                    $this->stop('mixed',    'London ON Transfer Hub',        '300 Wellington Rd S',        'London',        'ON', 'N6C 4P4', 'CA', '2026-05-12 18:00', '2026-05-12 19:30'),
                    $this->stop('delivery', 'Apex Freight Mississauga',      '6900 Airport Rd',            'Mississauga',   'ON', 'L4V 1E8', 'CA', '2026-05-13 08:00', '2026-05-13 10:00'),
                ],
            ],

            // 5 — sequence: Multi-stop return haul (5 stops)
            [
                'order_type'   => 'sequence',
                'status'       => 'draft',
                'ref_number'   => 'REF-RETURN-005',
                'manifest_idx' => 0,
                'stops'        => [
                    $this->stop('pickup',   'Apex Freight Toronto',          '450 Commerce Pkwy',          'Toronto',       'ON', 'M5V2T6', 'CA', '2026-05-14 07:00', '2026-05-14 08:30'),
                    $this->stop('delivery', 'Buffalo DC',                    '100 James E Casey Dr',       'Buffalo',       'NY', '14206',  'US', '2026-05-14 12:00', '2026-05-14 13:30'),
                    $this->stop('pickup',   'Erie Lumber Yard',              '2500 Peach St',              'Erie',          'PA', '16508',  'US', '2026-05-14 15:30', '2026-05-14 17:00'),
                    $this->stop('delivery', 'Cleveland Warehouse',           '1 Key Plaza',                'Cleveland',     'OH', '44114',  'US', '2026-05-14 19:30', '2026-05-14 21:00'),
                    $this->stop('delivery', 'Innovations Chicago Hub',       '200 Industrial Blvd',        'Chicago',       'IL', '60601',  'US', '2026-05-15 06:00', '2026-05-15 08:00'),
                ],
            ],
        ];

        foreach ($orderDefs as $i => $def) {
            $manifest = $manifests[$def['manifest_idx']];

            // Create order with TEMP number first
            $order = Order::create([
                'company_id'         => $company->id,
                'customer_id'        => $customer->id,
                'manifest_id'        => $manifest->id,
                'order_type'         => $def['order_type'],
                'status'             => $def['status'],
                'ref_number'         => $def['ref_number'] ?? null,
                'customer_po_number' => $def['customer_po_number'] ?? null,
                'order_number'       => 'TEMP-' . uniqid(),
            ]);

            // Shortcode-based order number: {COMPANY_SC}-{CUSTOMER_SC}-{ID}
            // e.g. INVO-AFSL-45  (always unique because id is unique)
            $order->update([
                'order_number' => strtoupper($company->shortcode) . '-' . strtoupper($customer->short_code) . '-' . $order->id,
            ]);

            $this->command->info("  ✔ Order: {$order->order_number} ({$def['order_type']}, {$def['status']})");

            // Stops
            foreach ($def['stops'] as $seq => $stopData) {
                $stop = OrderStop::create(array_merge($stopData, [
                    'order_id'        => $order->id,
                    'manifest_id'     => $manifest->id,
                    'sequence_number' => $seq + 1,
                    'service_type'    => 'truckload',
                    'measurement_type' => 'in_lbs',
                ]));

                // One commodity per stop
                OrderStopCommodity::create([
                    'order_stop_id'  => $stop->id,
                    'description'    => $this->commodityDescription($stopData['stop_type'], $i),
                    'type'           => 'skid',
                    'quantity'       => rand(5, 20),
                    'pieces'         => rand(10, 80),
                    'weight'         => rand(500, 4500),
                    'length'         => 48,
                    'width'          => 40,
                    'height'         => 60,
                    'linear_feet'    => round(rand(5, 20) * 0.33, 1),
                    'cube'           => rand(50, 200),
                    'freight_class'  => $this->freightClass($i),
                    'measurement_type' => 'imperial',
                ]);

                $this->command->line("       Stop {$stop->sequence_number}: [{$stopData['stop_type']}] {$stopData['company_name']} — {$stopData['city']}, {$stopData['state']}");
            }

            // Basic quote for non-draft/non-new orders
            if (in_array($def['status'], ['quoted', 'booked', 'in_transit', 'delivered'])) {
                $this->seedQuote($order);
            }
        }
    }

    // ── Helpers ─────────────────────────────────────────────────────────────────

    private function stop(string $type, string $company, string $address, string $city, string $state, string $zip, string $country, string $start, string $end): array
    {
        return [
            'stop_type'     => $type,
            'company_name'  => $company,
            'address_1'     => $address,
            'city'          => $city,
            'state'         => $state,
            'postal_code'   => $zip,
            'country'       => $country,
            'start_time'    => Carbon::parse($start),
            'end_time'      => Carbon::parse($end),
            'opening_time'  => '07:00',
            'closing_time'  => '18:00',
            'is_appointment' => false,
            'contact_name'  => 'Dock Manager',
            'contact_phone' => '+1-555-' . rand(100, 999) . '-' . rand(1000, 9999),
            'contact_email' => 'dock@' . Str::slug($company) . '.com',
            'notes'         => '',
        ];
    }

    private function commodityDescription(string $stopType, int $orderIdx): string
    {
        $goods = [
            'Auto Components', 'Industrial Hardware', 'Retail Merchandise',
            'General Freight', 'Electronic Components',
        ];
        return ($stopType === 'pickup' ? 'PU: ' : 'DEL: ') . ($goods[$orderIdx] ?? 'General Freight');
    }

    private function freightClass(int $idx): string
    {
        return ['70', '85', '92.5', '100', '110'][$idx % 5];
    }

    private function seedQuote(Order $order): void
    {
        $freightQty  = rand(5, 20);
        $freightRate = rand(120, 350);
        $fuelPct     = rand(10, 18);
        $freightBase = $freightQty * $freightRate;
        $fuelCost    = round($freightBase * $fuelPct / 100, 2);

        $quote = OrderQuote::create([
            'order_id'            => $order->id,
            'service_id'          => null,
            'delivery_start_date' => $order->stops->first()?->start_time?->toDateString(),
            'delivery_end_date'   => $order->stops->last()?->end_time?->toDateString(),
        ]);

        // Customer rows
        $quote->costs()->createMany([
            ['category' => 'customer', 'type' => 'Freight',          'description' => 'Line Haul',        'qty' => $freightQty,  'rate' => $freightRate, 'cost' => $freightBase,  'percentage' => null],
            ['category' => 'customer', 'type' => 'Fuel (Surcharge)', 'description' => "FSC {$fuelPct}%",  'qty' => $fuelPct,     'rate' => $freightBase, 'cost' => $fuelCost,     'percentage' => $fuelPct],
            ['category' => 'customer', 'type' => 'Miscellaneous',    'description' => 'Border Fee',        'qty' => 1,            'rate' => 75,           'cost' => 75,            'percentage' => null],
        ]);

        // Carrier rows (slightly less)
        $carrierRate = round($freightRate * 0.8, 2);
        $carrierBase = $freightQty * $carrierRate;
        $carrierFuel = round($carrierBase * $fuelPct / 100, 2);

        $quote->costs()->createMany([
            ['category' => 'carrier', 'type' => 'Freight',          'description' => 'Carrier Line Haul', 'qty' => $freightQty, 'rate' => $carrierRate, 'cost' => $carrierBase, 'percentage' => null],
            ['category' => 'carrier', 'type' => 'Fuel (Surcharge)', 'description' => "FSC {$fuelPct}%",   'qty' => $fuelPct,    'rate' => $carrierBase, 'cost' => $carrierFuel, 'percentage' => $fuelPct],
        ]);
    }
}
