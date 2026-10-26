<?php

namespace App\Modules\Affiliate;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use App\Modules\Affiliate\Services\AffiliateService;
use App\Modules\Affiliate\Services\ReferralService;
use App\Modules\Affiliate\Services\CommissionService;
use App\Modules\Affiliate\Services\AffiliateAnalyticsService;

class AffiliateServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register config
        $this->mergeConfigFrom(
            __DIR__ . '/config/affiliate.php',
            'affiliate'
        );

        // Register services
        $this->app->singleton(AffiliateService::class);
        $this->app->singleton(ReferralService::class);
        $this->app->singleton(CommissionService::class);
        $this->app->singleton(AffiliateAnalyticsService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        if (is_dir(__DIR__ . '/../../database/migrations')) {
            $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        }

        // Load routes (jika fail wujud)
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])->prefix('api/v1/affiliate')->group(__DIR__ . '/routes/api.php');
        }
    }
}
