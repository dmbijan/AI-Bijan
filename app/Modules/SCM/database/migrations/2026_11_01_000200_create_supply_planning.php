<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Kelompok 2 stok: pesanan semula (SUP-014), ramalan permintaan (SUP-011), kos pendaratan (SUP-015), maklumat produk (SUP-013).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $t) {
            // SUP-014
            $t->unsignedSmallInteger('lead_time_days')->default(7);
            $t->decimal('reorder_qty', 18, 6)->default(0); // 0 = kira automatik
            $t->decimal('min_order_qty', 18, 6)->default(0);
            $t->foreignId('preferred_supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            // SUP-013 PIM
            $t->foreignId('parent_id')->nullable()->constrained('items')->nullOnDelete();
            $t->string('brand', 80)->nullable();
            $t->text('description')->nullable();
            $t->json('images')->nullable();         // [url]
            $t->json('attributes')->nullable();     // {"Saiz":"M","Warna":"Merah"}
            $t->unsignedInteger('weight_g')->nullable();
            $t->json('channel_content')->nullable(); // {"shopee":{"title":..,"description":..}}
        });

        Schema::create('landed_costs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('number', 30);
            $t->foreignId('stock_movement_id')->constrained('stock_movements');
            $t->string('method', 10)->default('value'); // value, qty, weight
            $t->string('status', 10)->default('draft'); // draft, posted
            $t->decimal('total', 18, 2)->default(0);
            $t->decimal('capitalised', 18, 2)->default(0);
            $t->decimal('expensed', 18, 2)->default(0); // bahagian untuk stok yang sudah terjual → COGS
            $t->string('notes')->nullable();
            $t->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('posted_at')->nullable();
            $t->timestamps();
            $t->unique(['tenant_id', 'number']);
        });

        Schema::create('landed_cost_charges', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('landed_cost_id')->constrained()->cascadeOnDelete();
            $t->string('type', 20); // freight, duty, insurance, clearance, other
            $t->string('description')->nullable();
            $t->decimal('amount', 18, 2);
        });

        Schema::create('landed_cost_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('landed_cost_id')->constrained()->cascadeOnDelete();
            $t->foreignId('item_id')->constrained();
            $t->foreignId('warehouse_id')->constrained();
            $t->decimal('qty', 18, 6);
            $t->decimal('base_value', 18, 6);
            $t->decimal('allocated', 18, 6)->default(0);
            $t->decimal('capitalised', 18, 6)->default(0);
            $t->decimal('expensed', 18, 6)->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landed_cost_lines');
        Schema::dropIfExists('landed_cost_charges');
        Schema::dropIfExists('landed_costs');
        Schema::table('items', function (Blueprint $t) {
            $t->dropConstrainedForeignId('preferred_supplier_id');
            $t->dropConstrainedForeignId('parent_id');
            $t->dropColumn(['lead_time_days', 'reorder_qty', 'min_order_qty', 'brand', 'description', 'images', 'attributes', 'weight_g', 'channel_content']);
        });
    }
};
