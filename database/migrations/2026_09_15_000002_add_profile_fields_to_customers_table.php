<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('external_id')->nullable()->after('short_code');
            $table->decimal('credit_limit', 12, 2)->nullable()->after('external_id');
            $table->decimal('credit_balance', 12, 2)->nullable()->after('credit_limit');
            $table->timestamp('credit_balance_synced_at')->nullable()->after('credit_balance');
            $table->boolean('require_dimensions')->default(false)->after('quote_required');
            $table->string('logo_path')->nullable()->after('require_dimensions');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['external_id', 'credit_limit', 'credit_balance', 'credit_balance_synced_at', 'require_dimensions', 'logo_path']);
        });
    }
};
