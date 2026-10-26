<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logistics_warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', ['main', 'warehouse', 'store', 'distribution_center', 'popup'])->default('warehouse');
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('manager_phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->json('settings')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('code');
            $table->index(['is_active', 'is_default']);
        });

        Schema::create('logistics_stock_moves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('logistics_warehouses')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('lot_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->decimal('quantity', 16, 2);
            $table->decimal('unit_cost', 16, 4)->nullable();
            $table->decimal('total_value', 16, 2)->nullable();
            $table->enum('move_type', ['incoming', 'outgoing', 'transfer', 'adjustment', 'return']);
            $table->string('reference_type'); // purchase_order, sales_order, transfer, adjustment, return, shipment
            $table->unsignedBigInteger('reference_id');
            $table->foreignId('from_warehouse_id')->nullable()->constrained('logistics_warehouses')->onDelete('cascade');
            $table->foreignId('to_warehouse_id')->nullable()->constrained('logistics_warehouses')->onDelete('cascade');
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->foreignId('moved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status')->default('completed'); // pending, completed, cancelled
            $table->timestamp('moved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index(['warehouse_id', 'product_id']);
            $table->index('lot_number');
            $table->index(['move_type', 'status']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('moved_at');
        });

        Schema::create('logistics_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('shipment_number')->unique();
            $table->enum('order_type', ['sales', 'purchase', 'transfer']);
            $table->unsignedBigInteger('order_id');
            $table->foreignId('from_warehouse_id')->nullable()->constrained('logistics_warehouses')->onDelete('set null');
            $table->foreignId('to_warehouse_id')->nullable()->constrained('logistics_warehouses')->onDelete('set null');
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->string('recipient_email')->nullable();
            $table->text('shipping_address');
            $table->string('shipping_city');
            $table->string('shipping_state');
            $table->string('shipping_postal_code');
            $table->string('shipping_country')->nullable();
            $table->string('courier')->nullable(); // dhl, ups, fedex, jandeln, gdex, poslaju, ninja
            $table->string('service_type')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('consignment_note')->nullable();
            $table->decimal('weight_kg', 8, 2)->nullable();
            $table->decimal('length_cm', 8, 2)->nullable();
            $table->decimal('width_cm', 8, 2)->nullable();
            $table->decimal('height_cm', 8, 2)->nullable();
            $table->decimal('volume_cm3', 12, 2)->nullable();
            $table->enum('package_type', ['parcel', 'envelope', 'pallet', 'custom'])->default('parcel');
            $table->integer('number_of_packages')->default(1);
            $table->decimal('declared_value', 12, 2)->nullable();
            $table->string('currency')->default('MYR');
            $table->decimal('shipping_cost', 12, 2)->nullable();
            $table->decimal('insurance_cost', 12, 2)->nullable();
            $table->string('cost_allocation_method')->default('weight'); // weight, volume, value, equal
            $table->enum('status', [
                'created',
                'label_generated',
                'picked_up',
                'in_transit',
                'out_for_delivery',
                'delivered',
                'failed',
                'returned',
                'cancelled'
            ])->default('created');
            $table->timestamp('estimated_delivery')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('signed_by')->nullable();
            $table->text('proof_of_delivery')->nullable();
            $table->json('labels')->nullable();
            $table->json('customs_documents')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('shipment_number');
            $table->index('tracking_number');
            $table->index(['status', 'courier']);
            $table->index(['order_type', 'order_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logistics_shipments');
        Schema::dropIfExists('logistics_stock_moves');
        Schema::dropIfExists('logistics_warehouses');
    }
};
