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
        Schema::create('manifest_drivers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('manifest_id');
            $table->unsignedBigInteger('driver_id');

            $table->foreign('manifest_id')->references('id')->on('manifests')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manifest_drivers');
    }
};
