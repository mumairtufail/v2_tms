<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            $table->index(['company_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropIndex(['company_id', 'order_id']);
            $table->dropColumn('order_id');
        });
    }
};
