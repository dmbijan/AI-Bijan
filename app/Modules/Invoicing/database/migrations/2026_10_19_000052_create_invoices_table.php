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
        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id');
            $table->string('invoice_number', 50)->unique();
            
            // Source
            $table->unsignedBigInteger('sales_order_id')->nullable();
            $table->unsignedBigInteger('quotation_id')->nullable();
            
            // Customer info
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name', 255)->nullable();
            $table->string('customer_email', 255)->nullable();
            $table->string('customer_tax_id', 50)->nullable(); // Tax ID / SST number
            
            // Addresses
            $table->text('billing_address')->nullable();
            
            // Dates
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            
            // Financials
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('tax_total', 15, 2)->default(0.00);
            $table->decimal('discount_total', 15, 2)->default(0.00);
            $table->decimal('shipping_total', 15, 2)->default(0.00);
            $table->decimal('grand_total', 15, 2)->default(0.00);
            $table->decimal('amount_paid', 15, 2)->default(0.00);
            $table->decimal('amount_outstanding', 15, 2)->default(0.00);
            $table->string('currency', 3)->default('MYR');
            $table->decimal('exchange_rate', 10, 6)->default(1.0);
            
            // Content
            $table->text('notes')->nullable();
            $table->text('terms_conditions')->nullable();
            
            // Status
            $table->string('status', 20)->default('draft'); // draft, issued, partial, paid, overdue, cancelled
            
            // Payment tracking
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('overdue_notified_at')->nullable();
            
            // e-Invois LHDN fields
            $table->string('einvois_submission_status', 20)->default('pending'); // pending, submitted, accepted, rejected, cancelled
            $table->string('einvois_reference_no', 100)->nullable(); // UUID from LHDN
            $table->timestamp('einvois_submission_date')->nullable();
            $table->text('einvois_rejection_reason')->nullable();
            $table->timestamp('einvois_cancelled_at')->nullable();
            
            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('tenant_id');
            $table->index('status');
            $table->index('due_date');
            $table->index('einvois_submission_status');
            $table->index(['tenant_id', 'created_at']);
            
            // Foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('sales_order_id')->references('id')->on('sales_orders')->onDelete('set null');
            $table->foreign('quotation_id')->references('id')->on('quotations')->onDelete('set null');
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('invoice_id');
            
            // Product info
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name', 255)->nullable();
            $table->string('product_sku', 100)->nullable();
            $table->text('description')->nullable();
            
            // Line details
            $table->decimal('quantity', 12, 3)->default(1.000);
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('discount_percentage', 5, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('tax_percentage', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('line_total', 15, 2)->default(0.00);
            
            $table->timestamps();
            
            // Indexes
            $table->index('invoice_id');
            
            // Foreign keys
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
