<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OmniOrder — pesanan omnichannel (asal: Luxana).
 *
 * ⚠️ Wang dalam integer SEN.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('omni_couriers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->string('code', 40);
            $t->string('name', 120);
            $t->string('provider', 60)->nullable();
            $t->boolean('supports_cod')->default(false);
            $t->boolean('supports_awb')->default(true);
            $t->string('api_endpoint', 255)->nullable();
            $t->json('config')->nullable();
            $t->boolean('is_active')->default(true);
            $t->integer('sort_order')->default(0);
            $t->timestamps();

            $t->index(['tenant_id', 'is_active']);
            $t->unique(['tenant_id', 'code']);
        });

        Schema::create('omni_channel_connections', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->string('channel', 40);
            $t->string('name', 120)->nullable();
            $t->string('shop_id', 120)->nullable();
            $t->text('credentials')->nullable();
            $t->string('webhook_secret', 255)->nullable();
            $t->string('status', 20)->default('disconnected');
            $t->timestamp('last_synced_at')->nullable();
            $t->text('last_error')->nullable();
            $t->boolean('auto_approve')->default(false);
            $t->unsignedSmallInteger('sync_interval_minutes')->default(15);
            $t->json('settings')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'channel']);
            $t->unique(['tenant_id', 'channel', 'shop_id']);
        });

        Schema::create('omni_orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->string('order_no', 40);
            $t->string('external_order_id', 120)->nullable();
            $t->string('channel', 40)->default('manual');
            $t->string('status', 20)->default('new');
            $t->string('customer_name', 150)->nullable();
            $t->string('customer_phone', 40)->nullable();
            $t->text('customer_address')->nullable();
            $t->string('customer_city', 100)->nullable();
            $t->string('customer_state', 100)->nullable();
            $t->string('customer_postcode', 10)->nullable();
            $t->unsignedBigInteger('total_sen')->default(0);
            $t->string('payment_method', 20)->default('prepaid');
            $t->string('payment_status', 30)->default('unpaid');
            $t->string('courier', 40)->nullable();
            $t->string('tracking_no', 120)->nullable();
            $t->string('awb_url', 500)->nullable();
            $t->json('items')->nullable();
            $t->boolean('is_duplicate')->default(false);
            $t->unsignedBigInteger('duplicate_of_id')->nullable();
            $t->boolean('is_high_risk')->default(false);
            $t->unsignedTinyInteger('risk_score')->default(0);
            $t->string('risk_reason', 500)->nullable();
            $t->boolean('is_high_value')->default(false);
            $t->text('delivery_failure_reason')->nullable();
            $t->string('cod_payout_status', 20)->nullable();
            $t->timestamp('cod_payout_at')->nullable();
            $t->timestamp('approved_at')->nullable();
            $t->timestamp('packed_at')->nullable();
            $t->timestamp('shipped_at')->nullable();
            $t->timestamp('delivered_at')->nullable();
            $t->json('payload')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->index(['tenant_id', 'channel']);
            $t->index(['tenant_id', 'customer_phone']);
            $t->index(['tenant_id', 'is_duplicate']);
            $t->unique(['tenant_id', 'order_no']);
        });

        Schema::create('omni_cod_payouts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->foreignId('omni_order_id')->constrained('omni_orders')->cascadeOnDelete();
            $t->unsignedBigInteger('omni_courier_id')->nullable();
            $t->unsignedBigInteger('expected_sen')->default(0);
            $t->unsignedBigInteger('received_sen')->default(0);
            $t->string('status', 20)->default('pending');
            $t->string('payout_reference', 120)->nullable();
            $t->timestamp('expected_at')->nullable();
            $t->timestamp('received_at')->nullable();
            $t->text('dispute_reason')->nullable();
            $t->timestamp('resolved_at')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->index(['tenant_id', 'expected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('omni_cod_payouts');
        Schema::dropIfExists('omni_orders');
        Schema::dropIfExists('omni_channel_connections');
        Schema::dropIfExists('omni_couriers');
    }
};
