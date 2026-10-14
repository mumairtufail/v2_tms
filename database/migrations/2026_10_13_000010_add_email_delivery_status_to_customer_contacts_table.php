<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records whether mail to a person at a customer actually got through.
 *
 * A DNS check at the form catches a typo'd domain, but not a real domain with no
 * such mailbox — only a send attempt can tell you that. Until now the outcome
 * lived in storage/logs/mail-*.log, which nobody looks at, so "does this address
 * exist?" had no answer in the UI. These three columns are written by
 * MailService::recordDelivery on every send and read as a badge in the People tab.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_contacts', function (Blueprint $table) {
            // 'sent' | 'failed'. Null means nothing has been sent to this person yet,
            // which is different from "sent and fine" and is shown differently.
            $table->string('email_status', 20)->nullable()->after('email');
            $table->timestamp('email_status_at')->nullable()->after('email_status');
            // The human-readable reason from MailService::friendlyError, so the badge
            // can explain itself without anyone opening a log file.
            $table->string('email_status_reason', 500)->nullable()->after('email_status_at');
        });
    }

    public function down(): void
    {
        Schema::table('customer_contacts', function (Blueprint $table) {
            $table->dropColumn(['email_status', 'email_status_at', 'email_status_reason']);
        });
    }
};
