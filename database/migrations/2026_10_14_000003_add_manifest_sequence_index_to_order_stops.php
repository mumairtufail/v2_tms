<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Manifest::orderStops() now orders explicitly by sequence_number (see that
 * relation) - this index is what makes that cheap, matching the existing
 * (order_id, sequence_number) index that already serves Order::stops().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_stops', function (Blueprint $table) {
            $table->index(['manifest_id', 'sequence_number']);
        });
    }

    public function down(): void
    {
        Schema::table('order_stops', function (Blueprint $table) {
            $table->dropIndex(['manifest_id', 'sequence_number']);
        });
    }
};
