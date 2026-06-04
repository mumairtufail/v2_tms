<?php

use Illuminate\Contracts\Support\CanBeEscapedWhenCastToString;
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
        Schema::create('manifest_carriers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('manifest_id');
            $table->unsignedBigInteger('carrier_id');

            $table->foreign('manifest_id')->references('id')->on('manifests')->onDelete('cascade');
            $table->foreign('carrier_id')->references('id')->on('carriers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manifest_carriers');
    }
};
