<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Accounting tab defaults. Stored now; applied once invoicing is enabled.
        Schema::create('customer_billing_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('invoice_terms', 30)->nullable();
            $table->boolean('taxable')->default(false);
            $table->json('recipients')->nullable();
            $table->boolean('attach_proof_of_pickup')->default(false);
            $table->boolean('attach_proof_of_delivery')->default(false);
            $table->boolean('attach_commercial_invoice')->default(false);
            $table->boolean('combine_documents')->default(false);
            $table->string('bulk_send_method', 30)->default('individual');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_billing_settings');
    }
};
