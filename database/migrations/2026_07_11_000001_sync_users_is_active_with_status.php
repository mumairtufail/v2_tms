<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Repair drift between users.status (written by the v2 UI) and
     * users.is_active (checked by User::scopeActive). The v2 UI was the
     * only writer that skipped is_active, so status is authoritative.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNotNull('status')
            ->update(['is_active' => DB::raw("status = 'active'")]);
    }

    public function down(): void
    {
        // Data repair; nothing to revert.
    }
};
