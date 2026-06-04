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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unsignedBigInteger('manifest_id')->nullable();
            $table->foreign('manifest_id')->references('id')->on('manifests')->onDelete('cascade');     
            $table->string('type')->nullable();
            $table->string('name')->nullable();
            $table->string('desc')->nullable();
            $table->string('sub_type')->nullable();
            $table->string('status')->nullable();
            $table->string('last_seen')->nullable();
            $table->string('last_location')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
