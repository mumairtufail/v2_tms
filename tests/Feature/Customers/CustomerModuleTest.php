<?php

namespace Tests\Feature\Customers;

use App\Http\Controllers\V2\CustomerController;
use App\Models\Accessorial;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerContact;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderFormDataBuilder;
use App\Support\AccessorialCategories;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerModuleTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = $this->makeCompany('Innovations Freight');
        $this->staff = User::create([
            'company_id' => $this->company->id,
            'name' => 'Dana Dispatch',
            'f_name' => 'Dana',
            'l_name' => 'Dispatch',
            'email' => 'dana.' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'is_active' => true,
            'is_deleted' => false,
            'is_super_admin' => true,
        ]);
    }

    private function makeCompany(string $name): Company
    {
        $suffix = uniqid();

        return Company::create([
            'name' => $name,
            'slug' => str()->slug($name) . '-' . $suffix,
            'shortcode' => 'INV',
            'is_active' => true,
            'is_deleted' => false,
        ]);
    }

    private function makeCustomer(array $overrides = [], ?Company $company = null): Customer
    {
        return Customer::create(array_merge([
            'company_id' => ($company ?? $this->company)->id,
            'name' => 'Atlas Granite Inc',
            'short_code' => 'AGI3',
            'currency' => 'CAD',
            'is_active' => true,
            'is_deleted' => false,
        ], $overrides));
    }

    private function url(string $name, array $params = []): string
    {
        return route($name, array_merge(['company' => $this->company->slug], $params));
    }

    public function test_creating_a_customer_adds_billing_address_short_code_and_accessorials(): void
    {
        $pickup = Accessorial::create(['company_id' => $this->company->id, 'name' => 'Inside pickup']);
        Accessorial::create(['company_id' => $this->company->id, 'name' => 'Inside delivery']);
        Accessorial::create(['company_id' => $this->company->id, 'name' => 'Reefer']);

        $response = $this->actingAs($this->staff)->post($this->url('v2.customers.store'), [
            'name' => '7 Mountain Logistics',
            'address_1' => '19265 Airport Way',
            'address_2' => 'Unit 211',
            'city' => 'Pitt Meadows',
            'state' => 'BC',
            'postal_code' => 'V3Y 0G5',
            'country' => 'ca',
            'currency' => 'CAD',
            'customer_type' => 'other',
            'quote_required' => '1',
            'default_billing_option' => 'third_party',
        ]);

        $customer = Customer::where('name', '7 Mountain Logistics')->firstOrFail();

        $response->assertRedirect($this->url('v2.customers.show', ['customer' => $customer->id, 'tab' => 'details']));
        $this->assertSame('7ML1', $customer->short_code);
        $this->assertTrue($customer->quote_required);
        $this->assertSame(3, $customer->accessorials()->count());
        $this->assertSame(AccessorialCategories::ORIGIN, $pickup->fresh()->category);

        $billing = $customer->billingAddress;
        $this->assertNotNull($billing);
        $this->assertSame('CA', $billing->country);
        $this->assertSame('Pitt Meadows', $customer->fresh()->city, 'legacy address columns stay in sync');
    }

    public function test_short_codes_skip_ones_already_taken(): void
    {
        $this->makeCustomer(['name' => '7 Mountain Logistics', 'short_code' => '7ML1']);

        $this->actingAs($this->staff)
            ->getJson($this->url('v2.customers.generate-short-code', ['name' => '7 Mountain Logistics']))
            ->assertJsonPath('short_code', '7ML2');
    }

    public function test_every_tab_renders(): void
    {
        $customer = $this->makeCustomer();

        foreach (CustomerController::TABS as $tab) {
            $this->actingAs($this->staff)
                ->get($this->url('v2.customers.show', ['customer' => $customer->id, 'tab' => $tab]))
                ->assertOk();
        }

        $this->actingAs($this->staff)->get($this->url('v2.customers.index'))->assertOk()->assertSee('Atlas Granite Inc');
    }

    public function test_customer_from_another_company_is_not_found(): void
    {
        $other = $this->makeCustomer(['name' => 'Other Tenant Co'], $this->makeCompany('Other Freight'));

        $this->actingAs($this->staff)
            ->get($this->url('v2.customers.show', ['customer' => $other->id]))
            ->assertNotFound();
    }

    public function test_customer_with_orders_cannot_be_deleted(): void
    {
        $customer = $this->makeCustomer();
        Order::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'order_number' => 'INV-AGI3-1',
            'order_type' => 'point_to_point',
            'status' => 'draft',
        ]);

        $this->actingAs($this->staff)->delete($this->url('v2.customers.destroy', ['customer' => $customer->id]));
        $this->assertFalse($customer->fresh()->is_deleted);

        $empty = $this->makeCustomer(['name' => 'No Orders Ltd', 'short_code' => 'NOL1']);
        $this->actingAs($this->staff)->delete($this->url('v2.customers.destroy', ['customer' => $empty->id]))
            ->assertRedirect($this->url('v2.customers.index'));
        $this->assertTrue($empty->fresh()->is_deleted);
    }

    public function test_customer_with_orders_explains_why_and_can_be_marked_inactive(): void
    {
        $customer = $this->makeCustomer(['name' => 'Apex Freight Solutions Ltd', 'short_code' => 'AFSL']);
        Order::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'order_number' => 'INVO-AFSL-36',
            'order_type' => 'point_to_point',
            'status' => 'draft',
        ]);

        $this->actingAs($this->staff)->get($this->url('v2.customers.index'))
            ->assertOk()
            ->assertSee("This customer can't be deleted")
            ->assertSee('Mark inactive');

        $this->actingAs($this->staff)
            ->get($this->url('v2.customers.show', ['customer' => $customer->id]))
            ->assertSee('Mark inactive');

        $this->actingAs($this->staff)
            ->patch($this->url('v2.customers.deactivate', ['customer' => $customer->id]), ['return' => 'details'])
            ->assertRedirect($this->url('v2.customers.show', ['customer' => $customer->id, 'tab' => 'details']));

        $customer->refresh();
        $this->assertFalse($customer->is_active);
        $this->assertFalse($customer->is_deleted);
        $this->assertSame(1, $customer->orders()->count());
    }

    public function test_billing_address_moves_and_cannot_be_deleted(): void
    {
        $customer = $this->makeCustomer();
        $address = fn (array $overrides = []) => array_merge([
            'address_1' => '100 Quarry Rd',
            'city' => 'Barre',
            'state' => 'VT',
            'postal_code' => '05641',
            'country' => 'us',
        ], $overrides);

        $this->actingAs($this->staff)->post($this->url('v2.customers.addresses.store', ['customer' => $customer->id]), $address())->assertSessionHasNoErrors();
        $first = $customer->addresses()->firstOrFail();
        $this->assertTrue($first->is_billing, 'first address becomes the billing address');
        $this->assertSame('US', $first->country);
        $this->assertSame($customer->name, $first->company_name, 'the label defaults to the customer name');

        $this->actingAs($this->staff)->post($this->url('v2.customers.addresses.store', ['customer' => $customer->id]), $address([
            'address_1' => '1 State St',
            'city' => 'Montpelier',
            'is_billing' => '1',
        ]))->assertSessionHasNoErrors();

        $this->assertSame(1, $customer->addresses()->where('is_billing', true)->count());
        $this->assertFalse($first->fresh()->is_billing);
        $this->assertSame('Montpelier', $customer->fresh()->city);

        $billing = $customer->billingAddress()->firstOrFail();
        $this->actingAs($this->staff)->delete($this->url('v2.customers.addresses.destroy', ['customer' => $customer->id, 'address' => $billing->id]));
        $this->assertNotNull(CustomerAddress::find($billing->id));

        $this->actingAs($this->staff)->delete($this->url('v2.customers.addresses.destroy', ['customer' => $customer->id, 'address' => $first->id]));
        $this->assertSoftDeleted('customer_addresses', ['id' => $first->id]);
    }

    public function test_people_with_portal_access_need_a_password_and_can_sign_in(): void
    {
        $customer = $this->makeCustomer();
        $storeUrl = $this->url('v2.customers.contacts.store', ['customer' => $customer->id]);
        $person = [
            'first_name' => 'Rajeev',
            'last_name' => 'Singh',
            'email' => 'Rajeev@Go7mtn.com',
            'portal_access' => '1',
            'send_invoices' => '1',
            'phones' => [['type' => 'mobile', 'number' => '604-555-0100', 'ext' => ''], ['type' => 'office', 'number' => '', 'ext' => '']],
            'email_notifications' => ['order_booked' => '1', 'order_invoiced' => '1'],
        ];

        $this->actingAs($this->staff)->post($storeUrl, $person)->assertSessionHasErrors('password');

        $this->actingAs($this->staff)->post($storeUrl, $person + [
            'password' => 'Str0ng!Pass',
            'password_confirmation' => 'Str0ng!Pass',
        ])->assertSessionHasNoErrors();

        $contact = $customer->contacts()->firstOrFail();
        $this->assertSame('rajeev@go7mtn.com', $contact->email);
        $this->assertSame(1, $contact->phones()->count());
        $this->assertTrue($contact->preferences()['order_booked']['email']);
        $this->assertFalse($contact->preferences()['order_invoiced']['email'], 'events that never fire cannot opt in to email');

        auth()->guard('web')->logout();
        $this->post(route('portal.login', ['company' => $this->company->slug]), [
            'email' => 'rajeev@go7mtn.com',
            'password' => 'Str0ng!Pass',
        ]);
        $this->assertAuthenticatedAs($contact, 'customer');
    }

    public function test_a_broken_mail_setup_does_not_stop_a_person_being_added(): void
    {
        // Like production with no SMTP account: the .env fallback points at a server that isn't there
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 1]);

        $customer = $this->makeCustomer();

        $this->actingAs($this->staff)->post($this->url('v2.customers.contacts.store', ['customer' => $customer->id]), [
            'first_name' => 'Umar',
            'last_name' => 'Qayyum',
            'email' => 'umar@example.com',
            'portal_access' => '1',
            'password' => 'Str0ng!Pass',
            'password_confirmation' => 'Str0ng!Pass',
            'phones' => [['type' => 'mobile', 'number' => '', 'ext' => '']],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('umar@example.com', $customer->contacts()->firstOrFail()->email);
    }

    public function test_portal_email_must_be_unique_among_portal_people(): void
    {
        $customer = $this->makeCustomer();
        CustomerContact::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'first_name' => 'First',
            'email' => 'shared@atlas.com',
            'password' => Hash::make('x'),
            'portal_access' => true,
        ]);

        $this->actingAs($this->staff)->post($this->url('v2.customers.contacts.store', ['customer' => $customer->id]), [
            'first_name' => 'Second',
            'email' => 'shared@atlas.com',
            'portal_access' => '1',
            'password' => 'Str0ng!Pass',
            'password_confirmation' => 'Str0ng!Pass',
        ])->assertSessionHasErrors('email');
    }

    public function test_order_form_only_offers_the_customers_accessorials_from_its_company(): void
    {
        $customer = $this->makeCustomer();
        $allowed = Accessorial::create(['company_id' => $this->company->id, 'name' => 'Tailgate delivery']);
        $disabled = Accessorial::create(['company_id' => $this->company->id, 'name' => 'Reefer']);
        $otherTenant = Accessorial::create(['company_id' => $this->makeCompany('Other Freight')->id, 'name' => 'Tailgate delivery']);

        $this->actingAs($this->staff)
            ->put($this->url('v2.customers.accessorials.update', ['customer' => $customer->id]), ['accessorial_ids' => [$allowed->id]])
            ->assertSessionHasNoErrors();

        $order = Order::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'order_number' => 'INV-AGI3-2',
            'order_type' => 'point_to_point',
            'status' => 'draft',
        ]);

        $ids = app(OrderFormDataBuilder::class)->build($this->company, $order)['allAccessorials']->pluck('id')->all();

        $this->assertSame([$allowed->id], $ids);
        $this->assertNotContains($disabled->id, $ids);
        $this->assertNotContains($otherTenant->id, $ids);
    }

    public function test_another_companys_accessorial_cannot_be_enabled(): void
    {
        $customer = $this->makeCustomer();
        $foreign = Accessorial::create(['company_id' => $this->makeCompany('Other Freight')->id, 'name' => 'Van']);

        $this->actingAs($this->staff)
            ->put($this->url('v2.customers.accessorials.update', ['customer' => $customer->id]), ['accessorial_ids' => [$foreign->id]])
            ->assertSessionHasErrors('accessorial_ids.0');
    }

    public function test_commodity_csv_import_skips_invalid_rows_and_exports(): void
    {
        $customer = $this->makeCustomer();
        $csv = "Description,Type,Unit,Weight,Length,Width,Height,Class\n"
            . "Coconut Oil,Skid,in/lbs,15000,40,48,40,70\n"
            . ",skid,in/lbs,10,,,,\n"
            . "Steel Coil,skid,cm/kg,-5,,,,\n";

        $this->actingAs($this->staff)->post($this->url('v2.customers.commodities.import', ['customer' => $customer->id]), [
            'file' => UploadedFile::fake()->createWithContent('commodities.csv', $csv),
        ])->assertSessionHas('commodity_import_errors', fn ($errors) => count($errors) === 2);

        $this->assertSame(['Coconut Oil'], $customer->commodities()->pluck('description')->all());

        $export = $this->actingAs($this->staff)->get($this->url('v2.customers.commodities.export', ['customer' => $customer->id]));
        $export->assertOk();
        $this->assertStringContainsString('"Coconut Oil",skid,in/lbs,,15000,,40,48,40,70', $export->streamedContent());
    }

    public function test_invoice_settings_are_saved(): void
    {
        $customer = $this->makeCustomer();

        $this->actingAs($this->staff)->put($this->url('v2.customers.billing-settings.update', ['customer' => $customer->id]), [
            'invoice_terms' => 'net_30',
            'taxable' => '1',
            'recipients' => ['AP@atlas.com', '', 'billing@atlas.com'],
            'attach_proof_of_delivery' => '1',
            'bulk_send_method' => 'combined',
        ])->assertSessionHasNoErrors();

        $settings = $customer->billingSettings()->firstOrFail();
        $this->assertSame('net_30', $settings->invoice_terms);
        $this->assertSame(['ap@atlas.com', 'billing@atlas.com'], $settings->recipients);
        $this->assertTrue($settings->attach_proof_of_delivery);
        $this->assertFalse($settings->attach_commercial_invoice);
    }

    public function test_order_address_search_lists_customer_addresses_first(): void
    {
        $customer = $this->makeCustomer();
        $reefer = Accessorial::create(['company_id' => $this->company->id, 'name' => 'Appointment delivery']);
        $address = $customer->addresses()->create([
            'company_id' => $this->company->id,
            'company_name' => 'Atlas Granite Yard',
            'address_1' => '100 Quarry Rd',
            'city' => 'Barre',
            'open_time' => '06:30',
            'consignee_notes' => 'Call 30 minutes out',
        ]);
        $address->accessorials()->sync([$reefer->id]);

        $this->actingAs($this->staff)
            ->getJson($this->url('v2.contact-book.index', ['customer_id' => $customer->id]))
            ->assertOk()
            ->assertJsonPath('entries.0.source', 'customer')
            ->assertJsonPath('entries.0.opening_time', '06:30')
            ->assertJsonPath('entries.0.consignee_notes', 'Call 30 minutes out')
            ->assertJsonPath('entries.0.accessorial_ids', [(string) $reefer->id]);
    }
}
