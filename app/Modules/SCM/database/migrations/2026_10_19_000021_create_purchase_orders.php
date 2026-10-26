<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create purchase_orders table.
     * 
     * Purchase Order lifecycle:
     * Draft → Pending Approval → Approved → Sent to Supplier → Received → Closed
     */
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            
            // PO identification
            $table->string('po_number')->unique()->comment('PO-YYYY-NNNNN');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            
            // Dates
            $table->date('order_date')->index();
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            
            // Status tracking
            $table->enum('status', [
                'draft', 'pending_approval', 'approved', 'sent', 
                'partially_received', 'fully_received', 'cancelled'
            ])->default('draft')->index();
            
            // Financial details
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0)->comment('SST if applicable');
            $table->decimal('shipping_cost', 18, 2)->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->string('currency', 3)->default('MYR');
            
            // Payment terms
            $table->integer('payment_terms_days')->default(30);
            $table->date('payment_due_date')->nullable();
            
            // Shipping information
            $table->string('shipping_method')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('tracking_number')->nullable();
            
            // Notes
            $table->text('internal_notes')->nullable();
            $table->text('terms_conditions')->nullable();
            
            // Approval
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            
            // Journal entry link (for auto accounting)
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'supplier_id']);
            $table->index(['tenant_id', 'expected_delivery_date']);
        });
        
        // Purchase order line items
        Schema::create('purchase_order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            
            // Item reference
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete(); // From inventory module
            
            // Line details
            $table->string('item_code')->nullable();
            $table->string('item_name');
            $table->text('description')->nullable();
            
            // Quantities & pricing
            $table->integer('quantity_ordered')->default(0);
            $table->decimal('unit_price', 18, 2)->default(0);
            $table->string('uom')->default('pcs'); // Unit of measure
            
            $table->decimal('quantity_received', 18, 2)->default(0);
            $table->decimal('line_total', 18, 2)->default(0);
            
            // Tax
            $table->boolean('is_taxable')->default(false);
            $table->decimal('tax_rate', 5, 2)->default(0)->comment('SST rate');
            $table->decimal('tax_amount', 18, 2)->default(0);
            
            // Delivery tracking
            $table->integer('delivery_status')->default(0)->comment('0=not_delivered, 1=partial, 2=full');
            
            $table->timestamps();
        });
        
        // Purchase order approvals
        Schema::create('purchase_order_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('approver_id')->constrained('users')->cascadeOnDelete();
            
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('comments')->nullable();
            $table->timestamp('decided_at')->nullable();
            
            $table->unique(['purchase_order_id', 'approver_id'], 'po_approver_unique');
            $table->timestamps();
        });
        
        // Goods received notes (GRN)
        Schema::create('goods_received_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            
            $table->string('grn_number')->unique()->comment('GRN-YYYY-NNNNN');
            $table->date('received_date')->index();
            
            // Received quantities per line
            // Will be stored in goods_received_note_lines
            
            $table->text('notes')->nullable();
            $table->enum('condition', ['good', 'damaged', 'short_shipped'])->default('good');
            
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
        });
        
        // GRN line items
        Schema::create('goods_received_note_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_received_note_id')->constrained('goods_received_notes')->cascadeOnDelete();
            $table->foreignId('purchase_order_line_id')->nullable()->constrained('purchase_order_lines')->nullOnDelete();
            
            $table->string('item_code')->nullable();
            $table->string('item_name');
            
            $table->integer('ordered_quantity')->default(0);
            $table->integer('received_quantity')->default(0);
            $table->integer('rejected_quantity')->default(0);
            
            $table->string('uom')->default('pcs');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_received_note_lines');
        Schema::dropIfExists('goods_received_notes');
        Schema::dropIfExists('purchase_order_approvals');
        Schema::dropIfExists('purchase_order_lines');
        Schema::dropIfExists('purchase_orders');
    }
};
