<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// B-Inventory & Warehouse: barang, gudang, batch, lejar stok (tambah sahaja), lapisan kos FIFO, baki, dokumen pergerakan.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('code', 20);
            $t->string('name');
            $t->enum('type', ['gudang', 'outlet', 'dropship', 'transit'])->default('gudang');
            $t->string('address')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->unique(['tenant_id', 'code']);
        });

        Schema::create('items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('sku', 40);
            $t->string('name');
            $t->string('barcode', 64)->nullable();
            $t->string('category', 80)->nullable();
            $t->string('uom', 15)->default('unit');
            $t->boolean('is_stock')->default(true);
            $t->boolean('has_batch')->default(false);
            $t->boolean('has_expiry')->default(false);
            $t->decimal('reorder_level', 18, 6)->default(0);
            $t->decimal('safety_stock', 18, 6)->default(0);
            $t->decimal('standard_cost', 18, 6)->default(0);
            $t->decimal('sell_price', 18, 2)->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->unique(['tenant_id', 'sku']);
            $t->index(['tenant_id', 'barcode']);
        });

        Schema::create('item_uoms', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('item_id')->constrained()->cascadeOnDelete();
            $t->string('uom', 15);
            $t->decimal('factor', 18, 6); // 1 uom ini = factor × uom asas
            $t->unique(['item_id', 'uom']);
        });

        Schema::create('batches', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('item_id')->constrained()->cascadeOnDelete();
            $t->string('batch_no', 40);
            $t->date('manufactured_on')->nullable();
            $t->date('expires_on')->nullable();
            $t->timestamps();
            $t->unique(['item_id', 'batch_no']);
            $t->index(['tenant_id', 'expires_on']);
        });

        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('number', 30);
            $t->enum('type', ['receipt', 'issue', 'transfer', 'adjustment', 'count', 'production', 'sale']);
            $t->enum('status', ['draft', 'posted', 'in_transit', 'received', 'cancelled'])->default('draft');
            $t->foreignId('from_warehouse_id')->nullable()->constrained('warehouses');
            $t->foreignId('to_warehouse_id')->nullable()->constrained('warehouses');
            $t->string('reason')->nullable();
            $t->string('reference', 60)->nullable();
            $t->decimal('freight_in', 18, 6)->default(0); // kos penghantaran masuk diagih ke kos barang
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('posted_at')->nullable();
            $t->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('received_at')->nullable();
            $t->timestamps();
            $t->unique(['tenant_id', 'number']);
        });

        Schema::create('stock_movement_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('stock_movement_id')->constrained()->cascadeOnDelete();
            $t->foreignId('item_id')->constrained();
            $t->string('batch_no', 40)->nullable();
            $t->date('expires_on')->nullable();
            $t->decimal('qty', 18, 6);              // kuantiti dalam uom asas (untuk count: kuantiti dikira)
            $t->decimal('unit_cost', 18, 6)->nullable();
            $t->decimal('system_qty', 18, 6)->nullable(); // count: baki sistem ketika dipos
            $t->decimal('cost_value', 18, 6)->nullable(); // nilai kos sebenar dipos
        });

        // Lejar stok: TAMBAH SAHAJA. Setiap baris = satu perubahan kuantiti & nilai di satu gudang (+batch).
        Schema::create('stock_ledger', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('item_id')->constrained();
            $t->foreignId('warehouse_id')->constrained();
            $t->foreignId('batch_id')->nullable()->constrained();
            $t->decimal('qty', 18, 6);
            $t->decimal('unit_cost', 18, 6);
            $t->decimal('value', 18, 6);
            $t->decimal('balance_qty', 18, 6);
            $t->decimal('balance_value', 18, 6);
            $t->string('source_type', 40);
            $t->unsignedBigInteger('source_id');
            $t->string('reason')->nullable();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamp('posted_at');
            $t->index(['tenant_id', 'item_id', 'warehouse_id', 'id']);
            $t->index(['source_type', 'source_id']);
        });

        // Lapisan kos FIFO (baki kuantiti setiap penerimaan).
        Schema::create('stock_layers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('item_id')->constrained();
            $t->foreignId('warehouse_id')->constrained();
            $t->foreignId('batch_id')->nullable()->constrained();
            $t->decimal('qty_remaining', 18, 6);
            $t->decimal('unit_cost', 18, 6);
            $t->timestamp('received_at');
            $t->index(['tenant_id', 'item_id', 'warehouse_id', 'received_at']);
        });

        Schema::create('stock_balances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('item_id')->constrained();
            $t->foreignId('warehouse_id')->constrained();
            $t->foreignId('batch_id')->nullable()->constrained();
            $t->decimal('qty', 18, 6)->default(0);
            $t->decimal('reserved', 18, 6)->default(0);
            $t->decimal('value', 18, 6)->default(0);
            $t->timestamps();
            $t->unique(['tenant_id', 'item_id', 'warehouse_id', 'batch_id'], 'bal_unik');
        });

        Schema::create('idempotency_keys', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('key', 100);
            $t->string('scope', 40);
            $t->json('result')->nullable();
            $t->timestamps();
            $t->unique(['tenant_id', 'scope', 'key']);
        });
    }

    public function down(): void
    {
        foreach (['idempotency_keys', 'stock_balances', 'stock_layers', 'stock_ledger', 'stock_movement_lines', 'stock_movements', 'batches', 'item_uoms', 'items', 'warehouses'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
