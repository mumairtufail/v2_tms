<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('logo_light')->nullable()->after('phone');
            $table->string('logo_dark')->nullable()->after('logo_light');
            $table->string('logo_icon')->nullable()->after('logo_dark');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['logo_light', 'logo_dark', 'logo_icon']);
        });
    }
};
