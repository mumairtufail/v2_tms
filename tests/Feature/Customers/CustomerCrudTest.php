<?php

namespace Tests\Feature\Customers;

use App\Models\Accessorial;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerCommodity;
use App\Models\CustomerContact;
use App\Models\Order;
use App\Models\User;
use App\Services\CustomerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Create / read / update / delete for every customer tab.
 */
class CustomerCrudTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private User $staff;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = $this->makeCompany('Innovations Freight');
        $this->staff = $this->makeUser(superAdmin: true);
        $this->customer = app(CustomerService::class)->createCustomer([
            'company_id' => $this->company->id,
            'name' => 'Apex Freight Solutions Ltd',
            'short_code' => 'AFSL',
            'address_1' => '100 King St W',
            'city' => 'Toronto',
            'state' => 'ON',
            'postal_code' => 'M5V 2T6',
            'country' => 'CA',
            'currency' => 'CAD',
            'customer_type' => 'shipper',
            'default_billing_option' => 'shipper',
        ]);
    }

    private function makeCompany(string $name): Company
    {
        return Company::create([
            'name' => $name,
            'slug' => str()->slug($name) . '-' . uniqid(),
            'shortcode' => 'INV',
            'is_active' => true,
            'is_deleted' => false,
        ]);
    }

    private function makeUser(bool $superAdmin): User
    {
        return User::create([
            'company_id' => $this->company->id,
            'name' => $superAdmin ? 'Dana Dispatch' : 'Vic Viewer',
            'f_name' => $superAdmin ? 'Dana' : 'Vic',
            'l_name' => $superAdmin ? 'Dispatch' : 'Viewer',
            'email' => uniqid('staff.') . '@example.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'is_active' => true,
            'is_deleted' => false,
            'is_super_admin' => $superAdmin,
        ]);
    }

    private function url(string $name, array $params = []): string
    {
        return route($name, array_merge(['company' => $this->company->slug, 'customer' => $this->customer->id], $params));
    }

    private function contactPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Morgan',
            'last_name' => 'Lee',
            'job_title' => 'Logistics Manager',
            'email' => 'morgan@apexfreight.com',
            'send_invoices' => '1',
            'phones' => [['type' => 'office', 'number' => '416-555-0101', 'ext' => '12']],
        ], $overrides);
    }

    // ── Customer: create / list / details / deactivate ─────────────────────

    public function test_create_customer_requires_the_modal_fields_and_a_unique_short_code(): void
    {
        $this->actingAs($this->staff)
            ->post(route('v2.customers.store', ['company' => $this->company->slug]), ['short_code' => 'AFSL'])
            ->assertSessionHasErrors(['name', 'address_1', 'city', 'state', 'postal_code', 'country', 'currency', 'default_billing_option', 'short_code']);

        $this->assertSame(1, Customer::count());
    }

    public function test_list_searches_filters_and_sorts(): void
    {
        $service = app(CustomerService::class);
        $base = ['company_id' => $this->company->id, 'address_1' => '1 Main St', 'city' => 'Chicago', 'state' => 'IL', 'postal_code' => '60601', 'country' => 'US', 'currency' => 'USD', 'default_billing_option' => 'shipper'];
        $service->createCustomer($base + ['name' => 'Atlas Granite Inc']);
        $service->createCustomer($base + ['name' => 'Boscus'])->update(['is_active' => false]);

        $index = fn (array $query) => $this->actingAs($this->staff)->get(route('v2.customers.index', ['company' => $this->company->slug] + $query));

        $index(['search' => 'Atl'])->assertOk()->assertSee('Atlas Granite Inc')->assertDontSee('Boscus');
        $index(['status' => 'inactive'])->assertOk()->assertSee('Boscus')->assertDontSee('Atlas Granite Inc');
        $index(['sort' => 'name', 'direction' => 'desc'])->assertOk()->assertSeeInOrder(['Boscus', 'Atlas Granite Inc', 'Apex Freight Solutions Ltd']);
    }

    public function test_old_create_and_edit_links_still_work(): void
    {
        $this->actingAs($this->staff)
            ->get(route('v2.customers.create', ['company' => $this->company->slug]))
            ->assertRedirect(route('v2.customers.index', ['company' => $this->company->slug, 'create' => 1]));

        $this->actingAs($this->staff)
            ->get($this->url('v2.customers.edit'))
            ->assertRedirect($this->url('v2.customers.show', ['tab' => 'details']));
    }

    public function test_details_update_saves_every_field_but_never_the_short_code(): void
    {
        $this->actingAs($this->staff)->patch($this->url('v2.customers.update'), [
            'name' => 'Apex Freight Solutions Limited',
            'short_code' => 'ZZZZ',
            'external_id' => 'EXT-778',
            'credit_limit' => '5,000.50',
            'location_sharing' => 'approximate',
            'currency' => 'USD',
            'customer_type' => 'broker',
            'quote_required' => '0',
            'default_billing_option' => 'consignee',
            'is_active' => '1',
            'require_dimensions' => '1',
            'network_customer' => '1',
        ])->assertSessionHasNoErrors()->assertRedirect($this->url('v2.customers.show', ['tab' => 'details']));

        $customer = $this->customer->fresh();
        $this->assertSame('Apex Freight Solutions Limited', $customer->name);
        $this->assertSame('AFSL', $customer->short_code);
        $this->assertSame('EXT-778', $customer->external_id);
        $this->assertSame('5000.50', $customer->credit_limit);
        $this->assertSame('approximate', $customer->location_sharing);
        $this->assertSame('USD', $customer->currency);
        $this->assertSame('broker', $customer->customer_type);
        $this->assertFalse($customer->quote_required);
        $this->assertSame('consignee', $customer->default_billing_option);
        $this->assertTrue($customer->require_dimensions);
        $this->assertTrue($customer->network_customer);
    }

    public function test_unchecked_toggles_turn_off_and_bad_values_are_rejected(): void
    {
        $valid = ['name' => 'Apex', 'location_sharing' => 'Do not share', 'currency' => 'CAD', 'customer_type' => 'shipper', 'default_billing_option' => 'shipper'];

        $this->actingAs($this->staff)->patch($this->url('v2.customers.update'), $valid)->assertSessionHasNoErrors();
        $this->assertFalse($this->customer->fresh()->is_active, 'an unchecked "Is active" switch deactivates');

        $this->actingAs($this->staff)
            ->patch($this->url('v2.customers.update'), ['credit_limit' => '-1', 'location_sharing' => 'everywhere'] + $valid)
            ->assertSessionHasErrors(['credit_limit', 'location_sharing']);
    }

    public function test_logo_can_be_uploaded_replaced_and_removed(): void
    {
        Storage::fake('public');
        $valid = ['name' => 'Apex', 'is_active' => '1', 'location_sharing' => 'Do not share', 'currency' => 'CAD', 'customer_type' => 'shipper', 'default_billing_option' => 'shipper'];

        $this->actingAs($this->staff)->patch($this->url('v2.customers.update'), $valid + [
            'logo' => UploadedFile::fake()->create('apex.png', 120, 'image/png'),
        ])->assertSessionHasNoErrors();
        $first = $this->customer->fresh()->logo_path;
        Storage::disk('public')->assertExists($first);

        $this->actingAs($this->staff)->patch($this->url('v2.customers.update'), $valid + [
            'logo' => UploadedFile::fake()->create('apex-2.png', 120, 'image/png'),
        ]);
        $second = $this->customer->fresh()->logo_path;
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);

        $this->actingAs($this->staff)->patch($this->url('v2.customers.update'), $valid + ['remove_logo' => '1']);
        $this->assertNull($this->customer->fresh()->logo_path);
        Storage::disk('public')->assertMissing($second);

        $this->actingAs($this->staff)->patch($this->url('v2.customers.update'), $valid + [
            'logo' => UploadedFile::fake()->create('notes.pdf', 50, 'application/pdf'),
        ])->assertSessionHasErrors('logo');
    }

    public function test_mark_inactive_from_the_list_returns_to_the_list(): void
    {
        $this->actingAs($this->staff)
            ->patch($this->url('v2.customers.deactivate'))
            ->assertRedirect(route('v2.customers.index', ['company' => $this->company->slug]));

        $this->assertFalse($this->customer->fresh()->is_active);
    }

    public function test_refreshing_the_balance_needs_a_quickbooks_customer(): void
    {
        $this->actingAs($this->staff)
            ->post($this->url('v2.customers.refresh-balance'))
            ->assertRedirect($this->url('v2.customers.show', ['tab' => 'details']));

        $this->assertNull($this->customer->fresh()->credit_balance);
    }

    // ── Addresses ──────────────────────────────────────────────────────────

    public function test_address_update_changes_only_the_address_fields(): void
    {
        $billing = $this->customer->billingAddress;

        $this->actingAs($this->staff)->put($this->url('v2.customers.addresses.update', ['address' => $billing->id]), [
            'address_1' => '200 Bay St',
            'address_2' => 'Floor 12',
            'city' => 'Mississauga',
            'state' => 'ON',
            'postal_code' => 'L5T 2X5',
            'country' => 'ca',
        ])->assertSessionHasNoErrors()->assertRedirect($this->url('v2.customers.show', ['tab' => 'addresses']));

        $billing->refresh();
        $this->assertSame('200 Bay St', $billing->address_1);
        $this->assertSame('Floor 12', $billing->address_2);
        $this->assertSame('L5T 2X5', $billing->postal_code);
        $this->assertSame('CA', $billing->country);
        $this->assertSame('Apex Freight Solutions Ltd', $billing->company_name, 'editing keeps the label');
        $this->assertTrue($billing->is_billing, 'the only billing address stays billing when the box is unchecked');
        $this->assertSame('Mississauga', $this->customer->fresh()->city);
    }

    public function test_several_addresses_can_be_added_one_after_another(): void
    {
        $address = fn (string $street) => ['address_1' => $street, 'city' => 'Toronto', 'state' => 'ON', 'postal_code' => 'M5V 2T6', 'country' => 'CA'];

        $this->actingAs($this->staff)
            ->post($this->url('v2.customers.addresses.store'), $address('1 Front St') + ['add_another' => '1'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('address_add_another', true);

        $this->actingAs($this->staff)
            ->post($this->url('v2.customers.addresses.store'), $address('2 Front St'))
            ->assertSessionMissing('address_add_another');

        $this->assertSame(3, $this->customer->addresses()->count());
        $this->assertSame(1, $this->customer->addresses()->where('is_billing', true)->count(), 'new addresses do not take over billing');

        $this->actingAs($this->staff)
            ->withSession(['address_add_another' => true])
            ->get($this->url('v2.customers.show', ['tab' => 'addresses']))
            ->assertOk()
            ->assertSee('1 Front St')
            ->assertSee('2 Front St')
            ->assertSee('addAnother: true', false);
    }

    public function test_address_validation_and_another_customers_address_is_not_found(): void
    {
        $this->actingAs($this->staff)
            ->post($this->url('v2.customers.addresses.store'), ['address_1' => '1 Dock Rd'])
            ->assertSessionHasErrors(['city', 'state', 'postal_code', 'country'])
            ->assertSessionDoesntHaveErrors('address_1');
        $this->assertSame(1, $this->customer->addresses()->count());

        $other = app(CustomerService::class)->createCustomer([
            'company_id' => $this->company->id, 'name' => 'Boscus', 'address_1' => '9 Mill Rd', 'city' => 'Quebec',
            'state' => 'QC', 'postal_code' => 'G1K', 'country' => 'CA', 'currency' => 'CAD', 'default_billing_option' => 'shipper',
        ]);
        $foreignAddress = $other->billingAddress;

        $this->actingAs($this->staff)
            ->put($this->url('v2.customers.addresses.update', ['address' => $foreignAddress->id]), ['address_1' => 'Hijack St', 'city' => 'y', 'state' => 'QC', 'postal_code' => 'G1K', 'country' => 'CA'])
            ->assertNotFound();
        $this->actingAs($this->staff)
            ->delete($this->url('v2.customers.addresses.destroy', ['address' => $foreignAddress->id]))
            ->assertNotFound();

        $this->assertSame('9 Mill Rd', $foreignAddress->fresh()->address_1);
    }

    // ── People ─────────────────────────────────────────────────────────────

    public function test_person_can_be_added_edited_and_removed(): void
    {
        $this->actingAs($this->staff)
            ->post($this->url('v2.customers.contacts.store'), $this->contactPayload())
            ->assertSessionHasNoErrors()
            ->assertRedirect($this->url('v2.customers.show', ['tab' => 'people']));

        $contact = $this->customer->contacts()->with('phones')->firstOrFail();
        $this->assertSame('Morgan Lee', $contact->name);
        $this->assertSame('12', $contact->phones->first()->ext);
        $this->assertFalse($contact->portal_access);

        $this->actingAs($this->staff)->put($this->url('v2.customers.contacts.update', ['contact' => $contact->id]), $this->contactPayload([
            'job_title' => 'Director of Logistics',
            'cc_on_invoices' => '1',
            'phones' => [['type' => 'mobile', 'number' => '647-555-0199', 'ext' => ''], ['type' => 'fax', 'number' => '416-555-0102', 'ext' => '']],
            'email_notifications' => ['order_delivered' => '1'],
        ]))->assertSessionHasNoErrors();

        $contact->refresh()->load('phones');
        $this->assertSame('Director of Logistics', $contact->job_title);
        $this->assertTrue($contact->cc_on_invoices);
        $this->assertEqualsCanonicalizing(['mobile', 'fax'], $contact->phones->pluck('type')->all());
        $this->assertTrue($contact->preferences()['order_delivered']['email']);
        $this->assertFalse($contact->preferences()['order_quoted']['email']);

        $this->actingAs($this->staff)
            ->delete($this->url('v2.customers.contacts.destroy', ['contact' => $contact->id]))
            ->assertRedirect($this->url('v2.customers.show', ['tab' => 'people']));
        $this->assertSoftDeleted('customer_contacts', ['id' => $contact->id]);

        $this->actingAs($this->staff)
            ->get($this->url('v2.customers.show', ['tab' => 'people']))
            ->assertOk()
            ->assertDontSee('morgan@apexfreight.com');

        $this->actingAs($this->staff)
            ->put($this->url('v2.customers.contacts.update', ['contact' => $contact->id]), $this->contactPayload())
            ->assertNotFound();
    }

    public function test_leaving_the_password_blank_keeps_it_and_portal_access_can_be_revoked(): void
    {
        $contact = CustomerContact::create([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'first_name' => 'Pat',
            'email' => 'pat@apexfreight.com',
            'password' => Hash::make('Original!Pass1'),
            'portal_access' => true,
        ]);
        $originalHash = $contact->password;

        $this->actingAs($this->staff)->put($this->url('v2.customers.contacts.update', ['contact' => $contact->id]), [
            'first_name' => 'Pat',
            'email' => 'pat@apexfreight.com',
            'portal_access' => '1',
            'password' => '',
            'password_confirmation' => '',
        ])->assertSessionHasNoErrors();
        $this->assertSame($originalHash, $contact->fresh()->password);

        // Signed in to the portal, then staff turn portal access off.
        $this->actingAs($contact, 'customer')->get(route('portal.dashboard', ['company' => $this->company->slug]))->assertOk();

        $this->actingAs($this->staff)->put($this->url('v2.customers.contacts.update', ['contact' => $contact->id]), [
            'first_name' => 'Pat',
            'email' => 'pat@apexfreight.com',
        ])->assertSessionHasNoErrors();

        $this->actingAs($contact->fresh(), 'customer')
            ->get(route('portal.dashboard', ['company' => $this->company->slug]))
            ->assertRedirect(route('portal.login', ['company' => $this->company->slug]));
    }

    public function test_person_validation(): void
    {
        $this->actingAs($this->staff)->post($this->url('v2.customers.contacts.store'), [
            'first_name' => '',
            'send_invoices' => '1',
            'phones' => [['type' => 'pager', 'number' => '555']],
            'portal_access' => '1',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors(['first_name', 'email', 'phones.0.type', 'password']);

        $this->assertSame(0, $this->customer->contacts()->count());
    }

    // ── Commodities ────────────────────────────────────────────────────────

    public function test_commodity_can_be_added_edited_and_deleted(): void
    {
        $payload = ['description' => 'Coconut Oil', 'type' => 'skid', 'measurement_unit' => 'cm_kg', 'weight' => '15000', 'linear_feet' => '1', 'length' => '40', 'width' => '48', 'height' => '40', 'freight_class' => '', 'nmfc' => '', 'sku' => 'CO-01'];

        $this->actingAs($this->staff)
            ->post($this->url('v2.customers.commodities.store'), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect($this->url('v2.customers.show', ['tab' => 'commodities']));

        $commodity = $this->customer->commodities()->firstOrFail();
        $this->assertSame('cm_kg', $commodity->measurement_unit);
        $this->assertNull($commodity->freight_class);
        $this->assertSame(15000.0, $commodity->weight);

        $this->actingAs($this->staff)
            ->put($this->url('v2.customers.commodities.update', ['commodity' => $commodity->id]), array_merge($payload, ['weight' => '14500', 'freight_class' => '77.5', 'volume' => '']))
            ->assertSessionHasNoErrors();
        $this->assertSame('77.5', $commodity->fresh()->freight_class);
        $this->assertSame(14500.0, $commodity->fresh()->weight);

        $this->actingAs($this->staff)
            ->get($this->url('v2.customers.show', ['tab' => 'commodities', 'q' => 'Coconut']))
            ->assertOk()->assertSee('Coconut Oil');

        $this->actingAs($this->staff)
            ->delete($this->url('v2.customers.commodities.destroy', ['commodity' => $commodity->id]));
        $this->assertModelMissing($commodity);
    }

    public function test_commodity_validation_template_and_scoping(): void
    {
        $this->actingAs($this->staff)
            ->post($this->url('v2.customers.commodities.store'), ['description' => '', 'measurement_unit' => 'furlongs', 'freight_class' => '999', 'weight' => '-3'])
            ->assertSessionHasErrors(['description', 'measurement_unit', 'freight_class', 'weight']);

        $template = $this->actingAs($this->staff)->get($this->url('v2.customers.commodities.template'));
        $template->assertOk();
        $this->assertStringStartsWith('description,type,unit,volume,weight', $template->streamedContent());

        $other = Customer::create(['company_id' => $this->company->id, 'name' => 'Boscus', 'short_code' => 'B34F', 'is_active' => true, 'is_deleted' => false]);
        $foreign = CustomerCommodity::create(['company_id' => $this->company->id, 'customer_id' => $other->id, 'description' => 'Lumber', 'measurement_unit' => 'in_lbs']);

        $this->actingAs($this->staff)
            ->delete($this->url('v2.customers.commodities.destroy', ['commodity' => $foreign->id]))
            ->assertNotFound();
        $this->assertModelExists($foreign);
    }

    // ── Accessorials & accounting ──────────────────────────────────────────

    public function test_accessorials_can_be_cleared_and_restored(): void
    {
        $van = Accessorial::create(['company_id' => $this->company->id, 'name' => 'Van']);

        $this->actingAs($this->staff)->put($this->url('v2.customers.accessorials.update'), [])->assertSessionHasNoErrors();
        $this->assertSame(0, $this->customer->accessorials()->count());

        $this->actingAs($this->staff)->put($this->url('v2.customers.accessorials.update'), ['accessorial_ids' => [$van->id]]);
        $this->assertSame([$van->id], $this->customer->accessorials()->pluck('accessorials.id')->all());
    }

    public function test_invoice_settings_update_in_place_and_limit_recipients(): void
    {
        $url = $this->url('v2.customers.billing-settings.update');

        $this->actingAs($this->staff)->put($url, ['invoice_terms' => 'net_15', 'bulk_send_method' => 'individual', 'recipients' => ['a@apex.com']])->assertSessionHasNoErrors();
        $this->actingAs($this->staff)->put($url, ['invoice_terms' => 'net_45', 'bulk_send_method' => 'manual', 'combine_documents' => '1'])->assertSessionHasNoErrors();

        $this->assertSame(1, $this->customer->billingSettings()->count());
        $settings = $this->customer->billingSettings()->first();
        $this->assertSame('net_45', $settings->invoice_terms);
        $this->assertSame([], $settings->recipients);
        $this->assertTrue($settings->combine_documents);

        $this->actingAs($this->staff)->put($url, [
            'bulk_send_method' => 'individual',
            'invoice_terms' => 'net_999',
            'recipients' => ['1@a.com', '2@a.com', '3@a.com', '4@a.com', '5@a.com', '6@a.com'],
        ])->assertSessionHasErrors(['recipients', 'invoice_terms']);
    }

    // ── Permissions ────────────────────────────────────────────────────────

    public function test_staff_without_customer_permissions_cannot_change_anything(): void
    {
        $viewer = $this->makeUser(superAdmin: false);
        $dashboard = route('v2.dashboard', ['company' => $this->company->slug]);

        $this->actingAs($viewer)->post(route('v2.customers.store', ['company' => $this->company->slug]), ['name' => 'Sneaky'])->assertRedirect($dashboard);
        $this->actingAs($viewer)->post($this->url('v2.customers.contacts.store'), $this->contactPayload())->assertRedirect($dashboard);
        $this->actingAs($viewer)->post($this->url('v2.customers.commodities.store'), ['description' => 'x', 'measurement_unit' => 'in_lbs'])->assertRedirect($dashboard);
        $this->actingAs($viewer)->delete($this->url('v2.customers.destroy'))->assertRedirect($dashboard);
        $this->actingAs($viewer)->get(route('v2.customers.index', ['company' => $this->company->slug]))->assertRedirect($dashboard);

        $this->assertSame(1, Customer::count());
        $this->assertSame(0, $this->customer->contacts()->count());
        $this->assertSame(0, $this->customer->commodities()->count());
        $this->assertFalse($this->customer->fresh()->is_deleted);
    }

    public function test_customer_with_orders_keeps_its_order_after_being_marked_inactive_from_details(): void
    {
        Order::create([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'INVO-AFSL-36',
            'order_type' => 'point_to_point',
            'status' => 'draft',
        ]);

        $this->actingAs($this->staff)->get($this->url('v2.customers.show'))->assertOk()->assertSee("This customer can't be deleted");

        $this->actingAs($this->staff)->delete($this->url('v2.customers.destroy'));
        $this->assertFalse($this->customer->fresh()->is_deleted);
    }

    public function test_delete_confirmations_use_the_app_dialog_not_the_browser_popup(): void
    {
        $this->customer->contacts()->create(['company_id' => $this->company->id, 'first_name' => 'Pat', 'email' => 'pat@apexfreight.com']);
        $this->customer->commodities()->create(['company_id' => $this->company->id, 'description' => 'Coconut Oil', 'measurement_unit' => 'in_lbs']);
        $this->customer->addresses()->create([
            'company_id' => $this->company->id, 'company_name' => $this->customer->name, 'address_1' => '9 Dock Rd', 'city' => 'Toronto',
        ]);

        foreach (['people' => 'confirm-person-delete', 'addresses' => 'confirm-address-delete', 'commodities' => 'confirm-commodity-delete'] as $tab => $modal) {
            $this->actingAs($this->staff)
                ->get($this->url('v2.customers.show', ['tab' => $tab]))
                ->assertOk()
                ->assertSee($modal)
                ->assertDontSee('return confirm(', false);
        }
    }

    public function test_adding_a_commodity_keeps_the_add_row_open_for_the_next_one(): void
    {
        $this->actingAs($this->staff)
            ->post($this->url('v2.customers.commodities.store'), ['description' => 'Steel Coil', 'measurement_unit' => 'in_lbs'])
            ->assertSessionHas('commodity_keep_adding', true);

        $this->actingAs($this->staff)
            ->withSession(['commodity_keep_adding' => true])
            ->get($this->url('v2.customers.show', ['tab' => 'commodities']))
            ->assertOk()
            ->assertSee("editing: 'new'", false)
            ->assertSee('Steel Coil');

        // Editing an existing row does not reopen the add row.
        $commodity = $this->customer->commodities()->firstOrFail();
        $this->actingAs($this->staff)
            ->put($this->url('v2.customers.commodities.update', ['commodity' => $commodity->id]), ['description' => 'Steel Coil XL', 'measurement_unit' => 'in_lbs'])
            ->assertSessionMissing('commodity_keep_adding');
    }

    public function test_bulk_delete_removes_only_the_selected_commodities_of_this_customer(): void
    {
        $make = fn (Customer $customer, string $description) => CustomerCommodity::create([
            'company_id' => $this->company->id, 'customer_id' => $customer->id, 'description' => $description, 'measurement_unit' => 'in_lbs',
        ]);
        [$oil, $coil, $lumber] = [$make($this->customer, 'Coconut Oil'), $make($this->customer, 'Steel Coil'), $make($this->customer, 'Lumber')];
        $other = Customer::create(['company_id' => $this->company->id, 'name' => 'Boscus', 'short_code' => 'B34F', 'is_active' => true, 'is_deleted' => false]);
        $foreign = $make($other, 'Plywood');

        $this->actingAs($this->staff)
            ->delete($this->url('v2.customers.commodities.bulk-destroy'), ['ids' => [$oil->id, $coil->id, $foreign->id]])
            ->assertRedirect($this->url('v2.customers.show', ['tab' => 'commodities']));

        $this->assertModelMissing($oil);
        $this->assertModelMissing($coil);
        $this->assertModelExists($lumber);
        $this->assertModelExists($foreign);

        $this->actingAs($this->staff)
            ->delete($this->url('v2.customers.commodities.bulk-destroy'), [])
            ->assertSessionHasErrors('ids');
        $this->assertModelExists($lumber);
    }
}
