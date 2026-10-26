<?php

namespace App\Modules\Analytics;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AnalyticsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/analytics.php', 'analytics');

        // Bind services
        $this->app->singleton(\App\Modules\Analytics\Services\MetricService::class, function () {
            return new \App\Modules\Analytics\Services\MetricService();
        });

        $this->app->singleton(\App\Modules\Analytics\Services\ReportService::class, function () {
            return new \App\Modules\Analytics\Services\ReportService();
        });

        $this->app->singleton(\App\Modules\Analytics\Services\DashboardService::class, function () {
            return new \App\Modules\Analytics\Services\DashboardService();
        });

        $this->app->singleton(\App\Modules\Analytics\Services\AnalyticsEngine::class, function () {
            return new \App\Modules\Analytics\Services\AnalyticsEngine();
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }
}
