<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Default accessorials added to an order stop when this address is used.
        Schema::create('customer_address_accessorial', function (Blueprint $table) {
            $table->foreignId('customer_address_id')->constrained('customer_addresses')->cascadeOnDelete();
            $table->foreignId('accessorial_id')->constrained('accessorials')->cascadeOnDelete();
            $table->primary(['customer_address_id', 'accessorial_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_address_accessorial');
    }
};
