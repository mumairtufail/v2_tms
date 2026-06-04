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
            $table->string('service_type')->nullable()->after('sequence_number');
            $table->string('measurement_type')->nullable()->after('service_type');
        });

        Schema::table('order_stop_commodities', function (Blueprint $table) {
            $table->string('type')->nullable()->after('description');
            $table->integer('pieces')->default(0)->after('quantity');
            $table->decimal('linear_feet', 8, 2)->nullable()->after('height');
            $table->decimal('cube', 8, 2)->nullable()->after('linear_feet');
            $table->string('freight_class')->nullable()->after('cube');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_stops', function (Blueprint $table) {
            $table->dropColumn(['service_type', 'measurement_type']);
        });

        Schema::table('order_stop_commodities', function (Blueprint $table) {
            $table->dropColumn(['type', 'pieces', 'linear_feet', 'cube', 'freight_class']);
        });
    }
};
