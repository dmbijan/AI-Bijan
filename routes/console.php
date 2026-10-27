<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Modules\Helpdesk\HelpdeskService;
use App\Services\TenantProvisioner;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

/**
 * Selepas kebenaran baru ditambah dalam config/aibijan.php: tambah ke peranan SISTEM setiap tenant.
 * Hanya menambah (tidak membuang kebenaran yang tenant ubah sendiri); peranan tersuai tenant tidak disentuh.
 */
Artisan::command('aibijan:sync-peranan', function () {
    TenantProvisioner::syncPermissions();
    $ids = Permission::pluck('id', 'key');
    $ctx = app(TenantContext::class);
    $n = 0;
    foreach (Tenant::cursor() as $t) {
        $ctx->set($t);
        foreach (config('aibijan.roles') as $key => $def) {
            $role = Role::where('key', $key)->where('is_system', true)->first();
            if ($role) {
                $mahu = $def['permissions'] === ['*'] ? $ids->values() : $ids->only($def['permissions'])->values();
                $n += count($role->permissions()->syncWithoutDetaching($mahu)['attached']);
            }
        }
        $ctx->clear();
    }
    $this->info("Kebenaran ditambah: $n");
})->purpose('Selaraskan kebenaran baru ke peranan sistem semua tenant');

// Penjadual (cron: php artisan schedule:run setiap minit). Setiap tenant diproses dalam konteksnya sendiri.
Schedule::call(function () {
    $ctx = app(TenantContext::class);
    foreach (Tenant::whereIn('status', ['trial', 'active', 'past_due'])->cursor() as $t) {
        $ctx->set($t);
        app(HelpdeskService::class)->checkSla();
        $ctx->clear();
    }
})->everyFiveMinutes()->name('helpdesk-sla')->withoutOverlapping();

// B-Flow Automation — proses run yang due untuk retry (BAB 28)
Schedule::call(function () {
    $ctx = app(TenantContext::class);
    $results = [];
    foreach (Tenant::whereIn('status', ['trial', 'active', 'past_due'])->cursor() as $t) {
        $ctx->set($t);
        try {
            $results = array_merge(
                $results,
                app(\App\Modules\FlowAutomation\Services\FlowEngine::class)->processRetries()
            );
        } catch (\Throwable $e) {
            Log::error('Flow retry gagal', ['tenant' => $t->id, 'error' => $e->getMessage()]);
        }
        $ctx->clear();
    }
    Log::info('Flow retries diproses', ['count' => count($results)]);
})->everyMinute()->name('flow-retries')->withoutOverlapping();

// B-AI Gateway — reset kuota kredit bulanan (BAB 22.3)
Schedule::call(function () {
    $ctx = app(TenantContext::class);
    $count = 0;

    foreach (Tenant::whereIn('status', ['trial', 'active', 'past_due'])->cursor() as $t) {
        $ctx->set($t);

        $wallet = \App\Modules\AIGateway\Models\AiCreditWallet::forTenant($t->id);

        // Kekalkan kuota sama, reset penggunaan
        if ((float) $wallet->period_quota > 0) {
            $wallet->resetPeriod((float) $wallet->period_quota);
            $count++;
        }

        $ctx->clear();
    }

    Log::info('Kuota wallet AI di-reset', ['count' => $count]);
})->monthlyOn(1, '00:05')->name('ai-wallet-reset')->withoutOverlapping();

// B-AI Gateway — alert baki rendah (BAB 22.3)
Schedule::call(function () {
    $ctx = app(TenantContext::class);

    foreach (Tenant::whereIn('status', ['trial', 'active', 'past_due'])->cursor() as $t) {
        $ctx->set($t);

        $wallet = \App\Modules\AIGateway\Models\AiCreditWallet::forTenant($t->id);
        $alerts = $wallet->applicableAlerts();

        if (!empty($alerts)) {
            Log::warning('Baki kredit AI rendah', [
                'tenant' => $t->id,
                'percent' => $wallet->usagePercent(),
                'alerts' => array_column($alerts, 'message'),
            ]);
        }

        $ctx->clear();
    }
})->dailyAt('08:00')->name('ai-wallet-alerts')->withoutOverlapping();

// ============================================================================
// PART XVIII — BAB 82 (Prospecting, Blast, Media Studio, B-Executive AI)
// ============================================================================

// Blast Engine — hantar kempen yang disahkan & dijadualkan (Fasa 2)
Schedule::call(function () {
    $ctx = app(TenantContext::class);

    foreach (Tenant::whereIn('status', ['trial', 'active', 'past_due'])->cursor() as $t) {
        $ctx->set($t);

        try {
            // Kempen yang dijadualkan dan sudahDue
            $campaigns = \App\Modules\Blast\Models\BlastCampaign::query()
                ->where('status', 'running')
                ->orWhere(function ($q) {
                    $q->where('status', 'scheduled')
                        ->where('scheduled_at', '<=', now());
                })
                ->get();

            foreach ($campaigns as $campaign) {
                \App\Jobs\Blast\DispatchBlastJob::dispatch($campaign->id);
            }

            // Bersihkan suppression yang luput
            app(\App\Modules\Blast\Services\OptOutService::class)->bersihkanLuput($t->id);
        } catch (\Throwable $e) {
            Log::error('Blast dispatch gagal', ['tenant' => $t->id, 'error' => $e->getMessage()]);
        }

        $ctx->clear();
    }
})->everyMinute()->name('blast-dispatch')->withoutOverlapping();

// Prospecting — skor semula prospect (Fasa 1)
Schedule::call(function () {
    $ctx = app(TenantContext::class);

    foreach (Tenant::whereIn('status', ['trial', 'active', 'past_due'])->cursor() as $t) {
        $ctx->set($t);

        try {
            app(\App\Modules\Prospecting\Services\ProspectScoringService::class)->skorSemula($t->id);
        } catch (\Throwable $e) {
            Log::error('Prospect rescore gagal', ['tenant' => $t->id, 'error' => $e->getMessage()]);
        }

        $ctx->clear();
    }
})->everyTenMinutes()->name('prospecting-rescore')->withoutOverlapping();

// Media Studio — purge aset yang tamat tempoh retensi (Fasa 4)
Schedule::call(function () {
    try {
        $hasil = app(\App\Modules\MediaStudio\Services\MediaRetentionService::class)->purgeExpired();

        if ($hasil['purged'] > 0) {
            Log::info('Media purge selesai', $hasil);
        }
    } catch (\Throwable $e) {
        Log::error('Media purge gagal', ['error' => $e->getMessage()]);
    }
})->dailyAt('03:30')->name('media-retention-purge')->withoutOverlapping();

// B-Executive AI — briefing harian eksekutif (Fasa 5)
Schedule::call(function () {
    $ctx = app(TenantContext::class);

    foreach (Tenant::whereIn('status', ['trial', 'active', 'past_due'])->cursor() as $t) {
        $ctx->set($t);

        try {
            $pengguna = \App\Models\User::query()
                ->whereHas('memberships', function ($q) use ($t) {
                    $q->where('tenant_id', $t->id);
                })
                ->limit(10)
                ->get();

            foreach ($pengguna as $u) {
                app(\App\Modules\ExecutiveAI\Services\BriefingService::class)->jana('daily', $u->id);
            }
        } catch (\Throwable $e) {
            Log::error('Briefing eksekutif gagal', ['tenant' => $t->id, 'error' => $e->getMessage()]);
        }

        $ctx->clear();
    }
})->dailyAt('07:30')->name('executive-briefing-daily')->withoutOverlapping();

// B-Reminder — hantar peringatan yang jatuh tempo (BAB 86 Idea #22)
Schedule::call(function () {
    $ctx = app(TenantContext::class);
    $count = 0;

    foreach (Tenant::whereIn('status', ['trial', 'active', 'past_due'])->cursor() as $t) {
        $ctx->set($t);
        try {
            $service = app(\App\Modules\Reminder\Services\ReminderService::class);

            // 1. Eskalasi yang lewat
            $count += $service->eskalasiLewat($t->id)['dileskalakan'];

            // 2. Jalankan jadual berulang yang due
            $count += $service->jalankanJadual($t->id)['dijana'];
        } catch (\Throwable $e) {
            Log::error('Reminder dispatch gagal', ['tenant' => $t->id, 'error' => $e->getMessage()]);
        }
        $ctx->clear();
    }

    Log::info('Reminder diproses', ['count' => $count]);
})->everyTenMinutes()->name('reminder-dispatch')->withoutOverlapping();

// B-Content Studio — terbitkan kandungan yang dijadualkan (BAB 86 Idea #21)
Schedule::call(function () {
    $ctx = app(TenantContext::class);
    $total = 0;

    foreach (Tenant::whereIn('status', ['trial', 'active', 'past_due'])->cursor() as $t) {
        $ctx->set($t);
        try {
            $hasil = app(\App\Modules\ContentStudio\Services\ContentStudioService::class)
                ->terbitQueued($t->id, 20);
            $total += $hasil['berjaya'];
        } catch (\Throwable $e) {
            Log::error('Content publish gagal', ['tenant' => $t->id, 'error' => $e->getMessage()]);
        }
        $ctx->clear();
    }

    Log::info('Kandungan diterbitkan', ['count' => $total]);
})->everyFiveMinutes()->name('content-publish')->withoutOverlapping();

// B-Orders — bersihkan rizab stok yang luput (BAB 86 Idea #19)
Schedule::call(function () {
    $ctx = app(TenantContext::class);
    $count = 0;

    foreach (Tenant::whereIn('status', ['trial', 'active', 'past_due'])->cursor() as $t) {
        $ctx->set($t);
        try {
            $count += app(\App\Modules\Orders\Services\UnifiedOrderService::class)
                ->bersihkanRizabLuput($t->id);
        } catch (\Throwable $e) {
            Log::error('Orders cleanup gagal', ['tenant' => $t->id, 'error' => $e->getMessage()]);
        }
        $ctx->clear();
    }

    Log::info('Rizab stok luput dibersihkan', ['count' => $count]);
})->hourly()->name('orders-reservation-cleanup')->withoutOverlapping();

// B-SEO — audit SEO automatik + sitemap (BAB 86 Idea #20)
Schedule::call(function () {
    $ctx = app(TenantContext::class);
    $count = 0;

    foreach (Tenant::whereIn('status', ['trial', 'active', 'past_due'])->cursor() as $t) {
        $ctx->set($t);
        try {
            $hasil = app(\App\Modules\Seo\Services\SeoService::class)->auditTenant($t->id);
            $count += $hasil['jumlah_metadata'];
        } catch (\Throwable $e) {
            Log::error('SEO audit gagal', ['tenant' => $t->id, 'error' => $e->getMessage()]);
        }
        $ctx->clear();
    }

    Log::info('SEO audit automatik', ['objects' => $count]);
})->dailyAt('03:15')->name('seo-audit-daily')->withoutOverlapping();

// B-BusinessIntel — snapshot kesihatan + sinyal pasaran harian (BAB 86 Idea #23-25)
Schedule::call(function () {
    $ctx = app(TenantContext::class);
    $count = 0;

    foreach (Tenant::whereIn('status', ['trial', 'active', 'past_due'])->cursor() as $t) {
        $ctx->set($t);
        try {
            $service = app(\App\Modules\BusinessIntel\Services\BusinessIntelService::class);

            if (config('business_intel.scheduler.daily_health_snapshot', true)) {
                $service->simpanKesihatan($t->id);
                $count++;
            }

            if (config('business_intel.scheduler.daily_signal', true)) {
                $service->janaSinyalPasaran($t->id);
            }
        } catch (\Throwable $e) {
            Log::error('BusinessIntel harian gagal', ['tenant' => $t->id, 'error' => $e->getMessage()]);
        }
        $ctx->clear();
    }

    Log::info('BusinessIntel snapshot harian', ['count' => $count]);
})->dailyAt('04:00')->name('business-intel-daily')->withoutOverlapping();

// ---------------------------------------------------------------
// B-Billing — komisen reseller berulang (27 Okt 2026)
// ---------------------------------------------------------------
// ⚠️ Pekerjaan ini berjalan PLATFORM-WIDE (bukan per tenant) — jangan
// gunakan TenantContext. Semua query dalam ResellerCommissionService
// menggunakan withoutGlobalScope('tenant') secara eksplisit.

// 02:00 — sahkan komisen yang sudah matang (earned_at + 30 hari)
Schedule::call(function () {
    $n = app(\App\Modules\Billing\Services\ResellerCommissionService::class)
        ->maturekanKomisen();

    Log::info('Komisen reseller dimatangkan', ['bilangan' => $n]);
})->dailyAt('02:00')->name('reseller-mature')->withoutOverlapping();

// 03:00 — kesan churn (langganan tamat/batal → komisen pending dibalikkan)
Schedule::call(function () {
    $n = app(\App\Modules\Billing\Services\ResellerCommissionService::class)
        ->kesanChurn();

    Log::info('Churn reseller dikesan', ['komisen_dibalikkan' => $n]);
})->dailyAt('03:00')->name('reseller-churn')->withoutOverlapping();

// 1hb 04:00 — bina payout automatik jika baki >= RM100
Schedule::call(function () {
    $payout = app(\App\Modules\Billing\Services\ResellerCommissionService::class)
        ->binaPayout();

    Log::info('Payout reseller dibina', ['bilangan' => count($payout)]);
})->monthlyOn(1, '04:00')->name('reseller-payout')->withoutOverlapping();
