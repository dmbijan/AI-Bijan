<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// B-BOM & Manufacturing: resipi/BOM berversi (pengeluaran atau kit jualan), work order & bahan digunakan.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boms', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('item_id')->constrained();
            $t->unsignedInteger('version')->default(1);
            $t->enum('type', ['manufacture', 'kit']); // kit = tolak bahan automatik semasa jualan
            $t->decimal('quantity', 18, 6)->default(1); // hasil keluaran bagi resipi ini
            $t->decimal('labour_cost', 18, 6)->default(0);   // bagi setiap 'quantity'
            $t->decimal('overhead_cost', 18, 6)->default(0); // bagi setiap 'quantity'
            $t->boolean('is_active')->default(true);
            $t->boolean('is_default')->default(false);
            $t->string('notes')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->unique(['tenant_id', 'item_id', 'version']);
        });

        Schema::create('bom_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('bom_id')->constrained()->cascadeOnDelete();
            $t->foreignId('item_id')->constrained();
            $t->decimal('qty', 18, 6);
            $t->decimal('wastage_pct', 8, 4)->default(0);
        });

        Schema::create('work_orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('number', 30);
            $t->foreignId('bom_id')->constrained();
            $t->foreignId('item_id')->constrained();
            $t->decimal('qty_planned', 18, 6);
            $t->foreignId('source_warehouse_id')->constrained('warehouses');
            $t->foreignId('target_warehouse_id')->constrained('warehouses');
            $t->enum('status', ['draft', 'released', 'completed', 'cancelled'])->default('draft');
            $t->decimal('qty_good', 18, 6)->nullable();
            $t->decimal('qty_reject', 18, 6)->nullable();
            $t->decimal('material_cost', 18, 6)->nullable();
            $t->decimal('labour_cost', 18, 6)->nullable();
            $t->decimal('overhead_cost', 18, 6)->nullable();
            $t->decimal('unit_cost', 18, 6)->nullable();
            $t->decimal('standard_unit_cost', 18, 6)->nullable();
            $t->decimal('variance', 18, 6)->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
            $t->unique(['tenant_id', 'number']);
        });

        Schema::create('work_order_materials', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('item_id')->constrained();
            $t->decimal('qty_required', 18, 6);
            $t->decimal('qty_consumed', 18, 6)->nullable();
            $t->decimal('cost', 18, 6)->nullable();
        });
    }

    public function down(): void
    {
        foreach (['work_order_materials', 'work_orders', 'bom_lines', 'boms'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
