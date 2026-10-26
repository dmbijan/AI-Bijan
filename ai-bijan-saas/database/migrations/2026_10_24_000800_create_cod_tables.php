<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * COD Engine (BAB 84 / Idea #6) — Cash on Delivery untuk PKS Malaysia.
 *
 * ✅ Keburukan discovered daripada Shopify "EasySell COD Form" (4.9, 989 review):
 * COD ialah kaedah pembayaran dominan di Malaysia dan TIDAK ada pesaing MY
 * yang menyenangkannya.
 *
 * Ciri:
 * - COD settings per tenant (caj tambahan, had minimum, whitelist kurier)
 * - COD order + status tracking (pending → dispatched → delivered → collected)
 * - Semak alamat + liputan pengemparan
 * - Rekod kutipan & reconciliation (tunaicollected vs_order)
 * - COD failure analytics (batal selepas hantar — punca utama kehilangan)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Tetapan COD per tenant
        Schema::create('cod_settings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->boolean('enabled')->default(false);
            // Caj tambahan COD (RM). 0 = tiada caj.
            $t->decimal('surcharge', 10, 2)->default(0);
            // Had minimum pesanan COD (RM)
            $t->decimal('min_order_amount', 12, 2)->default(0);
            // Had maksimum nilai COD (RM) — atas nilai ini, guna prepaid.
            $t->decimal('max_order_amount', 12, 2)->default(3000);
            // Kurier yang menyokong COD (kod dipisah|koma)
            $t->json('allowed_couriers')->nullable();
            // Senarai wilayah yang tidak disokong (kode pos / negeri)
            $t->json('excluded_regions')->nullable();
            // Peratus COD yang perlu pengesahan WhatsApp sebelum hantar
            $t->boolean('require_confirmation')->default(true);
            // Peratus COD yang perlu pengesahan  sebelum hantar
            $t->boolean('require_call_confirmation')->default(false);
            $t->timestamps();

            $t->unique('tenant_id');
        });

        // Pesanan COD
        Schema::create('cod_orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            // Pautan ke marketplace_orders (boleh null — COD bukan marketplace)
            $t->foreignId('marketplace_order_id')->nullable();
            $t->string('cod_no', 40);
            $t->string('reference_no', 60)->nullable();

            // Pelanggan
            $t->string('customer_name', 180);
            $t->string('customer_phone', 30);
            $t->text('delivery_address');
            $t->string('postcode', 12)->nullable();
            $t->string('state', 60)->nullable();
            $t->string('city', 100)->nullable();
            $t->string('country', 2)->default('MY');

            // Nilai
            $t->decimal('order_amount', 12, 2);
            $t->decimal('shipping_amount', 12, 2)->default(0);
            $t->decimal('surcharge', 10, 2)->default(0);
            $t->decimal('total_amount', 12, 2);
            $t->decimal('amount_to_collect', 12, 2);
            $t->decimal('amount_collected', 12, 2)->default(0);
            $t->decimal('amount_refunded', 12, 2)->default(0);
            $t->string('currency', 3)->default('MYR');

            // Status: pending|confirmed|dispatched|delivered|collected|failed|cancelled|refunded
            $t->enum('status', [
                'pending', 'confirmed', 'dispatched', 'delivered',
                'collected', 'failed', 'cancelled', 'refunded',
            ])->default('pending');

            // Kurier
            $t->string('courier', 80)->nullable();
            $t->string('tracking_number', 80)->nullable();
            $t->timestamp('dispatched_at')->nullable();
            $t->timestamp('delivered_at')->nullable();
            $t->timestamp('collected_at')->nullable();
            $t->timestamp('failed_at')->nullable();
            $t->string('failure_reason', 250)->nullable();

            // Sahkan alamat
            $t->boolean('address_verified')->default(false);
            $t->string('address_issue', 250)->nullable();
            $t->boolean('serviceable')->default(true);

            // Audit
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('collected_by')->nullable()->constrained('users')->nullOnDelete();
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->index(['tenant_id', 'created_at']);
            $t->index(['tenant_id', 'tracking_number']);
            $t->unique(['tenant_id', 'cod_no']);
        });

        // Sejarah status COD (jejak audit)
        Schema::create('cod_status_histories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('cod_order_id')->constrained('cod_orders')->cascadeOnDelete();
            $t->string('from_status', 30)->nullable();
            $t->string('to_status', 30);
            $t->text('note')->nullable();
            $t->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('changed_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'cod_order_id']);
        });

        // Kutipan wang COD (reconciliation harian)
        Schema::create('cod_collections', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->date('collection_date');
            $t->string('courier', 80)->nullable();
            $t->decimal('total_collected', 14, 2)->default(0);
            $t->decimal('total_expected', 14, 2)->default(0);
            $t->integer('orders_collected')->default(0);
            $t->integer('orders_failed')->default(0);
            $t->decimal('total_refunded', 14, 2)->default(0);
            $t->text('note')->nullable();
            $t->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['tenant_id', 'collection_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cod_collections');
        Schema::dropIfExists('cod_status_histories');
        Schema::dropIfExists('cod_orders');
        Schema::dropIfExists('cod_settings');
    }
};
