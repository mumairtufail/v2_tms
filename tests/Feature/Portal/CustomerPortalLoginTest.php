<?php

namespace Tests\Feature\Portal;

use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerPortalLoginTest extends TestCase
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
            'short_code' => 'PCI1',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $this->contact = $this->makePerson('portal@customer.com');
    }

    private function makePerson(string $email, array $overrides = []): CustomerContact
    {
        return CustomerContact::create(array_merge([
            'company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'first_name' => 'Pat',
            'last_name' => 'Portal',
            'email' => $email,
            'password' => Hash::make('portal-password'),
            'portal_access' => true,
        ], $overrides));
    }

    private function attemptLogin(string $email, string $password = 'portal-password')
    {
        return $this->post(route('portal.login', ['company' => $this->company->slug]), [
            'email' => $email,
            'password' => $password,
        ]);
    }

    public function test_portal_login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('portal.login', ['company' => $this->company->slug]));

        $response->assertStatus(200);
    }

    public function test_person_can_authenticate_with_their_password(): void
    {
        $response = $this->attemptLogin('portal@customer.com');

        $this->assertAuthenticatedAs($this->contact, 'customer');
        $response->assertRedirect(route('portal.dashboard', ['company' => $this->company->slug]));
        $this->assertNotNull($this->contact->fresh()->last_login_at);
    }

    public function test_email_is_matched_case_insensitively(): void
    {
        $this->attemptLogin('Portal@Customer.com');

        $this->assertAuthenticatedAs($this->contact, 'customer');
    }

    public function test_person_cannot_authenticate_with_wrong_password(): void
    {
        $this->attemptLogin('portal@customer.com', 'wrong-password');

        $this->assertGuest('customer');
    }

    public function test_person_without_portal_access_cannot_login(): void
    {
        $this->contact->update(['portal_access' => false]);

        $this->attemptLogin('portal@customer.com');

        $this->assertGuest('customer');
    }

    public function test_person_at_inactive_customer_cannot_login(): void
    {
        $this->customer->update(['is_active' => false]);

        $this->attemptLogin('portal@customer.com');

        $this->assertGuest('customer');
    }

    public function test_person_without_password_cannot_login(): void
    {
        $this->contact->update(['password' => null]);

        $this->attemptLogin('portal@customer.com');

        $this->assertGuest('customer');
    }

    public function test_removed_person_cannot_login(): void
    {
        $this->contact->delete();

        $this->attemptLogin('portal@customer.com');

        $this->assertGuest('customer');
    }

    public function test_two_people_at_one_customer_each_have_their_own_login(): void
    {
        $second = $this->makePerson('second@customer.com', ['first_name' => 'Sam']);

        $this->attemptLogin('second@customer.com');

        $this->assertAuthenticatedAs($second, 'customer');
    }
}
