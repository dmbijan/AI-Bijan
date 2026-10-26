<?php

namespace App\Modules\Marketplace;

use Illuminate\Support\Facades\Route;

use Illuminate\Support\ServiceProvider;

class MarketplaceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])->prefix('api/v1/marketplace')->group(__DIR__ . '/routes/api.php');
        }
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // Config
        $this->mergeConfigFrom(__DIR__ . '/config/marketplace.php', 'marketplace');
        $this->publishes([
            __DIR__ . '/config/marketplace.php' => config_path('marketplace.php'),
        ], 'marketplace-config');
    }

    public function register(): void
    {
        // Bind services
        $this->app->singleton(\App\Modules\Marketplace\Services\ProductService::class, function ($app) {
            return new \App\Modules\Marketplace\Services\ProductService();
        });

        $this->app->singleton(\App\Modules\Marketplace\Services\VendorService::class, function ($app) {
            return new \App\Modules\Marketplace\Services\VendorService();
        });

        $this->app->singleton(\App\Modules\Marketplace\Services\OrderService::class, function ($app) {
            return new \App\Modules\Marketplace\Services\OrderService();
        });

        $this->app->singleton(\App\Modules\Marketplace\Services\CartService::class, function ($app) {
            return new \App\Modules\Marketplace\Services\CartService();
        });

        $this->app->singleton(\App\Modules\Marketplace\Services\ReviewService::class, function ($app) {
            return new \App\Modules\Marketplace\Services\ReviewService();
        });
    }
}
