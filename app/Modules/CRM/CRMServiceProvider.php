<?php

namespace App\Modules\CRM;

use Illuminate\Support\Facades\Route;

use Illuminate\Support\ServiceProvider;

class CRMServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Modules\CRM\Services\LeadService::class);
        $this->app->singleton(\App\Modules\CRM\Services\OpportunityService::class);
        $this->app->singleton(\App\Modules\CRM\Services\QuotationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware('api')->prefix('api/v1/crm')->group(__DIR__ . '/routes/api.php');
        }
    }
}
