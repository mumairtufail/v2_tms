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
        Schema::table('users', function (Blueprint $table) {
            // Add name field if not exists (for V2)
            if (!Schema::hasColumn('users', 'name')) {
                $table->string('name')->nullable()->after('id');
            }
            
            // Add V2 specific fields
            $table->string('status')->default('active')->after('email');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->boolean('email_notifications')->default(true)->after('last_login_at');
            $table->boolean('two_factor_enabled')->default(false)->after('email_notifications');
        });

        // Populate name field from f_name and l_name for existing users
        DB::table('users')->get()->each(function ($user) {
            if (!$user->name && ($user->f_name || $user->l_name)) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['name' => trim(($user->f_name ?? '') . ' ' . ($user->l_name ?? ''))]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'last_login_at',
                'email_notifications',
                'two_factor_enabled'
            ]);
        });
    }
};
