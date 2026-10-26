<?php

namespace App\Modules\Invoicing;

use Illuminate\Support\Facades\Route;

use Illuminate\Support\ServiceProvider;
use App\Modules\Invoicing\Services\InvoiceService;
use App\Modules\Invoicing\Services\RecurringBillingService;
use App\Modules\Invoicing\Services\EInvoisService;

class InvoicingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/config/invoicing.php',
            'invoicing'
        );

        // Register services
        $this->app->singleton(InvoiceService::class);
        $this->app->singleton(RecurringBillingService::class);
        $this->app->singleton(EInvoisService::class);
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
            Route::middleware(['api', 'auth:sanctum', 'tenant'])->prefix('api/v1/invoicing')->group(__DIR__ . '/routes/api.php');
        }

        // Load views (if any)
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'invoicing');

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'invoicing');
    }
}
