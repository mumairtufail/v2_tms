<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('quote_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_quote_id')->nullable()->constrained('order_quotes')->onDelete('cascade');
            $table->string('category')->nullable(); // 'carrier' or 'quote'
            $table->string('type')->nullable(); // e.g., 'Freight', 'Fuel'
            $table->string('description')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('quote_costs');
    }
};
