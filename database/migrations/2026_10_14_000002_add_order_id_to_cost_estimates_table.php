<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Attributes a carrier-cost line to the order that entered it.
 *
 * A manifest can carry stops from several orders, and carrier cost is typed
 * per order (the order's own "Carrier Cost" panel, OrderUpdateService::
 * applyCarrierCostToManifest). Until now that write replaced the manifest's
 * ENTIRE cost_estimates list regardless of who it belonged to - so entering
 * $233 while editing order A, then $150 while editing order B on the same
 * manifest, silently deleted order A's rows: whichever order was saved last
 * won, and it won for every order on the trip. The per-order "financials"
 * view then divided whatever was left by stop count, while the rate
 * confirmation PDF showed the (wrong, clobbered) raw total - two numbers
 * that never agreed with each other or with what was actually typed.
 *
 * order_id lets a replace scope itself to the order that made it, so two
 * orders sharing a manifest keep their own figures. Null stays meaningful:
 * it is a cost entered on the manifest itself (ManifestController::
 * addCostEstimates, or the manifest edit screen), not attributed to any one
 * order, and is still split by stop share across the orders on the trip.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cost_estimates', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('manifest_id')->constrained()->nullOnDelete();
            $table->index(['manifest_id', 'order_id']);
        });

        // Existing rows predate this column and cannot be attributed to a
        // single order after the fact (several orders may already share the
        // manifest they sit on) - they stay null: "on this manifest, no
        // order in particular", same as the no-op query that wrote them.
    }

    public function down(): void
    {
        Schema::table('cost_estimates', function (Blueprint $table) {
            $table->dropIndex(['manifest_id', 'order_id']);
            $table->dropConstrainedForeignId('order_id');
        });
    }
};
