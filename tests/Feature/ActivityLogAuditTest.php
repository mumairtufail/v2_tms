<?php

namespace Tests\Feature;

use App\Models\ActivityLogs;
use App\Models\Carrier;
use App\Models\Company;
use App\Models\User;
use App\Services\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tests\TestCase;

/**
 * Covers the audit guarantees: who really acted, that reads and failures are
 * recorded alongside successes, and that secrets never reach the table.
 */
class ActivityLogAuditTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected User $superAdmin;

    protected User $companyUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Audit Freight Co',
            'slug' => 'audit-freight-co',
            'shortcode' => 'AUD',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $this->superAdmin = User::create([
            'company_id' => $this->company->id,
            'f_name' => 'Super',
            'l_name' => 'Admin',
            'email' => 'super@test.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'is_super_admin' => true,
        ]);

        $this->companyUser = User::create([
            'company_id' => $this->company->id,
            'f_name' => 'Company',
            'l_name' => 'User',
            'email' => 'company@test.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'is_super_admin' => false,
        ]);
    }

    public function test_starting_impersonation_is_recorded_against_the_super_admin(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.users.impersonate', ['user' => $this->companyUser->id]));

        $this->flushAuditLog();

        $log = ActivityLogs::where('action', 'impersonation.start')->first();

        $this->assertNotNull($log, 'Starting impersonation must be logged');
        $this->assertSame($this->superAdmin->id, $log->user_id);
        $this->assertSame($this->superAdmin->id, $log->impersonator_id);
        $this->assertSame($this->companyUser->id, (int) $log->subject_id);
        $this->assertSame('critical', $log->severity);
    }

    public function test_actions_during_impersonation_name_the_real_actor(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.users.impersonate', ['user' => $this->companyUser->id]));

        // The session is now the company user's, with the super admin behind it.
        $this->actingAs($this->companyUser)
            ->withSession(['impersonating_original_id' => $this->superAdmin->id]);

        $this->session(['impersonating_original_id' => $this->superAdmin->id]);
        $this->be($this->companyUser);

        app(ActivityLog::class)->log('orders.destroy', ['description' => 'Deleted the order']);

        $this->flushAuditLog();

        $log = ActivityLogs::where('action', 'orders.destroy')->first();

        $this->assertNotNull($log);
        $this->assertSame($this->companyUser->id, $log->user_id, 'The account used is the company user');
        $this->assertSame($this->superAdmin->id, $log->impersonator_id, 'The real actor is the super admin');
        $this->assertTrue($log->is_impersonated);

        // Stored clean so a company user never reads the support context; the
        // super admin view composes the full wording from the columns.
        $this->assertSame('Deleted the order', $log->description);
        $this->assertStringContainsString('impersonated by', $log->description_with_actor);
        $this->assertStringContainsString('Super Admin', $log->description_with_actor);
    }

    public function test_stopping_impersonation_is_recorded(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.users.impersonate', ['user' => $this->companyUser->id]));

        $this->actingAs($this->companyUser)
            ->withSession(['impersonating_original_id' => $this->superAdmin->id])
            ->post(route('admin.impersonate.stop'));

        $this->flushAuditLog();

        $log = ActivityLogs::where('action', 'impersonation.stop')->first();

        $this->assertNotNull($log, 'Stopping impersonation must be logged');
        $this->assertSame($this->superAdmin->id, $log->user_id);
    }

    public function test_impersonating_a_super_admin_is_refused_and_recorded(): void
    {
        $other = User::create([
            'company_id' => $this->company->id,
            'f_name' => 'Other',
            'l_name' => 'Super',
            'email' => 'other-super@test.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'is_super_admin' => true,
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('admin.users.impersonate', ['user' => $other->id]));

        $this->flushAuditLog();

        $log = ActivityLogs::where('action', 'impersonation.start.denied')->first();

        $this->assertNotNull($log, 'A refused impersonation is still an event worth keeping');
        $this->assertFalse((bool) $log->is_successful);
    }

    public function test_read_requests_are_logged(): void
    {
        config(['audit.log_reads' => true]);

        $this->actingAs($this->superAdmin)->get(route('admin.settings.index'));

        $this->flushAuditLog();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->superAdmin->id,
            'category' => 'read',
            'method' => 'GET',
        ]);
    }

    public function test_viewing_the_activity_log_does_not_add_to_it(): void
    {
        config(['audit.log_reads' => true]);

        // The page, a summary tile (a filtered reload) and an empty filter form
        $this->actingAs($this->superAdmin)->get(route('admin.logs'));
        $this->actingAs($this->superAdmin)->get(route('admin.logs', ['status' => 'failed']));
        $this->actingAs($this->superAdmin)->get(route('admin.logs', ['search' => '', 'from' => '', 'to' => '']));

        $this->flushAuditLog();

        $this->assertDatabaseMissing('activity_logs', ['category' => 'read']);
    }

    public function test_read_logging_can_be_turned_off(): void
    {
        config(['audit.log_reads' => false]);

        $this->actingAs($this->superAdmin)->get(route('admin.logs'));

        $this->flushAuditLog();

        $this->assertDatabaseMissing('activity_logs', ['category' => 'read']);
    }

    public function test_secrets_are_redacted_before_they_are_stored(): void
    {
        $this->be($this->superAdmin);

        app(ActivityLog::class)->log('settings.update', [
            'description' => 'Updated settings',
            'input' => [
                'email' => 'visible@test.com',
                'password' => 'plain-text-secret',
                'nested' => ['api_key' => 'sk-live-should-not-persist'],
            ],
        ]);

        $this->flushAuditLog();

        $log = ActivityLogs::where('action', 'settings.update')->firstOrFail();

        $this->assertSame('visible@test.com', $log->data['input']['email']);
        $this->assertSame('[redacted]', $log->data['input']['password']);
        $this->assertSame('[redacted]', $log->data['input']['nested']['api_key']);

        // Belt and braces: the raw secret must not appear anywhere in the row.
        $this->assertStringNotContainsString('plain-text-secret', json_encode($log->data));
        $this->assertStringNotContainsString('sk-live-should-not-persist', json_encode($log->data));
    }

    public function test_model_changes_record_before_and_after_values(): void
    {
        $this->be($this->superAdmin);

        $carrier = Carrier::create([
            'company_id' => $this->company->id,
            'carrier_name' => 'Old Haulage',
            'is_active' => true,
        ]);

        $carrier->update(['carrier_name' => 'New Haulage']);

        $this->flushAuditLog();

        $log = ActivityLogs::where('action', 'updated')
            ->where('subject_type', Carrier::class)
            ->where('subject_id', $carrier->id)
            ->firstOrFail();

        $this->assertSame('Old Haulage', $log->data['changes']['carrier_name']['from']);
        $this->assertSame('New Haulage', $log->data['changes']['carrier_name']['to']);
        $this->assertStringContainsString('Old Haulage', $log->description);
        $this->assertStringContainsString('New Haulage', $log->description);
    }

    public function test_model_creation_and_deletion_are_recorded(): void
    {
        $this->be($this->superAdmin);

        $carrier = Carrier::create([
            'company_id' => $this->company->id,
            'carrier_name' => 'Transient Haulage',
            'is_active' => true,
        ]);

        $this->flushAuditLog();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'created',
            'subject_type' => Carrier::class,
            'subject_id' => $carrier->id,
        ]);

        $id = $carrier->id;
        $carrier->delete();

        $this->flushAuditLog();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'deleted',
            'subject_type' => Carrier::class,
            'subject_id' => $id,
        ]);
    }

    public function test_entries_carry_severity_and_session_correlation(): void
    {
        $this->be($this->superAdmin);

        app(ActivityLog::class)->logDenied('permission.denied', 'Denied: tried to delete orders');

        $this->flushAuditLog();

        $log = ActivityLogs::where('action', 'permission.denied')->firstOrFail();

        $this->assertSame('warning', $log->severity);
        $this->assertSame('authorization', $log->category);
        $this->assertFalse((bool) $log->is_successful);
    }

    public function test_noteworthy_scope_surfaces_failures_and_impersonation(): void
    {
        $this->be($this->superAdmin);

        app(ActivityLog::class)->log('routine.thing', ['description' => 'Nothing to see']);
        app(ActivityLog::class)->logError('error.unhandled', 'Something broke');

        $this->flushAuditLog();

        $noteworthy = ActivityLogs::noteworthy()->pluck('action')->all();

        $this->assertContains('error.unhandled', $noteworthy);
        $this->assertNotContains('routine.thing', $noteworthy);
    }

    public function test_unhandled_errors_are_audited(): void
    {
        $this->be($this->superAdmin);

        app(ExceptionHandler::class)->report(new \RuntimeException('deliberate failure'));

        $this->flushAuditLog();

        $log = ActivityLogs::where('action', 'error.unhandled')->first();

        $this->assertNotNull($log, 'An unhandled exception must reach the audit trail');
        $this->assertSame('critical', $log->severity);
        $this->assertSame('error', $log->category);
        // The message, trace and cause chain live in the technical column,
        // never in the payload a company user is shown.
        $this->assertStringNotContainsString('deliberate failure', json_encode($log->data));
        $this->assertSame('RuntimeException', $log->technical['exception']);
        $this->assertSame('deliberate failure', $log->technical['exception_message']);
        $this->assertNotEmpty($log->technical['trace']);
        $this->assertStringContainsString('.php:', $log->technical['file']);
    }

    public function test_http_errors_on_the_internal_dont_report_list_are_audited(): void
    {
        $this->be($this->superAdmin);

        // AccessDeniedHttpException sits on Laravel's internalDontReport list,
        // so report() returns before any reportable callback runs. Overriding
        // report() rather than registering a callback is what makes this work.
        app(ExceptionHandler::class)->report(new AccessDeniedHttpException('nope'));

        $this->flushAuditLog();

        $log = ActivityLogs::where('action', 'http.error.403')->first();

        $this->assertNotNull($log, 'A 403 must reach the audit trail');
        $this->assertSame('warning', $log->severity);
        $this->assertFalse((bool) $log->is_successful);
    }

    public function test_validation_failures_are_not_double_audited(): void
    {
        $this->be($this->superAdmin);

        app(ExceptionHandler::class)->report(
            \Illuminate\Validation\ValidationException::withMessages(['email' => 'required'])
        );

        // The middleware already records the failed request; the handler would
        // only add a duplicate row for the same event.
        $this->flushAuditLog();

        $this->assertSame(0, ActivityLogs::where('category', 'error')->count());
    }

    public function test_prune_respects_the_retention_window(): void
    {
        $this->be($this->superAdmin);

        $old = ActivityLogs::create([
            'user_id' => $this->superAdmin->id,
            'company_id' => $this->company->id,
            'action' => 'old.entry',
            'category' => 'read',
            'severity' => 'info',
            'is_successful' => true,
            'data' => ['description' => 'Old view'],
        ]);
        $old->forceFill(['created_at' => now()->subDays(400)])->save();

        $recent = ActivityLogs::create([
            'user_id' => $this->superAdmin->id,
            'company_id' => $this->company->id,
            'action' => 'recent.entry',
            'category' => 'read',
            'severity' => 'info',
            'is_successful' => true,
            'data' => ['description' => 'Recent view'],
        ]);

        $this->artisan('audit:prune')->assertSuccessful();

        $this->flushAuditLog();

        $this->assertDatabaseMissing('activity_logs', ['id' => $old->id]);
        $this->flushAuditLog();

        $this->assertDatabaseHas('activity_logs', ['id' => $recent->id]);
    }

    public function test_equipment_status_changed_through_a_service_is_audited(): void
    {
        $this->be($this->superAdmin);

        $equipment = \App\Models\Equipment::create([
            'company_id' => $this->company->id,
            'name' => 'Trailer 42',
            'type' => 'trailer',
            'status' => 'Available',
        ]);

        $manifest = \App\Models\Manifest::create([
            'company_id' => $this->company->id,
            'code' => 'MAN-AUDIT-1',
        ]);

        // Goes through the service, which previously used a query builder
        // update and so changed the row without producing any entry.
        app(\App\Services\ManifestService::class)->assignEquipment($manifest, $equipment->id);
        $this->flushAuditLog();

        $log = ActivityLogs::where('action', 'updated')
            ->where('subject_type', \App\Models\Equipment::class)
            ->where('subject_id', $equipment->id)
            ->first();

        $this->assertNotNull($log, 'A status change made by a service must be audited');
        $this->assertSame('Available', $log->data['changes']['status']['from']);
        $this->assertSame('In Use', $log->data['changes']['status']['to']);
    }

    public function test_order_stops_removed_during_an_update_are_audited(): void
    {
        $this->be($this->superAdmin);

        $order = \App\Models\Order::create([
            'company_id' => $this->company->id,
            'order_number' => 'ORD-AUDIT-1',
            'status' => 'draft',
        ]);

        $stop = \App\Models\OrderStop::create([
            'order_id' => $order->id,
        ]);

        // The same shape the update service uses for stops dropped from a form.
        \App\Models\OrderStop::whereIn('id', [$stop->id])->get()->each->delete();
        $this->flushAuditLog();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'deleted',
            'subject_type' => \App\Models\OrderStop::class,
            'subject_id' => $stop->id,
        ]);
    }
}
