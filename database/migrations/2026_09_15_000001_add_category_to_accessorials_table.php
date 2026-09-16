<?php

use App\Support\AccessorialCategories;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accessorials', function (Blueprint $table) {
            $table->string('category', 20)->default(AccessorialCategories::END_TO_END)->after('name');
            $table->boolean('is_active')->default(true)->after('category');
        });

        DB::table('accessorials')->select('id', 'name')->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $row) {
                DB::table('accessorials')->where('id', $row->id)->update([
                    'category' => AccessorialCategories::forName($row->name),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('accessorials', function (Blueprint $table) {
            $table->dropColumn(['category', 'is_active']);
        });
    }
};
