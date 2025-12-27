<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'task_start_date')) {
                $table->date('task_start_date')->nullable();
            }
            if (!Schema::hasColumn('tasks', 'task_start_time')) {
                $table->time('task_start_time')->nullable();
            }
            if (!Schema::hasColumn('tasks', 'task_end_date')) {
                $table->date('task_end_date')->nullable();
            }
            if (!Schema::hasColumn('tasks', 'task_end_time')) {
                $table->time('task_end_time')->nullable();
            }
            if (!Schema::hasColumn('tasks', 'trailer_id')) {
                $table->string('trailer_id')->nullable();
            }
            if (!Schema::hasColumn('tasks', 'security_id')) {
                $table->string('security_id')->nullable();
            }
            if (!Schema::hasColumn('tasks', 'hours')) {
                $table->integer('hours')->nullable();
            }
            if (!Schema::hasColumn('tasks', 'manifest_id')) {
                $table->unsignedBigInteger('manifest_id')->nullable();
                // Optionally add foreign key constraint when manifest table exists:
                $table->foreign('manifest_id')->references('id')->on('manifests')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'task_start_date')) {
                $table->dropColumn('task_start_date');
            }
            if (Schema::hasColumn('tasks', 'task_start_time')) {
                $table->dropColumn('task_start_time');
            }
            if (Schema::hasColumn('tasks', 'task_end_date')) {
                $table->dropColumn('task_end_date');
            }
            if (Schema::hasColumn('tasks', 'task_end_time')) {
                $table->dropColumn('task_end_time');
            }
            if (Schema::hasColumn('tasks', 'trailer_id')) {
                $table->dropColumn('trailer_id');
            }
            if (Schema::hasColumn('tasks', 'security_id')) {
                $table->dropColumn('security_id');
            }
            if (Schema::hasColumn('tasks', 'hours')) {
                $table->dropColumn('hours');
            }
            if (Schema::hasColumn('tasks', 'manifest_id')) {
                $table->dropForeign(['manifest_id']);
                $table->dropColumn('manifest_id');
            }
        });
    }
};
