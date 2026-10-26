<?php

namespace App\Modules\COGS;

use Illuminate\Support\ServiceProvider;

class COGSServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/config/cogs.php',
            'cogs'
        );

        // Bind services
        $this->app->singleton(\App\Modules\COGS\Services\CostCalculationService::class, function ($app) {
            return new \App\Modules\COGS\Services\CostCalculationService();
        });

        $this->app->singleton(\App\Modules\COGS\Services\MarginAnalysisService::class, function ($app) {
            return new \App\Modules\COGS\Services\MarginAnalysisService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }
}
