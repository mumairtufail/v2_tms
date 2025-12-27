<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('order_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->enum('stop_type', ['pickup', 'delivery', 'mixed']);
            $table->integer('sequence_number')->default(1);
            $table->string('company_name');
            $table->string('address_1');
            $table->string('address_2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');
            $table->string('country')->default('US');
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->boolean('is_appointment')->default(false);
            $table->timestamps();
            $table->index(['order_id', 'sequence_number']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('order_stops');
    }
};