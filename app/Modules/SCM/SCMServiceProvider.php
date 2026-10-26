<?php

namespace App\Modules\SCM;

use Illuminate\Support\Facades\Route;

use Illuminate\Support\ServiceProvider;

class SCMServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Modules\SCM\Services\PurchaseOrderService::class);
        $this->app->singleton(\App\Modules\SCM\Services\SupplierManagementService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load migrations from this module
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        
        // Load routes
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])->prefix('api/v1/scm')->group(__DIR__ . '/routes/api.php');
        }
    }
}
