<?php

namespace App\Modules\Finance;

use Illuminate\Support\Facades\Route;

use Illuminate\Support\ServiceProvider;

class FinanceServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Modules\Finance\Services\JournalService::class);
        $this->app->singleton(\App\Modules\Finance\Services\FinancialReportService::class);
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
            Route::middleware(['api', 'auth:sanctum', 'tenant'])->prefix('api/v1/finance')->group(__DIR__ . '/routes/api.php');
        }
    }
}
