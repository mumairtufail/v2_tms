<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            // Who really performed the action when a super admin was wearing
            // someone else's account. Null for ordinary, non-impersonated work.
            $table->foreignId('impersonator_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();

            // Lets a reviewer replay one sitting end to end, and spot a session
            // that suddenly changes IP or user agent.
            $table->string('session_id', 64)->nullable()->after('order_id');

            // Promoted out of the JSON blob so they can be filtered and indexed.
            $table->string('category', 32)->nullable()->after('action');
            $table->string('severity', 16)->default('info')->after('category');
            $table->unsignedSmallInteger('status_code')->nullable()->after('method');
            $table->unsignedInteger('duration_ms')->nullable()->after('status_code');

            // The record the action was about, when there is one.
            $table->string('subject_type')->nullable()->after('session_id');
            $table->unsignedBigInteger('subject_id')->nullable()->after('subject_type');

            $table->index('created_at');
            $table->index(['company_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['category', 'created_at']);
            $table->index(['severity', 'created_at']);
            $table->index('session_id');
            $table->index(['subject_type', 'subject_id']);
            $table->index(['is_successful', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['company_id', 'created_at']);
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['category', 'created_at']);
            $table->dropIndex(['severity', 'created_at']);
            $table->dropIndex(['session_id']);
            $table->dropIndex(['subject_type', 'subject_id']);
            $table->dropIndex(['is_successful', 'created_at']);

            $table->dropForeign(['impersonator_id']);
            $table->dropColumn([
                'impersonator_id',
                'session_id',
                'category',
                'severity',
                'status_code',
                'duration_ms',
                'subject_type',
                'subject_id',
            ]);
        });
    }
};
