<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B-Expense P1 — Teras Pengurusan Perbelanjaan (BAB 89).
 *
 * 7 jadual teras:
 *   1. expense_categories      — kategori + pautan ke chart_of_accounts (6xxx)
 *   2. expenses                — induk perbelanjaan
 *   3. expense_items           — baris perincian
 *   4. expense_receipts        — pautan ke documents (DocumentAI OCR)
 *   5. expense_claims          — tuntutan staff
 *   6. expense_claim_items     — baris tuntutan
 *   7. expense_claim_approvals — jejak kelulusan
 *
 * ⚠️ PENTING:
 *  - Modul ini TIDAK menyimpan wang tunai. Pembayaran kekal Finance/Invoicing.
 *  - Staff upload → DRAF sahaja. Kelulusan manusia WAJIB sebelum jadi perbelanjaan sah.
 *  - Posting ke jurnal HANYA melalui Finance\JournalService (tiada logik jurnal baharu).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('chart_of_account_id')->nullable()->index();
            $table->unsignedBigInteger('parent_id')->nullable()->index();

            $table->string('code', 40);
            $table->string('name', 150);
            $table->string('name_bm', 150)->nullable();
            $table->text('description')->nullable();

            // Kawalan polisi
            $table->boolean('requires_receipt')->default(true);
            $table->decimal('receipt_threshold', 12, 2)->default(50.00);
            $table->boolean('requires_approval')->default(true);
            $table->boolean('is_billable')->default(false);
            $table->boolean('is_tax_deductible')->default(true);

            // Kata kunci untuk auto-klasifikasi AI (JSON array)
            $table->json('merchant_keywords')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code'], 'expense_categories_tenant_code_unique');
            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('expense_no', 40);

            $table->unsignedBigInteger('expense_category_id')->nullable()->index();
            $table->unsignedBigInteger('supplier_id')->nullable()->index();
            $table->unsignedBigInteger('service_project_id')->nullable()->index();
            $table->unsignedBigInteger('created_by_user_id')->nullable()->index();
            $table->unsignedBigInteger('approved_by_user_id')->nullable()->index();

            $table->string('status', 30)->default('draft');
            $table->string('payment_status', 30)->default('unpaid');
            $table->string('payment_method', 40)->nullable();

            $table->string('vendor_name', 180)->nullable();
            $table->string('vendor_tax_id', 60)->nullable();
            $table->string('vendor_invoice_no', 80)->nullable();

            $table->date('expense_date');
            $table->date('due_date')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('voided_at')->nullable();

            $table->string('currency', 3)->default('MYR');
            $table->decimal('exchange_rate', 12, 6)->default(1);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->decimal('amount_myr', 14, 2)->default(0);

            $table->string('tax_type', 20)->nullable();
            $table->decimal('tax_rate', 6, 2)->default(0);
            $table->string('tax_code', 30)->nullable();
            $table->boolean('tax_claimable')->default(true);

            $table->boolean('is_billable')->default(false);
            $table->boolean('is_recurring_child')->default(false);
            $table->unsignedBigInteger('recurring_id')->nullable()->index();
            $table->boolean('is_flagged')->default(false);
            $table->string('flag_reason', 300)->nullable();

            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->json('custom_fields')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'expense_no'], 'expenses_tenant_no_unique');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'expense_date']);
            $table->index(['tenant_id', 'expense_category_id', 'expense_date'], 'expenses_cat_date_idx');
            $table->index(['tenant_id', 'payment_status']);
        });

        Schema::create('expense_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('expense_id')->index();
            $table->unsignedBigInteger('expense_category_id')->nullable()->index();

            $table->unsignedInteger('line_number')->default(1);
            $table->string('description', 300);
            $table->decimal('quantity', 12, 3)->default(1);
            $table->string('unit', 30)->nullable();
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);

            $table->string('cost_center', 80)->nullable();
            $table->string('department', 80)->nullable();
            $table->string('project_code', 80)->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'expense_id']);
        });

        Schema::create('expense_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('expense_id')->nullable()->index();
            $table->unsignedBigInteger('expense_claim_id')->nullable()->index();
            $table->unsignedBigInteger('document_id')->nullable()->index();

            $table->string('file_path', 400);
            $table->string('file_mime', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('file_hash', 64)->nullable();

            $table->longText('ocr_text')->nullable();
            $table->decimal('ocr_confidence', 6, 2)->nullable();
            $table->json('extracted_fields')->nullable();
            $table->boolean('ocr_processed')->default(false);
            $table->timestamp('ocr_processed_at')->nullable();

            $table->string('merchant_name', 180)->nullable();
            $table->date('receipt_date')->nullable();
            $table->decimal('receipt_total', 14, 2)->nullable();
            $table->string('receipt_number', 80)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->unsignedBigInteger('verified_by_user_id')->nullable()->index();
            $table->timestamp('verified_at')->nullable();

            $table->string('duplicate_of_receipt_id', 40)->nullable();
            $table->boolean('is_duplicate_suspected')->default(false);

            $table->boolean('is_primary')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'document_id']);
            $table->index(['tenant_id', 'file_hash'], 'expense_receipts_hash_idx');
        });

        Schema::create('expense_claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('claim_no', 40);

            $table->unsignedBigInteger('claimant_user_id')->index();
            $table->unsignedBigInteger('approver_user_id')->nullable()->index();
            $table->unsignedBigInteger('delegate_user_id')->nullable()->index();
            $table->unsignedBigInteger('expense_id')->nullable()->index();
            $table->unsignedBigInteger('service_project_id')->nullable()->index();

            $table->string('title', 200);
            $table->text('purpose')->nullable();

            $table->string('status', 30)->default('draft');
            $table->string('claim_type', 30)->default('reimbursement');
            $table->string('payment_method', 40)->nullable();

            $table->date('claim_date');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->date('due_date')->nullable();

            $table->string('currency', 3)->default('MYR');
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->decimal('approved_amount', 14, 2)->default(0);
            $table->decimal('reimbursed_amount', 14, 2)->default(0);

            $table->decimal('mileage_distance_km', 10, 2)->nullable();
            $table->decimal('mileage_rate', 8, 4)->nullable();
            $table->decimal('mileage_amount', 12, 2)->nullable();

            $table->unsignedSmallInteger('per_diem_days')->nullable();
            $table->decimal('per_diem_rate', 10, 2)->nullable();
            $table->decimal('per_diem_amount', 12, 2)->nullable();
            $table->string('per_diem_location', 120)->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('reimbursed_at')->nullable();
            $table->string('rejection_reason', 500)->nullable();

            $table->boolean('is_flagged')->default(false);
            $table->string('flag_reason', 300)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'claim_no'], 'expense_claims_tenant_no_unique');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'claimant_user_id', 'status'], 'expense_claims_claimant_idx');
            $table->index(['tenant_id', 'claim_date']);
        });

        Schema::create('expense_claim_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('expense_claim_id')->index();
            $table->unsignedBigInteger('expense_category_id')->nullable()->index();

            $table->unsignedInteger('line_number')->default(1);
            $table->date('item_date');
            $table->string('description', 300);
            $table->string('vendor_name', 180)->nullable();
            $table->decimal('quantity', 12, 3)->default(1);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->decimal('approved_amount', 14, 2)->default(0);

            $table->boolean('has_receipt')->default(false);
            $table->boolean('receipt_required')->default(true);
            $table->boolean('is_billable')->default(false);
            $table->string('cost_center', 80)->nullable();
            $table->string('project_code', 80)->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'expense_claim_id']);
        });

        Schema::create('expense_claim_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('expense_claim_id')->index();
            $table->unsignedBigInteger('approver_user_id')->index();

            $table->unsignedSmallInteger('step')->default(1);
            $table->string('action', 30);
            $table->string('previous_status', 30)->nullable();
            $table->string('new_status', 30);

            $table->decimal('amount_before', 14, 2)->nullable();
            $table->decimal('amount_after', 14, 2)->nullable();
            $table->text('comment')->nullable();
            $table->string('ip_address', 45)->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'expense_claim_id', 'step'], 'expense_claim_approvals_step_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_claim_approvals');
        Schema::dropIfExists('expense_claim_items');
        Schema::dropIfExists('expense_claims');
        Schema::dropIfExists('expense_receipts');
        Schema::dropIfExists('expense_items');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};
