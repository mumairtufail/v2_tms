<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_stops', function (Blueprint $table) {
            // Make company_name nullable
            $table->string('company_name')->nullable()->change();
            
            // Make address fields nullable
            $table->string('address_1')->nullable()->change();
            $table->string('address_2')->nullable()->change();
            
            // Make location fields nullable
            $table->string('city')->nullable()->change();
            $table->string('state')->nullable()->change();
            $table->string('postal_code')->nullable()->change();
            
            // Make contact fields nullable
            $table->string('contact_name')->nullable()->change();
            $table->string('contact_phone')->nullable()->change();
            $table->string('contact_email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_stops', function (Blueprint $table) {
            // Revert to not nullable (be careful with this in production)
            $table->string('company_name')->nullable(false)->change();
            $table->string('address_1')->nullable(false)->change();
            $table->string('address_2')->nullable(false)->change();
            $table->string('city')->nullable(false)->change();
            $table->string('state')->nullable(false)->change();
            $table->string('postal_code')->nullable(false)->change();
            $table->string('contact_name')->nullable(false)->change();
            $table->string('contact_phone')->nullable(false)->change();
            $table->string('contact_email')->nullable(false)->change();
        });
    }
};
