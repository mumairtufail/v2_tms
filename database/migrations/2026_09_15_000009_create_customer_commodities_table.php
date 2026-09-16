<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Saved commodity presets. Column names mirror order_stop_commodities.
        Schema::create('customer_commodities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->string('type', 30)->nullable();
            $table->string('measurement_unit', 10)->default('in_lbs');
            $table->decimal('volume', 10, 2)->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('linear_feet', 8, 2)->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->string('freight_class', 10)->nullable();
            $table->string('nmfc', 30)->nullable();
            $table->string('sku', 60)->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_commodities');
    }
};
