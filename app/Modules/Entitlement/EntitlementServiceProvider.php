<?php

namespace App\Modules\Entitlement;

use App\Modules\Entitlement\Services\EntitlementService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * B-Entitlement service provider (BAB 90).
 */
class EntitlementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/entitlement.php',
            'entitlement'
        );

        $this->app->singleton(EntitlementService::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant', 'idempotent'])
                ->prefix('api/v1')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
