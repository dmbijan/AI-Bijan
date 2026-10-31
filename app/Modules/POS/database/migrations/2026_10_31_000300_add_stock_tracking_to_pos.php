<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jualan POS potong stok: kaunter boleh ditetapkan ke gudang, dan setiap baris jualan
 * menyimpan status potongan stok (untuk senarai semakan bila stok tak cukup).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_registers', function (Blueprint $t) {
            $t->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
        });

        Schema::table('pos_sale_items', function (Blueprint $t) {
            // deducted | insufficient | not_stock | no_warehouse
            $t->string('stock_status', 20)->nullable();
            $t->string('stock_note')->nullable();
            $t->decimal('cogs', 18, 4)->nullable();
            $t->index('stock_status');
        });
    }

    public function down(): void
    {
        Schema::table('pos_sale_items', function (Blueprint $t) {
            $t->dropIndex(['stock_status']);
            $t->dropColumn(['stock_status', 'stock_note', 'cogs']);
        });

        Schema::table('pos_registers', function (Blueprint $t) {
            $t->dropConstrainedForeignId('warehouse_id');
        });
    }
};
