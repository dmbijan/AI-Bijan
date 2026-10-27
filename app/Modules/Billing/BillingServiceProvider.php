<?php

namespace App\Modules\Billing;

use App\Modules\Billing\Services\BillingService;
use App\Modules\Billing\Services\ChipService;
use App\Modules\Billing\Services\ChipWebhookService;
use App\Modules\Billing\Services\ResellerCommissionService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * B-Billing — Langganan CHIP + komisen reseller berulang.
 */
class BillingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/billing.php', 'billing');

        $this->app->singleton(ChipService::class);
        $this->app->singleton(BillingService::class);
        $this->app->singleton(ResellerCommissionService::class);
        $this->app->singleton(ChipWebhookService::class);
    }

    public function boot(): void
    {
        // ── Laluan API (auth + tenant) ─────────────────────────────
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware('api')->prefix('api/v1')->group(__DIR__ . '/routes/api.php');
        }

        // ── Webhook CHIP (awam — pengesahan melalui tandatangan RSA) ──
        // ⚠️ DI LUAR auth:sanctum dan DI LUAR middleware tenant.
        // CHIP tidak menghantar token kita, dan tiada TenantContext semasa
        // webhook tiba. Keselamatan = tandatangan RSA + timestamp + idempotency.
        Route::post('api/v1/webhooks/chip', [
            \App\Modules\Billing\Http\Controllers\BillingController::class,
            'webhookChip',
        ])->middleware('api');

        // ── Pautan rujukan awam /r/{code} ───────────────────────────
        Route::get('r/{code}', [
            \App\Modules\Billing\Http\Controllers\BillingController::class,
            'pautanRujukan',
        ])->middleware('web');
    }
}
