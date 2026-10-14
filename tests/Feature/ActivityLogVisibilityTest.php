<?php

namespace Tests\Feature;

use App\Models\ActivityLogs;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLog;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The boundary between the two audiences.
 *
 * A super admin gets stack traces, SQL and headers. A company user gets the
 * business record only, and must not be able to reach the diagnostics by
 * asking the endpoint directly.
 */
class ActivityLogVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Company $otherCompany;

    protected User $superAdmin;

    protected User $companyUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Visible Freight', 'slug' => 'visible-freight',
            'shortcode' => 'VIS', 'is_active' => true, 'is_deleted' => false,
        ]);

        $this->otherCompany = Company::create([
            'name' => 'Other Freight', 'slug' => 'other-freight',
            'shortcode' => 'OTH', 'is_active' => true, 'is_deleted' => false,
        ]);

        $this->superAdmin = User::create([
            'company_id' => $this->company->id, 'f_name' => 'Super', 'l_name' => 'Admin',
            'email' => 'super@visible.test', 'password' => Hash::make('password'),
            'status' => 'active', 'is_super_admin' => true,
        ]);

        $this->companyUser = User::create([
            'company_id' => $this->company->id, 'f_name' => 'Company', 'l_name' => 'Staff',
            'email' => 'staff@visible.test', 'password' => Hash::make('password'),
            'status' => 'active', 'is_super_admin' => false,
        ]);

        $this->grantLogsPermission($this->companyUser);
    }

    /**
     * Give the company user permission to view logs at all, so the tests
     * exercise the diagnostics gate rather than stopping at the 403 the
     * permission middleware would otherwise return.
     */
    protected function grantLogsPermission(User $user): void
    {
        $role = Role::create([
            'name' => 'logs-viewer',
            'company_id' => $user->company_id,
            'is_active' => true,
        ]);

        $permission = Permission::firstOrCreate(['name' => 'logs'], ['is_active' => true]);

        $role->permissions()->attach($permission->id, [
            'view' => true, 'create' => false, 'update' => false,
            'delete' => false, 'logs' => true, 'others' => false,
        ]);

        $user->roles()->attach($role->id);
    }

    protected function makeErrorEntry(): ActivityLogs
    {
        $this->be($this->superAdmin);

        app(ExceptionHandler::class)->report(
            new \RuntimeException('internal detail that must not leak')
        );

        $this->flushAuditLog();

        return ActivityLogs::where('action', 'error.unhandled')->firstOrFail();
    }

    public function test_diagnostics_are_stored_separately_from_the_shown_payload(): void
    {
        $log = $this->makeErrorEntry();

        $this->assertNotNull($log->technical);
        $this->assertSame('RuntimeException', $log->technical['exception']);

        // The description a company user reads says nothing about internals.
        $this->assertSame('A system error occurred and was reported', $log->data['description']);
        $this->assertStringNotContainsString('internal detail', json_encode($log->data));
        $this->assertStringNotContainsString('RuntimeException', json_encode($log->data));
    }

    public function test_super_admin_endpoint_returns_diagnostics(): void
    {
        $log = $this->makeErrorEntry();

        $response = $this->actingAs($this->superAdmin)
            ->getJson(route('admin.logs.show', ['log' => $log->id]));

        $response->assertOk();
        $response->assertJsonPath('technical.exception', 'RuntimeException');
        $response->assertJsonPath('technical.exception_message', 'internal detail that must not leak');
        $this->assertNotEmpty($response->json('technical.trace'));
    }

    public function test_company_endpoint_refuses_platform_error_entries(): void
    {
        $log = $this->makeErrorEntry();
        $log->update(['company_id' => $this->company->id]);

        // A platform failure is not part of the company's own record, so the
        // entry is not reachable at all rather than returned with the
        // diagnostics stripped out.
        $this->actingAs($this->companyUser)
            ->getJson(route('v2.activity-logs.show', [
                'company' => $this->company->slug,
                'log' => $log->id,
            ]))
            ->assertNotFound();
    }

    public function test_company_endpoint_withholds_diagnostics_on_an_ordinary_entry(): void
    {
        $this->be($this->companyUser);

        app(ActivityLog::class)->log('orders.update', [
            'description' => 'Updated the order',
            'category' => 'http',
            'exception' => 'ShouldNotBeShown',
            'trace' => 'secret trace frames',
        ]);
        $this->flushAuditLog();

        $log = ActivityLogs::where('action', 'orders.update')->firstOrFail();
        $log->update(['company_id' => $this->company->id]);

        $response = $this->actingAs($this->companyUser)
            ->getJson(route('v2.activity-logs.show', [
                'company' => $this->company->slug,
                'log' => $log->id,
            ]));

        $response->assertOk();
        $response->assertJsonPath('description', 'Updated the order');

        // The gate is on the server: asking directly reveals nothing either.
        $response->assertJsonMissingPath('technical');
        $response->assertJsonMissingPath('raw_data');
        $this->assertStringNotContainsString('secret trace frames', $response->getContent());
        $this->assertStringNotContainsString('ShouldNotBeShown', $response->getContent());
    }

    public function test_super_admin_keeps_diagnostics_inside_the_company_view(): void
    {
        $log = $this->makeErrorEntry();
        $log->update(['company_id' => $this->company->id]);

        $response = $this->actingAs($this->superAdmin)
            ->getJson(route('v2.activity-logs.show', [
                'company' => $this->company->slug,
                'log' => $log->id,
            ]));

        $response->assertOk();
        $response->assertJsonPath('technical.exception', 'RuntimeException');
    }

    public function test_a_company_cannot_open_another_companys_entry(): void
    {
        $log = $this->makeErrorEntry();
        $log->update(['company_id' => $this->otherCompany->id]);

        $response = $this->actingAs($this->companyUser)
            ->getJson(route('v2.activity-logs.show', [
                'company' => $this->company->slug,
                'log' => $log->id,
            ]));

        // 403 and 404 are both refusals; what matters is that nothing leaks.
        $this->assertContains($response->status(), [403, 404]);
        $this->assertStringNotContainsString('internal detail', $response->getContent());
    }

    public function test_company_category_list_excludes_platform_internals(): void
    {
        $categories = ActivityLogs::companyCategories();

        $this->assertArrayNotHasKey('impersonation', $categories);
        $this->assertArrayNotHasKey('error', $categories);
        $this->assertArrayHasKey('model', $categories);
        $this->assertArrayHasKey('auth', $categories);
    }

    public function test_entries_from_one_request_share_a_request_id(): void
    {
        $this->be($this->superAdmin);

        $service = app(ActivityLog::class);
        $service->log('first.thing', ['description' => 'First']);
        $service->log('second.thing', ['description' => 'Second']);
        $this->flushAuditLog();

        $ids = ActivityLogs::whereIn('action', ['first.thing', 'second.thing'])
            ->pluck('request_id')
            ->unique();

        $this->assertCount(1, $ids, 'Entries from one request must correlate');
        $this->assertNotNull($ids->first());
    }

    public function test_the_actor_survives_deletion_of_the_user(): void
    {
        $this->be($this->companyUser);

        app(ActivityLog::class)->log('something.happened', ['description' => 'Did a thing']);
        $this->flushAuditLog();

        $log = ActivityLogs::where('action', 'something.happened')->firstOrFail();
        $this->assertSame($this->companyUser->name, $log->actor_name);

        // No foreign key, so the row stays and still names the person.
        $this->companyUser->forceDelete();

        $log->refresh();
        $this->assertSame('Company Staff', $log->actor_name);
    }

    public function test_impersonation_entries_are_hidden_from_the_company(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.users.impersonate', ['user' => $this->companyUser->id]));
        $this->flushAuditLog();

        ActivityLogs::query()->update(['company_id' => $this->company->id]);

        $service = app(\App\Services\ActivityLogListingService::class);

        $companyView = $service->paginate(request(), $this->company->id, privileged: false);
        $adminView = $service->paginate(request(), $this->company->id, privileged: true);

        $this->assertNotContains('impersonation', $companyView->pluck('category')->all());
        $this->assertContains('impersonation', $adminView->pluck('category')->all(),
            'The record still exists for the super admin');
    }

    public function test_company_rows_never_name_the_super_admin(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.users.impersonate', ['user' => $this->companyUser->id]));

        // An ordinary action performed while impersonating.
        $this->session(['impersonating_original_id' => $this->superAdmin->id]);
        $this->be($this->companyUser);
        app(ActivityLog::class)->log('orders.update', ['description' => 'Updated the order']);
        $this->flushAuditLog();

        ActivityLogs::query()->update(['company_id' => $this->company->id]);

        $log = ActivityLogs::where('action', 'orders.update')->firstOrFail();

        // Stored clean, so nothing has to be scrubbed at render time.
        $this->assertSame('Updated the order', $log->description);
        $this->assertStringNotContainsString('impersonated by', $log->description);
        $this->assertStringNotContainsString('Super Admin', $log->description);

        // The super admin view still composes the full wording.
        $this->assertStringContainsString('impersonated by', $log->description_with_actor);

        $payload = app(\App\Services\ActivityLogListingService::class)->detail($log, includeTechnical: false);
        $this->assertArrayNotHasKey('impersonator', $payload);
        $this->assertArrayNotHasKey('impersonated', $payload);
        $this->assertStringNotContainsString('Super Admin', json_encode($payload));
    }

    public function test_company_cannot_open_an_impersonation_entry_directly(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.users.impersonate', ['user' => $this->companyUser->id]));
        $this->flushAuditLog();

        $log = ActivityLogs::where('category', 'impersonation')->firstOrFail();
        $log->update(['company_id' => $this->company->id]);

        $this->actingAs($this->companyUser)
            ->getJson(route('v2.activity-logs.show', [
                'company' => $this->company->slug,
                'log' => $log->id,
            ]))
            ->assertNotFound();
    }

    public function test_starting_impersonation_is_not_marked_as_acting_as_someone(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.users.impersonate', ['user' => $this->companyUser->id]));
        $this->flushAuditLog();

        $log = ActivityLogs::where('action', 'impersonation.start')->firstOrFail();

        // The super admin was themselves at that moment; the row should not
        // read "acting: themselves".
        $this->assertFalse($log->is_impersonated);
    }

    public function test_support_access_does_not_flag_routine_views_as_attention(): void
    {
        $this->session(['impersonating_original_id' => $this->superAdmin->id]);
        $this->be($this->companyUser);

        app(ActivityLog::class)->log('orders.index', [
            'description' => 'Viewed the orders list',
            'category' => 'read',
        ]);
        $this->flushAuditLog();

        $log = ActivityLogs::where('action', 'orders.index')->firstOrFail();

        $this->assertSame('info', $log->severity, 'Impersonation must not raise severity on routine traffic');
        $this->assertTrue($log->is_impersonated);
    }

    public function test_the_impersonate_route_does_not_produce_a_second_misattributed_entry(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.users.impersonate', ['user' => $this->companyUser->id]));
        $this->flushAuditLog();

        $this->assertSame(0, ActivityLogs::where('action', 'admin.users.impersonate')->count(),
            'The middleware entry would name the impersonated user as the one who started it');
        $this->assertSame(1, ActivityLogs::where('action', 'impersonation.start')->count());
    }
}
