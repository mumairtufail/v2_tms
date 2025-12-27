<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(1);
            $table->string('short_code')->nullable();
            $table->boolean('portal')->default(0);
            $table->enum('location_sharing', ['Do not share', 'approximate', 'exact live location'])->default('Do not share');
            $table->boolean('network_customer')->default(0);
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('currency')->nullable();
            $table->string('customer_email')->nullable();
            $table->enum('customer_type', ['shipper', 'broker', 'carrier', 'other'])->default('other');
            $table->enum('default_billing_option', ['third_party', 'consignee', 'shipper'])->default('shipper');
            $table->boolean('quote_required')->default(0);
            $table->boolean('is_deleted')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};