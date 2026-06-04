<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('status')->default('draft');
            $table->enum('order_type', ['point_to_point', 'single_shipper', 'single_consignee', 'sequence', 'multi_stop']);
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->foreignId('company_id')->nullable()->constrained('companies');
            $table->text('special_instructions')->nullable();
            $table->string('ref_number')->nullable();
            $table->string('customer_po_number')->nullable();
            $table->string('customs_broker')->nullable();
            $table->string('port_of_entry')->nullable();
            $table->decimal('declared_value', 10, 2)->nullable();
            $table->string('container_number')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('orders');
    }
};