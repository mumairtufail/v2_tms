<?php

namespace Tests\Feature\Portal;

use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerPortalSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Customer $customer;

    protected CustomerContact $contact;

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
            'is_active' => true,
            'is_deleted' => false,
            'location_sharing' => 'Do not share',
            'default_billing_option' => 'shipper',
            'network_customer' => false,
        ]);

        $this->contact = CustomerContact::create([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'first_name' => 'Pat',
            'last_name' => 'Portal',
            'email' => 'portal@customer.com',
            'password' => Hash::make('portal-password'),
            'portal_access' => true,
        ]);
    }

    public function test_person_can_view_settings_page(): void
    {
        $response = $this->actingAs($this->contact, 'customer')
            ->get(route('portal.settings', ['company' => $this->company->slug]));

        $response->assertStatus(200);
        $response->assertSee('Profile Information');
        $response->assertSee('Portal Preferences');
        $response->assertSee('Pat Portal');
        $response->assertSee('Portal Customer Inc');
    }

    public function test_person_can_update_their_profile(): void
    {
        $response = $this->actingAs($this->contact, 'customer')
            ->patch(route('portal.settings.profile.update', ['company' => $this->company->slug]), [
                'first_name' => 'Updated',
                'last_name' => 'Person',
                'job_title' => 'Dispatcher',
                'email' => 'Updated@Customer.com',
            ]);

        $response->assertRedirect(route('portal.settings', ['company' => $this->company->slug]));

        $this->contact->refresh();
        $this->assertSame('Updated Person', $this->contact->name);
        $this->assertSame('Dispatcher', $this->contact->job_title);
        $this->assertSame('updated@customer.com', $this->contact->email);
    }

    public function test_email_used_by_another_portal_person_is_rejected(): void
    {
        CustomerContact::create([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'first_name' => 'Taken',
            'email' => 'taken@customer.com',
            'password' => Hash::make('portal-password'),
            'portal_access' => true,
        ]);

        $this->actingAs($this->contact, 'customer')
            ->patch(route('portal.settings.profile.update', ['company' => $this->company->slug]), [
                'first_name' => 'Pat',
                'email' => 'taken@customer.com',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_person_can_update_customer_preferences(): void
    {
        $response = $this->actingAs($this->contact, 'customer')
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
        $response = $this->actingAs($this->contact, 'customer')
            ->get(route('portal.profile', ['company' => $this->company->slug]));

        $response->assertRedirect(route('portal.settings', ['company' => $this->company->slug]));
    }
}
