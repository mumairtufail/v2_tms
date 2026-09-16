<?php

namespace Tests\Feature\Portal;

use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerPortalLogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_person_can_logout(): void
    {
        $company = Company::create([
            'name' => 'Test Freight Co',
            'slug' => 'test-freight-co',
            'shortcode' => 'TEST',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $customer = Customer::create([
            'company_id' => $company->id,
            'name' => 'Portal Customer Inc',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $contact = CustomerContact::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'first_name' => 'Pat',
            'email' => 'portal@customer.com',
            'password' => Hash::make('portal-password'),
            'portal_access' => true,
        ]);

        $response = $this->actingAs($contact, 'customer')
            ->post(route('portal.logout', ['company' => $company->slug]));

        $this->assertGuest('customer');
        $response->assertRedirect(route('portal.login', ['company' => $company->slug]));
    }
}
