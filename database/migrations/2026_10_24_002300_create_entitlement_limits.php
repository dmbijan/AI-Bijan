<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B-Entitlement — Pemasa Had Penggunaan & Modul (BAB 90).
 *
 * ⚠️ PENEMUAN AUDIT: jadual asas `plans`, `subscriptions`, `tenant_modules`
 * SUDAH ADA dalam `2026_10_18_000001_create_platform_core.php`. Namun
 * TIDAK ADA service/middleware yang membacanya — gate akses modul tidak
 * pernah digunakan.
 *
 * Migration ini hanya menambah yang PERLU:
 *   1. `plan_limits`        — had penggunaan per plan
 *   2. `usage_counters`     — kaunter penggunaan terkira
 *   3. `entitlement_grants` — grant/revoke modul manual + feature flags
 *
 * Tiada `plans`/`subscriptions`/`tenant_modules` dicipta semula — itu
 * akan menyebabkan pertindihan.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plan_limits')) {
            Schema::create('plan_limits', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('plan_id')->index();
                $table->string('limit_key', 80);
                $table->string('name', 150);
                $table->unsignedBigInteger('limit_value')->nullable();
                $table->string('unit', 30)->nullable();
                $table->string('period', 30)->default('monthly');
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['plan_id', 'limit_key'], 'plan_limits_unique');
            });
        }

        if (! Schema::hasTable('usage_counters')) {
            Schema::create('usage_counters', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->index();
                $table->string('limit_key', 80)->index();
                $table->string('period_key', 30);
                $table->unsignedBigInteger('used_value')->default(0);
                $table->unsignedBigInteger('reserved_value')->default(0);
                $table->timestamp('period_start')->nullable();
                $table->timestamp('period_end')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'limit_key', 'period_key'], 'usage_counters_unique');
                $table->index(['tenant_id', 'period_key']);
            });
        }

        if (! Schema::hasTable('entitlement_grants')) {
            Schema::create('entitlement_grants', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->index();
                $table->string('module', 40)->index();
                $table->string('source', 30)->default('addon');
                $table->boolean('enabled')->default(true);
                $table->timestamp('enabled_at')->nullable();
                $table->timestamp('disabled_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->json('feature_flags')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'module'], 'entitlement_grants_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('entitlement_grants');
        Schema::dropIfExists('usage_counters');
        Schema::dropIfExists('plan_limits');
    }
};
