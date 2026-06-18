<?php

namespace Tests\Feature;

use App\Models\ActivityLogs;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ActivityLogPageTest extends TestCase
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

        $permission = Permission::create(['name' => 'logs', 'is_active' => true]);
        $role = Role::create([
            'name' => 'Admin',
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);
        $role->permissions()->attach($permission->id, [
            'create' => false,
            'update' => false,
            'view' => true,
            'delete' => false,
            'logs' => true,
            'others' => false,
        ]);
        $this->user->roles()->attach($role->id);
    }

    public function test_company_activity_logs_page_is_accessible(): void
    {
        ActivityLogs::create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'action' => 'v2.orders.store',
            'data' => ['description' => 'Created order'],
            'method' => 'POST',
            'is_successful' => true,
        ]);

        $this->actingAs($this->user)
            ->get("/{$this->company->slug}/activity-logs")
            ->assertOk()
            ->assertSee('Activity Logs')
            ->assertSee('Created order');
    }

    public function test_admin_activity_logs_page_is_accessible(): void
    {
        $superAdmin = User::create([
            'company_id' => $this->company->id,
            'f_name' => 'Super',
            'l_name' => 'Admin',
            'email' => 'super@test.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'is_super_admin' => true,
        ]);

        ActivityLogs::create([
            'user_id' => $superAdmin->id,
            'company_id' => $this->company->id,
            'action' => 'auth.login.success',
            'data' => ['description' => 'Staff user logged in'],
            'method' => 'POST',
            'is_successful' => true,
        ]);

        $this->actingAs($superAdmin)
            ->get('/admin/logs')
            ->assertOk()
            ->assertSee('Activity Logs')
            ->assertSee('Staff user logged in')
            ->assertSee('Test Freight Co');
    }
}
