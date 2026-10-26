<?php

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
        // Cost components table — individual cost items per product
        Schema::create('cogs_cost_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            
            $table->string('component_type'); // raw_material, direct_labor, etc.
            $table->string('description')->nullable();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->boolean('is_allocated')->default(false);
            $table->string('allocation_base')->nullable();
            $table->decimal('allocation_percentage', 5, 2)->nullable();
            
            // Source tracking
            $table->string('source_document')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('source_type')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'product_id']);
            $table->index(['tenant_id', 'component_type']);
        });

        // Product costs table — calculated/recorded costs per product per period
        Schema::create('cogs_product_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            
            $table->date('cost_date');
            $table->string('method'); // fifo, lifo, weighted_average, specific_id
            
            // Direct costs
            $table->decimal('raw_material_cost', 15, 2)->default(0);
            $table->decimal('direct_labor_cost', 15, 2)->default(0);
            $table->decimal('manufacturing_overhead_cost', 15, 2)->default(0);
            $table->decimal('packaging_cost', 15, 2)->default(0);
            $table->decimal('shipping_in_cost', 15, 2)->default(0);
            $table->decimal('duties_taxes_cost', 15, 2)->default(0);
            
            // Calculated totals
            $table->decimal('total_direct_cost', 15, 2)->default(0);
            $table->decimal('allocated_overhead', 15, 2)->default(0);
            $table->decimal('total_unit_cost', 15, 2)->default(0);
            $table->integer('quantity_produced')->default(0);
            
            // Margin data
            $table->decimal('selling_price', 15, 2)->nullable();
            $table->decimal('gross_margin', 15, 2)->nullable();
            $table->decimal('gross_margin_percentage', 5, 2)->nullable();
            
            // Status
            $table->string('status')->default('draft'); // draft, finalized, archived
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'product_id', 'cost_date']);
            $table->index(['tenant_id', 'status']);
        });

        // COGS transactions table — actual COGS entries from sales
        Schema::create('cogs_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            
            $table->date('transaction_date');
            $table->string('transaction_type'); // sale_return, adjustment, write_off
            $table->integer('quantity');
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('total_cogs', 15, 2);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'transaction_date']);
            $table->index(['tenant_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cogs_transactions');
        Schema::dropIfExists('cogs_product_costs');
        Schema::dropIfExists('cogs_cost_components');
    }
};
