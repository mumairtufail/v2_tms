<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('company_name');
            $table->string('contact_name')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('phone_ext', 10)->nullable();
            $table->string('fax', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('address_1');
            $table->string('address_2')->nullable();
            $table->string('suite', 50)->nullable();
            $table->string('city', 100);
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 10)->nullable();
            // Same precision as contact_book_entries
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 11, 7)->nullable();
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->string('customs_broker')->nullable();
            $table->text('bol_instructions')->nullable();
            $table->text('shipper_notes')->nullable();
            $table->text('consignee_notes')->nullable();
            $table->string('external_id')->nullable();
            $table->boolean('is_billing')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'company_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
