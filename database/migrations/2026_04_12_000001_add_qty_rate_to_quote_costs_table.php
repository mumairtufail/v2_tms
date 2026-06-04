<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_costs', function (Blueprint $table) {
            $table->decimal('qty', 10, 2)->nullable()->after('description');
            $table->decimal('rate', 10, 2)->nullable()->after('qty');
        });
    }

    public function down(): void
    {
        Schema::table('quote_costs', function (Blueprint $table) {
            $table->dropColumn(['qty', 'rate']);
        });
    }
};
