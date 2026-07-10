<?php

namespace Tests\Feature\Portal;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerPortalLoginTest extends TestCase
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
            'password' => Hash::make('portal-password'),
            'short_code' => 'PCI',
            'portal' => true,
            'is_active' => true,
            'is_deleted' => false,
        ]);
    }

    public function test_portal_login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('portal.login', ['company' => $this->company->slug]));

        $response->assertStatus(200);
    }

    public function test_customer_can_authenticate_with_their_password(): void
    {
        $response = $this->post(route('portal.login', ['company' => $this->company->slug]), [
            'email' => $this->customer->customer_email,
            'password' => 'portal-password',
        ]);

        $this->assertAuthenticated('customer');
        $response->assertRedirect(route('portal.dashboard', ['company' => $this->company->slug]));
    }

    public function test_customer_cannot_authenticate_with_wrong_password(): void
    {
        $this->post(route('portal.login', ['company' => $this->company->slug]), [
            'email' => $this->customer->customer_email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest('customer');
    }

    public function test_customer_without_portal_flag_cannot_login(): void
    {
        $this->customer->update(['portal' => false]);

        $this->post(route('portal.login', ['company' => $this->company->slug]), [
            'email' => $this->customer->customer_email,
            'password' => 'portal-password',
        ]);

        $this->assertGuest('customer');
    }

    public function test_inactive_customer_cannot_login(): void
    {
        $this->customer->update(['is_active' => false]);

        $this->post(route('portal.login', ['company' => $this->company->slug]), [
            'email' => $this->customer->customer_email,
            'password' => 'portal-password',
        ]);

        $this->assertGuest('customer');
    }

    public function test_customer_without_password_cannot_login(): void
    {
        $this->customer->update(['password' => null]);

        $this->post(route('portal.login', ['company' => $this->company->slug]), [
            'email' => $this->customer->customer_email,
            'password' => 'portal-password',
        ]);

        $this->assertGuest('customer');
    }
}
