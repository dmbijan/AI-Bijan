<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BAB 88 — CIRI B-POS DIPINDAHKAN KE MODUL POS AI BIJAN (T-76 → T-83).
 *
 * Sumber: `b-pos-system/` (huluan MIT: aryadwiputra/point-of-sales; lapisan
 * tambahan proprietari Bijan Digital Marketing). Kedua-dua sistem milik pemilik
 * yang sama — lihat `THIRD-PARTY-NOTICES.md`.
 *
 * Ciri dipindahkan SATU PER SATU ke modul `POS/` sedia ada — bukan modul baharu,
 * kerana `pos_sales`/`pos_registers`/`cash_shifts` sudah wujud di sini.
 *
 * PERBEZAAN SENGAJA daripada B-POS (docs/TODO.md §Nota Integrasi B-POS):
 *   • `tenant_id` (bukan `outlet_id`) — pengasingan peringkat syarikat.
 *   • Semua jadual anak guna prefix `pos_` (elak bertindih — nota projek).
 *   • Tiada `access_token` uuid kekal untuk pesanan dine-in awam; guna token
 *     ber-HASH + tempoh luput (corak `document_shares`).
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->modifier();
        $this->kolumJualan();
        $this->dineIn();
        $this->syif();
        $this->tngDanDiskaun();
    }
    // ─────────────────────────────────────────────────────────────
    // T-77 — MODIFIER / ADD-ON
    // ─────────────────────────────────────────────────────────────
    private function modifier(): void
    {
        Schema::create('pos_modifier_groups', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->string('name');
            $t->unsignedTinyInteger('min_select')->default(0);   // >0 = wajib
            $t->unsignedTinyInteger('max_select')->default(1);   // 0 = tiada had
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();

            $t->index(['tenant_id', 'is_active']);
        });

        Schema::create('pos_modifier_options', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->foreignId('modifier_group_id')->constrained('pos_modifier_groups')->cascadeOnDelete();
            $t->string('name');
            $t->decimal('price', 15, 2)->default(0);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();

            $t->index(['tenant_id', 'modifier_group_id']);
        });

        Schema::create('pos_product_modifiers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $t->foreignId('modifier_group_id')->constrained('pos_modifier_groups')->cascadeOnDelete();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();

            $t->unique(['item_id', 'modifier_group_id']);
            $t->index('tenant_id');
        });
    }

    // ─────────────────────────────────────────────────────────────
    // T-78 / T-79 / T-81 / T-83 — KOLUM TAMBAHAN pos_sales
    // ─────────────────────────────────────────────────────────────
    private function kolumJualan(): void
    {
        Schema::table('pos_sales', function (Blueprint $t) {
            $t->decimal('service_charge_amount', 15, 2)->default(0);
            $t->decimal('service_charge_rate', 5, 2)->nullable();
            $t->string('payment_method', 30)->nullable();
            $t->string('payment_status', 20)->default('unpaid');
            $t->uuid('client_uuid')->nullable();
            $t->string('sync_fingerprint', 64)->nullable();
            $t->unsignedBigInteger('discount_approved_by')->nullable();
            $t->timestamp('discount_approved_at')->nullable();

            $t->index(['tenant_id', 'client_uuid']);
            $t->index(['tenant_id', 'sync_fingerprint']);
            $t->index(['tenant_id', 'payment_status']);
        });

        Schema::table('pos_sale_items', function (Blueprint $t) {
            $t->json('modifiers')->nullable();
            $t->decimal('modifier_total', 15, 2)->default(0);
        });

        Schema::create('pos_sale_tenders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->foreignId('sale_id')->constrained('pos_sales')->cascadeOnDelete();
            $t->string('method', 20);
            $t->decimal('amount', 15, 2);
            $t->decimal('cash_received', 15, 2)->nullable();
            $t->decimal('change_amount', 15, 2)->default(0);
            $t->string('payment_status', 20)->default('paid');
            $t->string('reference')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'sale_id']);
        });
    }

    // ─────────────────────────────────────────────────────────────
    // T-76 — DINE-IN + KITCHEN DISPLAY
    // ─────────────────────────────────────────────────────────────
    private function dineIn(): void
    {
        Schema::create('pos_dine_areas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->string('name');
            $t->string('code', 30)->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();

            $t->index(['tenant_id', 'is_active']);
        });

        Schema::create('pos_dine_tables', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->foreignId('dine_area_id')->constrained('pos_dine_areas')->cascadeOnDelete();
            $t->string('name');
            $t->string('code', 30)->nullable();
            $t->unsignedSmallInteger('seats')->default(4);
            $t->string('status', 20)->default('free');    // free|occupied|reserved
            $t->boolean('is_active')->default(true);
            $t->timestamps();

            $t->index(['tenant_id', 'dine_area_id']);
            $t->unique(['tenant_id', 'name']);
        });

        Schema::create('pos_dine_orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->foreignId('dine_table_id')->constrained('pos_dine_tables')->cascadeOnDelete();
            $t->foreignId('sale_id')->nullable()->constrained('pos_sales')->nullOnDelete();
            $t->unsignedBigInteger('customer_id')->nullable();
            $t->unsignedBigInteger('cashier_id')->nullable();

            $t->string('order_no', 40);
            $t->string('status', 20)->default('submitted');
            $t->string('payment_option', 30)->default('pay_at_counter');

            // Token awam ber-HASH + luput (B-POS guna uuid kekal — ditolak)
            $t->string('access_token_hash', 64)->nullable();
            $t->timestamp('access_expires_at')->nullable();

            $t->decimal('subtotal', 15, 2)->default(0);
            $t->unsignedSmallInteger('item_count')->default(0);
            $t->text('notes')->nullable();
            $t->text('rejection_reason')->nullable();
            $t->timestamp('submitted_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->index(['tenant_id', 'dine_table_id']);
        });

        Schema::create('pos_dine_order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->foreignId('dine_order_id')->constrained('pos_dine_orders')->cascadeOnDelete();
            $t->unsignedBigInteger('item_id')->nullable();
            $t->string('item_name');
            $t->decimal('unit_price', 15, 2);
            $t->unsignedInteger('quantity');
            $t->json('modifiers')->nullable();
            $t->decimal('modifier_total', 15, 2)->default(0);
            $t->decimal('line_total', 15, 2);
            $t->text('notes')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'dine_order_id']);
        });

        Schema::table('pos_sales', function (Blueprint $t) {
            $t->string('order_type', 20)->default('takeaway');
            $t->string('kitchen_status', 20)->nullable();
            $t->timestamp('kitchen_queued_at')->nullable();
            $t->timestamp('kitchen_started_at')->nullable();
            $t->timestamp('kitchen_ready_at')->nullable();
            $t->timestamp('kitchen_served_at')->nullable();
            $t->foreignId('dine_table_id')->nullable()->constrained('pos_dine_tables')->nullOnDelete();

            $t->index(['tenant_id', 'kitchen_status']);
        });
    }

    // ─────────────────────────────────────────────────────────────
    // T-80 — CASH MOVEMENT + RINGKASAN SYIF
    // ─────────────────────────────────────────────────────────────
    private function syif(): void
    {
        Schema::create('pos_shift_cash_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->foreignId('cash_shift_id')->constrained('cash_shifts')->cascadeOnDelete();
            $t->string('type', 20);        // drop|pickup|expense|adjustment
            $t->decimal('amount', 15, 2);
            $t->string('reason')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'cash_shift_id']);
        });

        Schema::table('cash_shifts', function (Blueprint $t) {
            $t->unsignedBigInteger('user_id')->nullable();
            $t->decimal('actual_cash', 15, 2)->nullable();
            $t->decimal('cash_sales_total', 15, 2)->default(0);
            $t->decimal('non_cash_sales_total', 15, 2)->default(0);
            $t->unsignedInteger('transactions_count')->default(0);
            $t->text('close_notes')->nullable();
            $t->timestamp('locked_at')->nullable();
        });
    }

    // ─────────────────────────────────────────────────────────────
    // T-82 — TNG SOUNDBOX   ·   T-83 — DISCOUNT APPROVAL
    // ─────────────────────────────────────────────────────────────
    private function tngDanDiskaun(): void
    {
        Schema::create('pos_tng_notifications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->decimal('amount', 15, 2);
            $t->string('reference', 100)->nullable();
            $t->string('raw_message', 500)->nullable();
            $t->string('status', 20)->default('unclaimed'); // matched|unclaimed|ignored
            $t->unsignedBigInteger('sale_id')->nullable();
            $t->unsignedBigInteger('matched_by')->nullable();
            $t->timestamp('received_at')->nullable();
            $t->timestamp('matched_at')->nullable();
            $t->timestamps();

            // Idempotency webhook: satu notifikasi luaran hanya sekali
            $t->unique(['tenant_id', 'reference']);
            $t->index(['tenant_id', 'status']);
        });

        Schema::create('pos_discount_approvals', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->foreignId('sale_id')->nullable()->constrained('pos_sales')->nullOnDelete();
            $t->unsignedBigInteger('requested_by')->nullable();
            $t->unsignedBigInteger('approved_by')->nullable();
            $t->decimal('discount_percent', 5, 2)->default(0);
            $t->decimal('discount_amount', 15, 2)->default(0);
            $t->string('status', 20)->default('pending');   // pending|approved|rejected
            $t->text('reason')->nullable();
            $t->timestamp('decided_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
        });
    }


    public function down(): void


    {
        Schema::dropIfExists('pos_discount_approvals');
        Schema::dropIfExists('pos_tng_notifications');
        Schema::dropIfExists('pos_shift_cash_movements');
        Schema::dropIfExists('pos_dine_order_items');
        Schema::dropIfExists('pos_dine_orders');
        Schema::dropIfExists('pos_dine_tables');
        Schema::dropIfExists('pos_dine_areas');
        Schema::dropIfExists('pos_sale_tenders');
        Schema::dropIfExists('pos_product_modifiers');
        Schema::dropIfExists('pos_modifier_options');
        Schema::dropIfExists('pos_modifier_groups');

        Schema::table('cash_shifts', function (Blueprint $t) {
            $t->dropColumn([
                'user_id', 'actual_cash', 'cash_sales_total', 'non_cash_sales_total',
                'transactions_count', 'close_notes', 'locked_at',
            ]);
        });

        Schema::table('pos_sale_items', function (Blueprint $t) {
            $t->dropColumn(['modifiers', 'modifier_total']);
        });

        Schema::table('pos_sales', function (Blueprint $t) {
            $t->dropColumn([
                'service_charge_amount', 'service_charge_rate', 'payment_method',
                'payment_status', 'client_uuid', 'sync_fingerprint',
                'discount_approved_by', 'discount_approved_at',
                'order_type', 'kitchen_status', 'kitchen_queued_at', 'kitchen_started_at',
                'kitchen_ready_at', 'kitchen_served_at', 'dine_table_id',
            ]);
        });
    }
};
