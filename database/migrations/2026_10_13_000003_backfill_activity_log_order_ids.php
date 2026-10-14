<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fills order_id on historical rows that only ever recorded the order in their
 * URL.
 *
 * The forOrder scope used to find those rows with leading-wildcard LIKEs, which
 * cannot use an index. Doing the match once here means the scope can stay on
 * indexed columns forever.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('activity_logs')
            ->whereNull('order_id')
            ->whereNotNull('url')
            ->where('url', 'like', '%/orders/%')
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    if (! preg_match('#/orders/(\d+)#', (string) $row->url, $matches)) {
                        continue;
                    }

                    DB::table('activity_logs')
                        ->where('id', $row->id)
                        ->update(['order_id' => (int) $matches[1]]);
                }
            });
    }

    public function down(): void
    {
        // Nothing to undo: the column is simply more complete than it was.
    }
};
