<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('order_stop_accessorials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_stop_id')->constrained('order_stops')->onDelete('cascade');
            $table->foreignId('accessorial_id')->constrained('accessorials')->onDelete('cascade'); // Assumes you have an 'accessorials' table
            $table->timestamps();
            $table->unique(['order_stop_id', 'accessorial_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('order_stop_accessorials');
    }
};