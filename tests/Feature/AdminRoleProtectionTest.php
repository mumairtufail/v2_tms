<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The built-in Admin role keeps its name.
 *
 * Renaming it used to switch off every protection, because it was recognised
 * by name, and renaming it back was then refused as a reserved name.
 */
class AdminRoleProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected User $user;

    protected Role $admin;

    protected Role $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Riverstone Logistics', 'slug' => 'riverstone-logistics',
            'shortcode' => 'RIV', 'is_active' => true, 'is_deleted' => false,
        ]);

        $this->user = User::create([
            'company_id' => $this->company->id, 'f_name' => 'Company', 'l_name' => 'Admin',
            'email' => 'admin@riverstone.test', 'password' => Hash::make('password'),
            'status' => 'active', 'is_super_admin' => false,
        ]);

        $permission = Permission::firstOrCreate(['name' => 'roles'], ['is_active' => true]);

        $this->admin = Role::create(['name' => 'admin', 'company_id' => $this->company->id, 'is_active' => true, 'is_system' => true]);
        $this->admin->permissions()->attach($permission->id, [
            'view' => true, 'create' => true, 'update' => true, 'delete' => true, 'logs' => true, 'others' => true,
        ]);
        $this->user->roles()->attach($this->admin->id);

        $this->dispatcher = Role::create(['name' => 'Dispatcher', 'company_id' => $this->company->id, 'is_active' => true]);
    }

    protected function url(string $name, array $params = []): string
    {
        return route($name, array_merge(['company' => $this->company->slug], $params));
    }

    public function test_the_admin_role_cannot_be_renamed(): void
    {
        $this->actingAs($this->user)
            ->from($this->url('v2.roles.index'))
            ->put($this->url('v2.roles.update', ['role' => $this->admin->id]), ['name' => 'Adminss'])
            ->assertRedirect($this->url('v2.roles.index'))
            ->assertSessionHasErrors('name', null, 'roleForm')
            ->assertSessionHas('open_role_modal', 'edit-role-'.$this->admin->id);

        $this->assertSame('admin', $this->admin->fresh()->name);
    }

    public function test_saving_the_admin_role_with_its_own_name_is_not_an_error(): void
    {
        $this->actingAs($this->user)
            ->put($this->url('v2.roles.update', ['role' => $this->admin->id]), ['name' => ' Admin '])
            ->assertSessionHasNoErrors();

        $this->assertSame('admin', $this->admin->fresh()->name);
    }

    public function test_another_role_cannot_take_a_reserved_name(): void
    {
        foreach (['Admin', ' ADMIN ', 'Company Admin', 'super-admin'] as $name) {
            $this->actingAs($this->user)
                ->put($this->url('v2.roles.update', ['role' => $this->dispatcher->id]), ['name' => $name])
                ->assertSessionHasErrors('name', null, 'roleForm');
        }

        $this->assertSame('Dispatcher', $this->dispatcher->fresh()->name);
    }

    public function test_a_new_role_cannot_use_a_reserved_name(): void
    {
        $this->actingAs($this->user)
            ->post($this->url('v2.roles.store'), ['name' => 'Company Admin'])
            ->assertSessionHasErrors('name', null, 'roleForm')
            ->assertSessionHas('open_role_modal', 'create-role-modal');

        $this->assertDatabaseMissing('roles', ['company_id' => $this->company->id, 'name' => 'Company Admin']);
    }

    public function test_ordinary_roles_can_still_be_renamed(): void
    {
        $this->actingAs($this->user)
            ->put($this->url('v2.roles.update', ['role' => $this->dispatcher->id]), ['name' => 'Night Dispatch'])
            ->assertSessionHasNoErrors();

        $this->assertSame('Night Dispatch', $this->dispatcher->fresh()->name);
    }

    public function test_protection_follows_the_flag_not_the_name(): void
    {
        // Even if the name were changed underneath (old data, a direct query),
        // the flag keeps it protected.
        \DB::table('roles')->where('id', $this->admin->id)->update(['name' => 'Adminss']);
        $role = $this->admin->fresh();

        $this->assertTrue($role->isProtectedAdmin());
        $this->assertFalse($role->delete());
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_the_model_refuses_a_rename_from_any_caller(): void
    {
        $this->assertFalse($this->admin->update(['name' => 'Owner']));
        $this->assertSame('admin', $this->admin->fresh()->name);
    }
}
