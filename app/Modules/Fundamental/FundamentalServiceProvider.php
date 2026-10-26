<?php

namespace App\Modules\Fundamental;

use App\Modules\Fundamental\Services\FundamentalService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * B-Fundamental service provider (BAB 90).
 *
 * Platform-level: health check & observability log tidak perlu tenant.
 */
class FundamentalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/fundamental.php',
            'fundamental'
        );

        $this->app->singleton(FundamentalService::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
