<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('name');
        });

        // Generate slugs for existing companies
        DB::table('companies')->get()->each(function ($company) {
            DB::table('companies')
                ->where('id', $company->id)
                ->update(['slug' => \Illuminate\Support\Str::slug($company->name)]);
        });

        // Make slug non-nullable after populating
        Schema::table('companies', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
