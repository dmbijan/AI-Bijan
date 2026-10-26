<?php

namespace App\Modules\Link;

use App\Modules\Link\Services\LinkService;
use App\Modules\Link\Services\DigitalCardService;
use Illuminate\Support\ServiceProvider;

class LinkServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/config/link.php',
            'link'
        );

        // Bind services
        $this->app->singleton(LinkService::class, function ($app) {
            return new LinkService();
        });

        $this->app->singleton(DigitalCardService::class, function ($app) {
            return new DigitalCardService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // Load routes
        if (file_exists(__DIR__ . '/routes/api.php')) {
            $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        }
    }
}
