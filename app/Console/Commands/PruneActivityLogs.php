<?php

namespace App\Console\Commands;

use App\Models\ActivityLogs;
use Illuminate\Console\Command;

/**
 * Keeps the audit table from growing without bound.
 *
 * Read entries age out on a shorter clock than everything else: they are the
 * bulk of the volume and the least evidential. Noteworthy rows - failures,
 * denials, errors and anything impersonated - are never pruned early.
 */
class PruneActivityLogs extends Command
{
    protected $signature = 'audit:prune
                            {--days= : Override the retention window for normal entries}
                            {--read-days= : Override the retention window for view entries}
                            {--dry-run : Report what would be deleted without deleting it}';

    protected $description = 'Delete activity log entries past their retention window';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?? config('audit.retention_days', 365));
        $readDays = (int) ($this->option('read-days') ?? config('audit.read_retention_days', 30));
        $dryRun = (bool) $this->option('dry-run');

        if ($days <= 0 && $readDays <= 0) {
            $this->info('Retention is disabled; nothing to prune.');

            return self::SUCCESS;
        }

        $total = 0;

        // Stack traces are the largest thing stored and go stale fastest: after
        // a few weeks nobody is still debugging that request. Clearing just the
        // technical column keeps the entry itself as evidence.
        $technicalDays = (int) config('audit.technical_retention_days', 30);

        if ($technicalDays > 0 && ! $dryRun) {
            $cleared = ActivityLogs::query()
                ->whereNotNull('technical')
                ->where('created_at', '<', now()->subDays($technicalDays))
                ->update(['technical' => null]);

            if ($cleared > 0) {
                $this->line(sprintf('Cleared diagnostics from %d entries older than %d days.', $cleared, $technicalDays));
            }
        }

        if ($readDays > 0) {
            $total += $this->prune(
                ActivityLogs::query()
                    ->where('category', 'read')
                    ->where('is_successful', true)
                    ->whereNull('impersonator_id')
                    ->where('created_at', '<', now()->subDays($readDays)),
                sprintf('view entries older than %d days', $readDays),
                $dryRun
            );
        }

        if ($days > 0) {
            $total += $this->prune(
                ActivityLogs::query()
                    ->where('created_at', '<', now()->subDays($days)),
                sprintf('entries older than %d days', $days),
                $dryRun
            );
        }

        $this->newLine();
        $this->info($dryRun
            ? sprintf('Dry run: %d entries would be removed.', $total)
            : sprintf('Removed %d entries.', $total));

        return self::SUCCESS;
    }

    protected function prune($query, string $label, bool $dryRun): int
    {
        $count = (clone $query)->count();

        if ($count === 0) {
            $this->line("No {$label}.");

            return 0;
        }

        if ($dryRun) {
            $this->line(sprintf('Would remove %d %s.', $count, $label));

            return $count;
        }

        $this->line(sprintf('Removing %d %s...', $count, $label));

        // Chunked so a long backlog does not lock the table in one statement.
        $removed = 0;
        do {
            $deleted = (clone $query)->limit(1000)->delete();
            $removed += $deleted;
        } while ($deleted > 0);

        return $removed;
    }
}
