<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records who started the order - staff in the admin screens, or the
 * customer from their own portal.
 *
 * A portal order is always created as origin-to-destination, and the portal
 * edit screen already keeps it that way. Nothing previously stopped a staff
 * member from switching it to a different strategy (Shuttle Loop, say) once
 * the order showed up in the company admin screen - this column is what lets
 * that edit screen, and OrderUpdateService, tell the two apart and lock the
 * type for an order the customer started.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('created_via', 16)->default('staff')->after('order_type');
        });

        // Existing rows default to 'staff': there is no reliable way, after the
        // fact, to tell which point_to_point orders were started by a customer
        // versus entered by staff on their behalf, so nothing already in the
        // table is guessed into a lock it may not belong under. The distinction
        // takes effect for orders created from here on.
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('created_via');
        });
    }
};
