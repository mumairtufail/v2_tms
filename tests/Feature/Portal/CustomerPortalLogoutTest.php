<?php

namespace Tests\Feature\Portal;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPortalLogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_logout(): void
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
            'customer_email' => 'portal@customer.com',
            'portal' => true,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($customer, 'customer')
            ->post(route('portal.logout', ['company' => $company->slug]));

        $this->assertGuest('customer');
        $response->assertRedirect(route('portal.login', ['company' => $company->slug]));
    }
}
