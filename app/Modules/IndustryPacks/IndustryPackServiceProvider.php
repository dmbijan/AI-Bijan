<?php

namespace App\Modules\IndustryPacks;

use Illuminate\Support\ServiceProvider;

class IndustryPackServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/industry_packs.php',
            'industry_packs'
        );

        $this->app->singleton(\App\Modules\IndustryPacks\Services\IndustryProfileService::class, function ($app) {
            return new \App\Modules\IndustryPacks\Services\IndustryProfileService();
        });

        $this->app->singleton(\App\Modules\IndustryPacks\Services\DashboardLayoutService::class, function ($app) {
            return new \App\Modules\IndustryPacks\Services\DashboardLayoutService();
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
