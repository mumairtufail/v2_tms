<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('logo_light')->nullable();
            $table->string('logo_dark')->nullable();
            $table->string('logo_icon')->nullable();
            $table->string('app_name')->default('TMS');
            $table->timestamps();
        });

        // Seed the single settings row
        DB::table('system_settings')->insert(['id' => 1, 'app_name' => 'TMS']);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
