<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('order_stop_commodities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_stop_id')->constrained('order_stops')->onDelete('cascade');
            $table->string('description');
            $table->string('service_type')->default('LTL');
            $table->string('measurement_type')->default('imperial');
            $table->integer('quantity')->default(1);
            $table->decimal('weight', 10, 2)->default(0);
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('order_stop_commodities');
    }
};