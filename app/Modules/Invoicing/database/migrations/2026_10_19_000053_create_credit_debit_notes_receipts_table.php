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
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id');
            $table->string('credit_note_number', 50)->unique();
            
            // Reference
            $table->unsignedBigInteger('invoice_id')->nullable();
            
            // Customer info
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name', 255)->nullable();
            $table->string('customer_email', 255)->nullable();
            
            // Reason & amounts
            $table->string('reason', 100); // return, damage, overcharge, discount
            $table->decimal('original_amount', 15, 2)->default(0.00);
            $table->decimal('credit_amount', 15, 2)->default(0.00);
            $table->decimal('applied_amount', 15, 2)->default(0.00);
            $table->decimal('remaining_credit', 15, 2)->default(0.00);
            $table->string('currency', 3)->default('MYR');
            
            // Content
            $table->text('notes')->nullable();
            
            // Status & dates
            $table->string('status', 20)->default('draft'); // draft, issued, applied, expired
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            
            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('tenant_id');
            $table->index('status');
            $table->index(['tenant_id', 'created_at']);
            
            // Foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
        });

        Schema::create('debit_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id');
            $table->string('debit_note_number', 50)->unique();
            
            // Reference
            $table->unsignedBigInteger('invoice_id')->nullable();
            
            // Customer info
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name', 255)->nullable();
            $table->string('customer_email', 255)->nullable();
            
            // Reason & amount
            $table->string('reason', 100); // undercharge, additional services, price error, tax adjustment
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->string('currency', 3)->default('MYR');
            
            // Content
            $table->text('notes')->nullable();
            
            // Status & dates
            $table->string('status', 20)->default('draft'); // draft, issued, paid, cancelled
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('tenant_id');
            $table->index('status');
            $table->index(['tenant_id', 'created_at']);
            
            // Foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
        });

        Schema::create('receipts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id');
            $table->string('receipt_number', 50)->unique();
            
            // Reference
            $table->unsignedBigInteger('invoice_id')->nullable();
            
            // Customer info
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name', 255)->nullable();
            $table->string('customer_email', 255)->nullable();
            
            // Payment details
            $table->string('payment_method', 50); // cash, fpx, card, bank_transfer, check
            $table->string('reference_number', 100)->nullable();
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->string('currency', 3)->default('MYR');
            
            // Content
            $table->text('notes')->nullable();
            
            // Date
            $table->timestamp('received_at')->nullable();
            
            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('tenant_id');
            $table->index('invoice_id');
            $table->index(['tenant_id', 'created_at']);
            
            // Foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('debit_notes');
        Schema::dropIfExists('credit_notes');
    }
};
