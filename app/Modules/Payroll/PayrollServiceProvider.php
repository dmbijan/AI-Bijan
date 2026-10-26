<?php

namespace App\Modules\Payroll;

use Illuminate\Support\Facades\Route;

use Illuminate\Support\ServiceProvider;

class PayrollServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register payroll models and services
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
            Route::middleware('api')->prefix('api/v1/payroll')->group(__DIR__ . '/routes/api.php');
        }
        
        // Register service provider bindings
        $this->app->singleton(\App\Modules\Payroll\Services\PayrollRunService::class);
        $this->app->singleton(\App\Modules\Payroll\Services\StatutoryCalculator::class);
        $this->app->singleton(\App\Modules\Payroll\Services\OvertimeCalculator::class);
    }
}
