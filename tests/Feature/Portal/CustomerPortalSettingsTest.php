<?php

namespace Tests\Feature\Portal;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPortalSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Test Freight Co',
            'slug' => 'test-freight-co',
            'shortcode' => 'TEST',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $this->customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Portal Customer Inc',
            'customer_email' => 'portal@customer.com',
            'portal' => true,
            'is_active' => true,
            'is_deleted' => false,
            'location_sharing' => 'Do not share',
            'default_billing_option' => 'shipper',
            'network_customer' => false,
        ]);
    }

    public function test_customer_can_view_settings_page(): void
    {
        $response = $this->actingAs($this->customer, 'customer')
            ->get(route('portal.settings', ['company' => $this->company->slug]));

        $response->assertStatus(200);
        $response->assertSee('Profile Information');
        $response->assertSee('Portal Preferences');
    }

    public function test_customer_can_update_profile(): void
    {
        $response = $this->actingAs($this->customer, 'customer')
            ->patch(route('portal.settings.profile.update', ['company' => $this->company->slug]), [
                'name' => 'Updated Customer Name',
                'customer_email' => 'updated@customer.com',
                'address' => '123 Main St',
                'city' => 'Chicago',
                'state' => 'IL',
                'postal_code' => '60601',
                'country' => 'US',
                'currency' => 'USD',
            ]);

        $response->assertRedirect(route('portal.settings', ['company' => $this->company->slug]));

        $this->customer->refresh();
        $this->assertSame('Updated Customer Name', $this->customer->name);
        $this->assertSame('updated@customer.com', $this->customer->customer_email);
        $this->assertSame('Chicago', $this->customer->city);
    }

    public function test_customer_can_update_preferences(): void
    {
        $response = $this->actingAs($this->customer, 'customer')
            ->patch(route('portal.settings.preferences.update', ['company' => $this->company->slug]), [
                'location_sharing' => 'approximate',
                'default_billing_option' => 'consignee',
                'network_customer' => '1',
            ]);

        $response->assertRedirect(route('portal.settings', ['company' => $this->company->slug]));

        $this->customer->refresh();
        $this->assertSame('approximate', $this->customer->location_sharing);
        $this->assertSame('consignee', $this->customer->default_billing_option);
        $this->assertTrue($this->customer->network_customer);
    }

    public function test_profile_redirects_to_settings(): void
    {
        $response = $this->actingAs($this->customer, 'customer')
            ->get(route('portal.profile', ['company' => $this->company->slug]));

        $response->assertRedirect(route('portal.settings', ['company' => $this->company->slug]));
    }
}
