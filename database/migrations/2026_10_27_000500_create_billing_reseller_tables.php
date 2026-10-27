<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B-Billing — Langganan berbayar melalui CHIP + komisen reseller berulang.
 *
 * Model perniagaan (disahkan pengguna 27 Okt 2026):
 *   Pelanggan bayar RM388/bulan → platform simpan RM288, reseller RM100/bulan
 *   selagi pelanggan langgan. Komisen jadi hak selepas 30 hari (lindungi refund).
 *
 * JADUAL
 * ──────
 * 1. `billing_customers`      — pemetaan tenant → pelanggan CHIP
 * 2. `billing_subscriptions`  — langganan aktif (CHIP recurring)
 * 3. `billing_charges`        — satu rekod per cubaan bayaran/invois CHIP
 * 4. `billing_webhook_events` — jejak webhook (idempoten, replay-safe)
 * 5. `resellers`              — orang yang jual SaaS (platform-level)
 * 6. `reseller_referrals`     — satu tenant ↔ satu reseller (UNIQUE)
 * 7. `reseller_commissions`   — komisen bulanan (UNIQUE per period)
 * 8. `reseller_payouts`       — bayaran kepada reseller
 * 9. `reseller_clicks`        — klik pautan rujukan (IP di-hash, PDPA)
 *
 * ⚠️ PERATURAN KESELAMATAN
 * - `billing_webhook_events` mesti UNIQUE(purchase_id, event_type) → idempoten.
 *   Nota: `UNIQUE` dengan kolum NULL gagal di SQLite/MySQL, jadi semak di PHP
 *   juga (rujuk `ChipWebhookService::sudahDiproses()`).
 * - `reseller_referrals` UNIQUE(tenant_id) → satu tenant hanya satu reseller.
 * - `reseller_commissions` UNIQUE(reseller_referral_id, period) → satu/bulan.
 * - `reseller_clicks.ip_hash` — JANGAN simpan IP sebenar (PDPA).
 * - Semua wang `decimal(15,2)`. Tidak pernah float.
 * - `resellers` ialah jadual PLATFORM (bukan tenant) — tiada `tenant_id`,
 *   ada `user_id`. JANGAN gabung dengan modul `Affiliate` (yang tenant-scoped).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---------------------------------------------------------------
        // 1. Billing customers (tenant ↔ CHIP)
        // ---------------------------------------------------------------
        if (! Schema::hasTable('billing_customers')) {
            Schema::create('billing_customers', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('tenant_id')->index();
                $t->string('chip_customer_id')->nullable()->index();
                $t->string('email')->nullable();
                $t->string('full_name')->nullable();
                $t->string('phone')->nullable();
                $t->json('metadata')->nullable();
                $t->timestamps();

                $t->unique('tenant_id');
                $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        // ---------------------------------------------------------------
        // 2. Subscriptions
        // ---------------------------------------------------------------
        if (! Schema::hasTable('billing_subscriptions')) {
            Schema::create('billing_subscriptions', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('tenant_id')->index();
                $t->string('plan_code', 40)->index();          // starter|growth|enterprise
                $t->string('chip_subscription_id')->nullable()->index();
                $t->string('status', 30)->default('pending')->index();
                // pending|active|past_due|suspended|cancelled|expired
                $t->decimal('amount', 15, 2)->default(0);
                $t->char('currency', 3)->default('MYR');
                $t->string('billing_cycle', 20)->default('monthly');

                $t->dateTime('trial_ends_at')->nullable();
                $t->dateTime('current_period_start')->nullable();
                $t->dateTime('current_period_end')->nullable();
                $t->dateTime('cancel_at')->nullable();
                $t->dateTime('cancelled_at')->nullable();

                // Reseller yang memperkenalkan tenant ini (komisen berulang)
                $t->unsignedBigInteger('reseller_id')->nullable()->index();

                $t->json('metadata')->nullable();
                $t->timestamps();

                $t->index(['tenant_id', 'status']);
            });
        }

        // ---------------------------------------------------------------
        // 3. Charges (setiap invois CHIP)
        // ---------------------------------------------------------------
        if (! Schema::hasTable('billing_charges')) {
            Schema::create('billing_charges', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('tenant_id')->index();
                $t->unsignedBigInteger('billing_subscription_id')->nullable()->index();

                $t->string('chip_purchase_id')->nullable()->unique();
                $t->string('chip_checkout_url', 1000)->nullable();
                $t->string('status', 30)->default('created')->index();
                // created|pending|paid|failed|refunded|expired

                $t->string('kind', 30)->default('subscription');  // subscription|topup
                $t->string('period_key', 20)->index();            // 2026-11

                $t->decimal('amount', 15, 2)->default(0);
                $t->char('currency', 3)->default('MYR');

                $t->dateTime('paid_at')->nullable();
                $t->dateTime('failed_at')->nullable();
                $t->string('failure_reason', 500)->nullable();

                $t->json('chip_payload')->nullable();   // disunting — tiada data sensitif
                $t->timestamps();

                $t->unique(['tenant_id', 'kind', 'period_key']);
            });
        }

        // ---------------------------------------------------------------
        // 4. Webhook events (jejak & idempotency)
        // ---------------------------------------------------------------
        if (! Schema::hasTable('billing_webhook_events')) {
            Schema::create('billing_webhook_events', function (Blueprint $t) {
                $t->id();
                $t->string('provider', 30)->default('chip')->index();
                $t->string('event_type', 60)->index();
                $t->string('chip_purchase_id')->nullable()->index();
                $t->string('chip_subscription_id')->nullable()->index();

                $t->string('status', 20)->default('received')->index();
                // received|processed|ignored|failed

                $t->string('signature', 255)->nullable();
                $t->json('payload')->nullable();
                $t->string('processing_error', 500)->nullable();
                $t->dateTime('processed_at')->nullable();
                $t->timestamps();

                // ⚠️ UNIQUE komposit — NULL dianggap berbeza, jadi semak PHP juga
                $t->unique(['chip_purchase_id', 'event_type'], 'billing_webhook_unik');
            });
        }

        // ---------------------------------------------------------------
        // 5. Resellers (PLATFORM-level, bukan tenant)
        // ---------------------------------------------------------------
        if (! Schema::hasTable('resellers')) {
            Schema::create('resellers', function (Blueprint $t) {
                $t->id();
                // ⚠️ TIADA tenant_id — reseller jual SAAS, bukan produk tenant
                $t->unsignedBigInteger('user_id')->index();
                $t->string('code', 40)->unique();       // pautan /r/{code}
                $t->string('name');
                $t->string('email')->nullable();
                $t->string('phone')->nullable();

                $t->string('status', 20)->default('pending')->index();
                // pending|active|suspended|terminated

                $t->decimal('commission_value', 15, 2)->default(100);
                // NULL = selagi pelanggan langgan (keputusan pengguna)
                $t->unsignedSmallInteger('commission_months')->nullable();
                $t->decimal('commission_cap_pct', 5, 2)->default(50);

                $t->json('bank_details')->nullable();
                $t->dateTime('approved_at')->nullable();
                $t->unsignedBigInteger('approved_by')->nullable();

                $t->timestamps();

                $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        // ---------------------------------------------------------------
        // 6. Reseller referrals (satu tenant = satu reseller)
        // ---------------------------------------------------------------
        if (! Schema::hasTable('reseller_referrals')) {
            Schema::create('reseller_referrals', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('reseller_id')->index();
                $t->unsignedBigInteger('tenant_id')->unique();   // ⚠️ UNIK
                $t->string('source', 40)->nullable();           // link|coupon|manual
                $t->unsignedBigInteger('reseller_click_id')->nullable();
                $t->dateTime('attributed_at')->nullable();
                $t->timestamps();

                $t->foreign('reseller_id')->references('id')->on('resellers')->cascadeOnDelete();
                $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        // ---------------------------------------------------------------
        // 7. Reseller commissions
        // ---------------------------------------------------------------
        if (! Schema::hasTable('reseller_commissions')) {
            Schema::create('reseller_commissions', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('reseller_id')->index();
                $t->unsignedBigInteger('reseller_referral_id')->index();
                $t->unsignedBigInteger('tenant_id')->index();
                $t->unsignedBigInteger('billing_charge_id')->nullable()->index();

                $t->string('period', 20);                   // 2026-11
                $t->decimal('base_amount', 15, 2)->default(0);   // jumlah SEBENAR dibayar
                $t->decimal('commission_amount', 15, 2)->default(0);
                $t->char('currency', 3)->default('MYR');

                $t->string('status', 20)->default('pending')->index();
                // pending|approved|paid|reversed

                $t->dateTime('earned_at')->nullable();
                $t->dateTime('mature_at')->nullable();     // earned_at + 30 hari
                $t->dateTime('approved_at')->nullable();
                $t->dateTime('paid_at')->nullable();
                $t->dateTime('reversed_at')->nullable();
                $t->string('reversal_reason', 255)->nullable();

                $t->timestamps();

                // ⚠️ Satu komisen per bulan per rujukan
                $t->unique(['reseller_referral_id', 'period'], 'reseller_komisen_unik');
            });
        }

        // ---------------------------------------------------------------
        // 8. Reseller payouts
        // ---------------------------------------------------------------
        if (! Schema::hasTable('reseller_payouts')) {
            Schema::create('reseller_payouts', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('reseller_id')->index();
                $t->string('payout_reference', 60)->unique();
                $t->decimal('amount', 15, 2)->default(0);
                $t->char('currency', 3)->default('MYR');
                $t->string('status', 20)->default('pending')->index();
                // pending|processing|paid|failed

                // Snapshot bank semasa bayar — rekod tidak berubah
                $t->json('bank_snapshot')->nullable();

                $t->unsignedInteger('commission_count')->default(0);
                $t->string('period_from', 20)->nullable();
                $t->string('period_to', 20)->nullable();

                $t->dateTime('paid_at')->nullable();
                $t->string('proof_path')->nullable();
                $t->string('notes', 500)->nullable();
                $t->timestamps();
            });
        }

        // ---------------------------------------------------------------
        // 9. Reseller clicks (atribusi)
        // ---------------------------------------------------------------
        if (! Schema::hasTable('reseller_clicks')) {
            Schema::create('reseller_clicks', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('reseller_id')->index();
                $t->string('code', 40)->index();
                $t->string('ip_hash', 64)->nullable();     // ⚠️ PDPA — hash sahaja
                $t->string('user_agent_hash', 64)->nullable();
                $t->string('referrer', 500)->nullable();
                $t->string('landing_url', 500)->nullable();
                $t->string('utm_source', 100)->nullable();
                $t->string('utm_campaign', 100)->nullable();
                $t->dateTime('clicked_at')->nullable()->index();
                $t->timestamps();
            });
        }

        // ---------------------------------------------------------------
        // 10. Kolum tambahan pada tenants
        // ---------------------------------------------------------------
        foreach (['reseller_id', 'reseller_code'] as $kolum) {
            if (! Schema::hasColumn('tenants', $kolum)) {
                Schema::table('tenants', function (Blueprint $t) use ($kolum) {
                    $kolum === 'reseller_id'
                        ? $t->unsignedBigInteger($kolum)->nullable()->index()
                        : $t->string($kolum, 40)->nullable()->index();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ([
            'reseller_clicks', 'reseller_payouts', 'reseller_commissions',
            'reseller_referrals', 'resellers',
            'billing_webhook_events', 'billing_charges',
            'billing_subscriptions', 'billing_customers',
        ] as $jadual) {
            Schema::dropIfExists($jadual);
        }
    }
};
