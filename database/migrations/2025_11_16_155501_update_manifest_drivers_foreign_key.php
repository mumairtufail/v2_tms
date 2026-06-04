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
        Schema::table('manifest_drivers', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['driver_id']);
            
            // Add the new foreign key constraint referencing users table
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manifest_drivers', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['driver_id']);
            
            // Restore the original foreign key constraint referencing company_users table
            $table->foreign('driver_id')->references('id')->on('company_users')->onDelete('cascade');
        });
    }
};
