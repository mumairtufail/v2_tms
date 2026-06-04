<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_stops', function (Blueprint $table) {
            $table->json('consignee_data')->nullable();
            $table->json('billing_data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_stops', function (Blueprint $table) {
            $table->dropColumn(['consignee_data', 'billing_data']);
        });
    }
};
