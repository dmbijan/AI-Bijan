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
        Schema::create('recurring_billings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id');
            
            // Customer info
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name', 255)->nullable();
            $table->string('customer_email', 255)->nullable();
            
            // Billing type & frequency
            $table->string('billing_type', 50)->default('subscription'); // subscription, retainer, usage-based, milestone
            $table->string('frequency', 30)->default('monthly'); // daily, weekly, monthly, quarterly, annually
            $table->integer('interval')->default(1); // every N periods
            
            // Dates
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('next_billing_date')->nullable();
            
            // Amounts
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->decimal('tax_percentage', 5, 2)->default(0.00);
            $table->decimal('grand_total', 15, 2)->default(0.00);
            $table->string('currency', 3)->default('MYR');
            
            // Automation settings
            $table->boolean('auto_send_invoice')->default(false);
            $table->boolean('auto_send_reminder')->default(false);
            $table->integer('reminder_days_before')->default(7);
            
            // Status
            $table->string('status', 20)->default('active'); // active, paused, completed, cancelled
            
            // Tracking
            $table->integer('total_invoices_generated')->default(0);
            $table->date('last_invoice_date')->nullable();
            $table->date('last_billing_date')->nullable();
            
            // Content
            $table->text('notes')->nullable();
            
            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('tenant_id');
            $table->index('status');
            $table->index('next_billing_date');
            $table->index(['tenant_id', 'created_at']);
            
            // Foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_billings');
    }
};
