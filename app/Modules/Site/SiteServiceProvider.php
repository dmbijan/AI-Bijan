<?php

namespace App\Modules\Site;

use Illuminate\Support\Facades\Route;

use Illuminate\Support\ServiceProvider;

class SiteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])->prefix('api/v1/site')->group(__DIR__ . '/routes/api.php');
        }
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // Config
        $this->mergeConfigFrom(__DIR__ . '/config/site.php', 'site');
        $this->publishes([
            __DIR__ . '/config/site.php' => config_path('site.php'),
        ], 'site-config');
    }

    public function register(): void
    {
        // Bind services
        $this->app->singleton(\App\Modules\Site\Services\SiteService::class, function ($app) {
            return new \App\Modules\Site\Services\SiteService();
        });

        $this->app->singleton(\App\Modules\Site\Services\PageService::class, function ($app) {
            return new \App\Modules\Site\Services\PageService();
        });

        $this->app->singleton(\App\Modules\Site\Services\ThemeService::class, function ($app) {
            return new \App\Modules\Site\Services\ThemeService();
        });

        $this->app->singleton(\App\Modules\Site\Services\PostService::class, function ($app) {
            return new \App\Modules\Site\Services\PostService();
        });

        $this->app->singleton(\App\Modules\Site\Services\FormService::class, function ($app) {
            return new \App\Modules\Site\Services\FormService();
        });

        $this->app->singleton(\App\Modules\Site\Services\DomainService::class, function ($app) {
            return new \App\Modules\Site\Services\DomainService();
        });

        $this->app->singleton(\App\Modules\Site\Services\AnalyticsService::class, function ($app) {
            return new \App\Modules\Site\Services\AnalyticsService();
        });
    }
}
