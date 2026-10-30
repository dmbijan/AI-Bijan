<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pesanan meja (QR): pelanggan imbas QR di meja, pesan dari menu, pesanan masuk dapur,
 * kemudian dibayar di kaunter POS.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 60);
            $table->string('token', 40)->unique(); // dalam QR; boleh dijana semula kalau bocor
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('tenant_id');
        });

        Schema::create('pos_table_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('table_id')->constrained('pos_tables')->cascadeOnDelete();
            $table->ulid('public_id')->unique(); // pelanggan semak status pesanan dengan ini
            $table->string('customer_name', 80)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('baru'); // baru → dimasak → siap → dihantar → selesai | batal
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->foreignId('sale_id')->nullable()->constrained('pos_sales')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('pos_table_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('pos_table_orders')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->string('name');
            $table->decimal('quantity', 10, 3);
            $table->decimal('unit_price', 15, 2);
            $table->string('note', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_table_order_items');
        Schema::dropIfExists('pos_table_orders');
        Schema::dropIfExists('pos_tables');
    }
};
