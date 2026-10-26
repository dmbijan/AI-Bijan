<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B-Entitlement — Lengkapkan jadual `plans` & `tenant_modules` sedia ada (BAB 90).
 *
 * ⚠️ PENEMUAN AUDIT:
 *  - `plans` wujud dalam platform core dengan lajur: code, name, max_users,
 *    max_locations, storage_mb, ai_credits_monthly, modules, price_monthly.
 *  - TETAPI jadual itu TIDAK PERNAH digunakan — tiada service/middleware
 *    membacanya, jadi gate akses modul tidak pernah wujud.
 *
 * Migration ini menambah lajur yang diperlukan untuk Subscription Engine:
 *  - `plans.name_bm`, `description`, `tier`, `price_yearly`, `currency`,
 *    `trial_days`, `is_public`, `features`, `sort_order`
 *  - `tenant_modules.source`, `enabled_at`, `disabled_at`, `expires_at`
 *
 * Semua tambahan selamat untuk jadual sedia ada (nullable/default).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('plans')) {
            Schema::table('plans', function (Blueprint $table) {
                if (! Schema::hasColumn('plans', 'name_bm')) {
                    $table->string('name_bm', 120)->nullable();
                }

                if (! Schema::hasColumn('plans', 'description')) {
                    $table->text('description')->nullable();
                }

                if (! Schema::hasColumn('plans', 'tier')) {
                    $table->string('tier', 30)->default('standard');
                }

                if (! Schema::hasColumn('plans', 'price_yearly')) {
                    $table->decimal('price_yearly', 12, 2)->default(0);
                }

                if (! Schema::hasColumn('plans', 'currency')) {
                    $table->string('currency', 3)->default('MYR');
                }

                if (! Schema::hasColumn('plans', 'trial_days')) {
                    $table->unsignedInteger('trial_days')->default(14);
                }

                if (! Schema::hasColumn('plans', 'is_public')) {
                    $table->boolean('is_public')->default(true);
                }

                if (! Schema::hasColumn('plans', 'features')) {
                    $table->json('features')->nullable();
                }

                if (! Schema::hasColumn('plans', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->default(0);
                }
            });
        }

        if (Schema::hasTable('tenant_modules')) {
            Schema::table('tenant_modules', function (Blueprint $table) {
                if (! Schema::hasColumn('tenant_modules', 'source')) {
                    $table->string('source', 30)->default('plan');
                }

                if (! Schema::hasColumn('tenant_modules', 'enabled_at')) {
                    $table->timestamp('enabled_at')->nullable();
                }

                if (! Schema::hasColumn('tenant_modules', 'disabled_at')) {
                    $table->timestamp('disabled_at')->nullable();
                }

                if (! Schema::hasColumn('tenant_modules', 'expires_at')) {
                    $table->timestamp('expires_at')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        $kOLUM = [
            'plans' => ['name_bm', 'description', 'tier', 'price_yearly', 'currency', 'trial_days', 'is_public', 'features', 'sort_order'],
            'tenant_modules' => ['source', 'enabled_at', 'disabled_at', 'expires_at'],
        ];

        foreach ($kOLUM as $jadual => $kolum) {
            if (! Schema::hasTable($jadual)) {
                continue;
            }

            Schema::table($jadual, function (Blueprint $table) use ($kolum) {
                foreach ($kolum as $k) {
                    if (Schema::hasColumn($jadual, $k)) {
                        $table->dropColumn($k);
                    }
                }
            });
        }
    }
};
