<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // People at a customer. People with portal_access sign in to the customer portal.
        Schema::create('customer_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name', 100)->nullable();
            $table->string('job_title', 100)->nullable();
            $table->string('email')->nullable();
            $table->text('notes')->nullable();
            $table->string('avatar_path')->nullable();
            $table->boolean('send_invoices')->default(false);
            $table->boolean('send_reports')->default(false);
            $table->boolean('send_dispatch_notifications')->default(false);
            $table->boolean('portal_access')->default(false);
            $table->boolean('cc_on_invoices')->default(false);
            $table->json('notification_prefs')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_contacts');
    }
};
