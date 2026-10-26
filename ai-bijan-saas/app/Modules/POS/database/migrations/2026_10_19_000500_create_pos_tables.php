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
        // POS Registers table
        Schema::create('pos_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique(); // POS-REG-001
            $table->text('description')->nullable();
            $table->string('location')->nullable(); // e.g., "Main Counter", "Floor 1"
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('is_active');
        });

        // Cash Shifts table (shift management for each register)
        Schema::create('cash_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('register_id')->constrained('pos_registers')->cascadeOnDelete();
            $table->foreignId('opened_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['open', 'closed', 'locked'])->default('open');
            $table->decimal('opening_cash', 15, 2)->default(0); // Initial float
            $table->decimal('closing_cash', 15, 2)->nullable();
            $table->decimal('expected_cash', 15, 2)->nullable();
            $table->decimal('cash_discrepancy', 15, 2)->nullable(); // Actual - Expected
            $table->date('shift_date')->default(now());
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('register_id');
            $table->index('status');
        });

        // Sales table (main sale/transaction)
        Schema::create('pos_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('register_id')->constrained('pos_registers')->cascadeOnDelete();
            $table->foreignId('cash_shift_id')->nullable()->constrained('cash_shifts')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('crm_clients')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Cashier
            $table->string('sale_no')->unique(); // SALE-2026-000001
            $table->enum('status', ['draft', 'completed', 'cancelled', 'refunded', 'partially_refunded'])->default('draft');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('change_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->date('sale_date')->default(now());
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('register_id');
            $table->index('cash_shift_id');
            $table->index('status');
            $table->index('sale_date');
        });

        // Sale items table (line items in a sale)
        Schema::create('pos_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('pos_sales')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('inventory_products')->nullOnDelete();
            $table->string('product_name');
            $table->string('product_sku')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2);
            $table->timestamps();

            $table->index('sale_id');
        });

        // Sale payments table (multiple payment methods per sale)
        Schema::create('pos_sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('pos_sales')->cascadeOnDelete();
            $table->enum('payment_method', ['cash', 'card', 'ewallet', 'credit', 'voucher', 'split']);
            $table->decimal('amount', 15, 2);
            $table->string('reference_number')->nullable(); // Transaction ref for card/ewallet
            $table->json('payment_details')->nullable(); // Additional info (e.g., card last 4 digits)
            $table->timestamps();

            $table->index('sale_id');
        });

        // Sale discounts table (tracking individual discounts applied)
        Schema::create('pos_sale_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('pos_sales')->cascadeOnDelete();
            $table->enum('type', ['percentage', 'fixed', 'loyalty', 'voucher', 'manual']);
            $table->string('code')->nullable(); // Discount code if applicable
            $table->decimal('value', 15, 2); // Percentage or fixed amount
            $table->decimal('amount', 15, 2); // Final discount amount
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('sale_id');
        });

        // Sale returns table (for refunds/returns)
        Schema::create('pos_sale_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('sale_id')->constrained('pos_sales')->cascadeOnDelete();
            $table->foreignId('returned_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('return_no')->unique(); // RET-2026-000001
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->decimal('return_amount', 15, 2);
            $table->text('reason');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('sale_id');
            $table->index('status');
        });

        // Receipt templates
        Schema::create('pos_receipt_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->text('header')->nullable();
            $table->text('footer')->nullable();
            $table->boolean('print_logo')->default(false);
            $table->string('logo_path')->nullable();
            $table->boolean('is_default')->default(true);
            $table->timestamps();

            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_receipt_templates');
        Schema::dropIfExists('pos_sale_returns');
        Schema::dropIfExists('pos_sale_discounts');
        Schema::dropIfExists('pos_sale_payments');
        Schema::dropIfExists('pos_sale_items');
        Schema::dropIfExists('pos_sales');
        Schema::dropIfExists('cash_shifts');
        Schema::dropIfExists('pos_registers');
    }
};
