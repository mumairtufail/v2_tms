<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as LaravelLog;
use Throwable;

/**
 * Buffers audit rows and writes them in one statement after the response has
 * been sent.
 *
 * A request that touches ten records used to pay for ten synchronous inserts
 * inside the request, each one blocking the user. Here the rows accumulate in
 * memory and leave in a single multi-row INSERT from a terminating callback,
 * so the cost lands after the client already has its response.
 *
 * The buffer is capped so a long-running job or an import cannot grow it
 * without bound; crossing the cap flushes immediately.
 */
class ActivityLogWriter
{
    /** @var array<int, array<string, mixed>> */
    protected array $buffer = [];

    protected bool $terminatingRegistered = false;

    protected bool $flushing = false;

    public function __construct(
        protected ?string $connection = null,
        protected int $bufferLimit = 100,
        protected bool $deferred = true,
    ) {}

    /**
     * Queue a row. Returns nothing: callers that need the persisted model
     * should use writeNow() instead.
     */
    public function queue(array $row): void
    {
        $this->buffer[] = $row;

        if (! $this->deferred || count($this->buffer) >= $this->bufferLimit) {
            $this->flush();

            return;
        }

        $this->registerTerminatingFlush();
    }

    /**
     * Write a single row immediately and return its id. Used where the caller
     * needs the row to exist before the response is built.
     */
    public function writeNow(array $row): ?int
    {
        try {
            return (int) $this->table()->insertGetId($row);
        } catch (Throwable $e) {
            $this->reportFailure($e, 1);

            return null;
        }
    }

    public function flush(): void
    {
        // A failure inside flush must not re-enter through the error path.
        if ($this->flushing || $this->buffer === []) {
            return;
        }

        $this->flushing = true;
        $rows = $this->buffer;
        $this->buffer = [];

        try {
            // Chunked because MySQL caps a statement at max_allowed_packet and
            // these rows carry JSON payloads.
            foreach (array_chunk($rows, 50) as $chunk) {
                $this->table()->insert($chunk);
            }
        } catch (Throwable $e) {
            $this->reportFailure($e, count($rows));
        } finally {
            $this->flushing = false;
        }
    }

    public function pending(): int
    {
        return count($this->buffer);
    }

    protected function registerTerminatingFlush(): void
    {
        if ($this->terminatingRegistered) {
            return;
        }

        $this->terminatingRegistered = true;

        // Runs after the response has been sent to the client under FPM.
        app()->terminating(function () {
            $this->flush();
            $this->terminatingRegistered = false;
        });
    }

    protected function table()
    {
        return DB::connection($this->connection)->table('activity_logs');
    }

    /**
     * The audit trail failing is itself worth knowing about, so it goes to the
     * application log rather than disappearing.
     */
    protected function reportFailure(Throwable $e, int $lostRows): void
    {
        LaravelLog::error('Activity log write failed; entries were lost', [
            'rows_lost' => $lostRows,
            'error' => $e->getMessage(),
        ]);
    }
}
