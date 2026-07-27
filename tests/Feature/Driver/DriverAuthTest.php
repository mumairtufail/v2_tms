<?php

namespace Tests\Feature\Driver;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DriverAuthTest extends TestCase
{
    use DatabaseTransactions;

    private function makeCompanyWithDriverRole(): array
    {
        $company = Company::create([
            'name' => 'Driver Test Co '.uniqid(),
            'slug' => 'driver-test-co-'.uniqid(),
            'address' => '1 Test St',
            'is_active' => true,
            'phone' => '555-0000',
            'is_deleted' => false,
        ]);

        $driverRole = Role::create([
            'name' => 'driver',
            'company_id' => $company->id,
            'is_active' => true,
        ]);

        return [$company, $driverRole];
    }

    public function test_driver_can_login_and_receive_a_token()
    {
        [$company, $driverRole] = $this->makeCompanyWithDriverRole();

        $user = User::create([
            'f_name' => 'Dana',
            'l_name' => 'Driver',
            'email' => 'dana.driver.'.uniqid().'@example.com',
            'password' => bcrypt('password123'),
            'company_id' => $company->id,
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $user->roles()->attach($driverRole->id);

        $response = $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'email', 'company' => ['id', 'name']]]);

        $this->assertEquals($company->id, $response->json('user.company.id'));
    }

    public function test_wrong_password_is_rejected()
    {
        [$company] = $this->makeCompanyWithDriverRole();

        $user = User::create([
            'f_name' => 'Dana',
            'l_name' => 'Driver',
            'email' => 'dana.wrong.'.uniqid().'@example.com',
            'password' => bcrypt('password123'),
            'company_id' => $company->id,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $response = $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'not-the-password',
        ]);

        $response->assertStatus(422);
    }

    public function test_non_driver_role_is_rejected()
    {
        [$company] = $this->makeCompanyWithDriverRole();

        $adminRole = Role::create([
            'name' => 'admin',
            'company_id' => $company->id,
            'is_active' => true,
        ]);

        $user = User::create([
            'f_name' => 'Alex',
            'l_name' => 'Admin',
            'email' => 'alex.admin.'.uniqid().'@example.com',
            'password' => bcrypt('password123'),
            'company_id' => $company->id,
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $user->roles()->attach($adminRole->id);

        $response = $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJsonFragment(['message' => 'This account is not set up as a driver.']);
    }

    public function test_inactive_driver_is_rejected()
    {
        [$company, $driverRole] = $this->makeCompanyWithDriverRole();

        $user = User::create([
            'f_name' => 'Ina',
            'l_name' => 'Active',
            'email' => 'ina.inactive.'.uniqid().'@example.com',
            'password' => bcrypt('password123'),
            'company_id' => $company->id,
            'is_active' => false,
            'is_deleted' => false,
        ]);
        $user->roles()->attach($driverRole->id);

        $response = $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJsonFragment(['message' => 'This account is inactive. Contact your dispatcher.']);
    }

    public function test_authenticated_driver_can_fetch_and_logout()
    {
        [$company, $driverRole] = $this->makeCompanyWithDriverRole();

        $user = User::create([
            'f_name' => 'Dana',
            'l_name' => 'Driver',
            'email' => 'dana.me.'.uniqid().'@example.com',
            'password' => bcrypt('password123'),
            'company_id' => $company->id,
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $user->roles()->attach($driverRole->id);

        $token = $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->json('token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/driver/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/driver/logout')
            ->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => (int) explode('|', $token)[0],
        ]);

        // Sanctum's guard instance is memoized for the lifetime of the test's
        // app container, so it must be forced to re-resolve before the next
        // simulated request will actually re-check the (now deleted) token.
        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/driver/me')
            ->assertStatus(401);
    }
}
