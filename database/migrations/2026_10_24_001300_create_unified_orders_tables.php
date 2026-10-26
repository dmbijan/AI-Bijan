<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Unified Order Management (BAB 86 / Idea #19) — SATU tempat untuk semua pesanan.
 *
 * ✅ Didapati daripada audit pesaing (BAB 84/85):
 * - Shopify, Lazada, Shopee, TikTok Shop, POS, B-Site, B-Booking semua ada pesanan
 *   masing-masing - tenant tidak perlu melihat 7 tempat untuk satu jawapan "berapa jualan hari ini?"
 * - ReStructure AI menjalankan "agent teams" per jabatan — tetapi AI BIJAN perlu
 *   lapisan unifikasi terlebih dahulu.
 *
 * Ciri:
 * - Pesanan unified: semua sumber (marketplace, POS, site, booking, COD) dalam 1 jadual
 * - Line item unified + rizab stok automatik
 * - Status kanonikal merentas semua platform
 * - Stok synced: setiap sales_channel_binding tetapkan policies
 * - Timeline audit setiap perubahan status / setiap sync
 */
return new class extends Migration
{
    public function up(): void
    {
        // Pesanan unified — satu jadual untuk semua sumber
        Schema::create('unified_orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('order_no')->unique();               // nombor unified (bukan nombor platform)
            $t->string('source_order_no')->nullable();      // nombor asal platform
            $t->string('source')->default('manual');        // marketplace|pos|site|booking|cod|manual|wholesale
            $t->string('source_platform')->nullable();      // shopee|lazada|tiktok|amazon|store|...
            $t->string('source_id')->nullable();             // ID dalam sistem asal
            $t->string('channel_id')->nullable();           // FK logical ke omnichannel channel
            $t->foreignId('customer_id')->nullable();        // FK ke unified_contacts

            // Status kanonikal (semua platform dipetakan ke set ini)
            $t->enum('status', [
                'draft', 'pending', 'confirmed', 'paid', 'processing',
                'packed', 'shipped', 'out_for_delivery', 'delivered',
                'cancelled', 'refunded', 'partially_refunded', 'on_hold', 'failed',
            ])->default('pending');

            // Status asal platform (untuk pemetaan balikan)
            $t->string('source_status')->nullable();
            $t->string('payment_method')->nullable();        // cod|bank_transfer|ewallet|credit_card|prepaid
            $t->string('payment_status')->default('unpaid'); // unpaid|paid|partial|refunded|failed
            $t->string('fulfillment_method')->nullable();    // shipping|pickup|delivery|digital|service

            // Nilai
            $t->decimal('subtotal', 12, 2)->default(0);
            $t->decimal('discount_amount', 12, 2)->default(0);
            $t->decimal('tax_amount', 12, 2)->default(0);
            $t->decimal('shipping_amount', 12, 2)->default(0);
            $t->decimal('total_amount', 12, 2)->default(0);
            $t->decimal('paid_amount', 12, 2)->default(0);
            $t->decimal('refunded_amount', 12, 2)->default(0);
            $t->decimal('cost_amount', 12, 2)->default(0);    // kos barang (untuk margin)
            $t->string('currency', 3)->default('MYR');

            // Party
            $t->string('customer_name')->nullable();
            $t->string('customer_email')->nullable();
            $t->string('customer_phone')->nullable();
            $t->json('shipping_address')->nullable();
            $t->json('billing_address')->nullable();
            $t->json('meta')->nullable();

            // Rentas masa
            $t->timestamp('placed_at')->nullable();
            $t->timestamp('confirmed_at')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->timestamp('shipped_at')->nullable();
            $t->timestamp('delivered_at')->nullable();
            $t->timestamp('cancelled_at')->nullable();
            $t->timestamp('refunded_at')->nullable();
            $t->timestamp('source_synced_at')->nullable();

            $t->unsignedInteger('line_count')->default(0);
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->index(['tenant_id', 'source', 'source_platform']);
            $t->index(['tenant_id', 'placed_at']);
        });

        // Item pesanan unified
        Schema::create('unified_order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('unified_order_id')->constrained('unified_orders')->cascadeOnDelete();
            $t->foreignId('product_id')->nullable();          // FK ke inventory products (boleh null — item digital/luar)
            $t->string('sku')->nullable();
            $t->string('name');
            $t->text('description')->nullable();
            $t->string('variant')->nullable();                 // saiz / warna
            $t->decimal('quantity', 12, 2)->default(1);
            $t->decimal('unit_price', 12, 2)->default(0);
            $t->decimal('discount_amount', 12, 2)->default(0);
            $t->decimal('tax_amount', 12, 2)->default(0);
            $t->decimal('line_total', 12, 2)->default(0);
            $t->decimal('unit_cost', 12, 2)->default(0);
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'unified_order_id']);
        });

        // Stok rizab — kunci fizikal untuk pesanan belum diproses
        Schema::create('order_stock_reservations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('unified_order_id')->constrained('unified_orders')->cascadeOnDelete();
            $t->foreignId('product_id')->nullable();
            $t->string('sku')->nullable();
            $t->decimal('quantity', 12, 2)->default(0);
            $t->decimal('reserved_quantity', 12, 2)->default(0);
            $t->string('status')->default('reserved');   // reserved|released|committed|cancelled
            $t->timestamp('expires_at')->nullable();
            $t->timestamp('committed_at')->nullable();
            $t->timestamp('released_at')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'unified_order_id']);
            $t->index(['tenant_id', 'status']);
        });

        // Sales channel binding — tetapan mapping platform + stok
        Schema::create('order_channel_bindings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('platform');                       // shopee|lazada|tiktok|amazon|pos|site|booking
            $t->string('display_name')->nullable();
            $t->string('status_map')->nullable();         // JSON: status platform → kanonikal
            $t->json('payment_methods')->nullable();
            $t->boolean('is_active')->default(true);
            $t->boolean('stock_sync_enabled')->default(true);
            $t->boolean('stock_push_enabled')->default(false);  // tolak stok ke platform
            $t->integer('sync_interval_minutes')->default(15);
            $t->timestamp('last_pull_at')->nullable();
            $t->timestamp('last_push_at')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->unique(['tenant_id', 'platform']);
        });

        // Timeline audit
        Schema::create('order_timeline_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('unified_order_id')->constrained('unified_orders')->cascadeOnDelete();
            $t->string('event_type');                     // created|status_changed|payment|sync|note|item_added
            $t->string('from_value')->nullable();
            $t->string('to_value')->nullable();
            $t->text('description')->nullable();
            $t->string('actor_type')->default('system');  // system|user|ai|platform
            $t->foreignId('actor_id')->nullable();
            $t->json('context')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'unified_order_id']);
        });

        // Sync log — jejak setiap sinkronisasi platform
        Schema::create('order_sync_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('unified_order_id')->nullable();
            $t->string('platform');
            $t->string('direction')->default('pull');   // pull|push
            $t->string('status')->default('success');    // success|failed|partial|skipped
            $t->unsignedInteger('records_synced')->default(0);
            $t->unsignedInteger('records_failed')->default(0);
            $t->text('error_message')->nullable();
            $t->json('payload_summary')->nullable();
            $t->unsignedInteger('duration_ms')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_sync_logs');
        Schema::dropIfExists('order_timeline_events');
        Schema::dropIfExists('order_channel_bindings');
        Schema::dropIfExists('order_stock_reservations');
        Schema::dropIfExists('unified_order_items');
        Schema::dropIfExists('unified_orders');
    }
};
