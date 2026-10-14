<?php

namespace Tests;

use App\Services\ActivityLogWriter;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Push any buffered audit entries to the database.
     *
     * Entries are normally written from a terminating callback once the
     * response has been sent. A test that calls the logging service directly
     * never reaches that point, so it asks for the flush itself.
     */
    protected function flushAuditLog(): void
    {
        $this->app->make(ActivityLogWriter::class)->flush();
    }

    /**
     * Assert against the audit trail, flushing first so buffered entries are
     * visible to the query.
     */
    protected function assertAuditLogged(array $attributes, string $message = ''): void
    {
        $this->flushAuditLog();

        $this->assertDatabaseHas('activity_logs', $attributes, message: $message);
    }
}
