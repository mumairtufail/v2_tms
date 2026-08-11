<?php

namespace Tests\Feature;

use App\Models\ActivityLogs;
use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected User $user;

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

        $this->user = User::create([
            'company_id' => $this->company->id,
            'f_name' => 'Admin',
            'l_name' => 'User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'is_super_admin' => false,
        ]);
    }

    public function test_successful_staff_login_creates_activity_log(): void
    {
        $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ])->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'action' => 'auth.login.success',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_failed_staff_login_creates_activity_log(): void
    {
        $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'auth.login.failed',
            'is_successful' => false,
        ]);
    }

    public function test_portal_login_creates_customer_activity_log(): void
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'Portal Customer',
            'customer_email' => 'customer@test.com',
            'password' => Hash::make('password'),
            'portal' => true,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $this->post("/{$this->company->slug}/portal/login", [
            'email' => 'customer@test.com',
            'password' => 'password',
        ])->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'customer_id' => $customer->id,
            'company_id' => $this->company->id,
            'action' => 'portal.login.success',
        ]);
    }

}
