<?php

namespace App\Modules\BusinessIntel;

use App\Modules\BusinessIntel\Services\BusinessIntelService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BusinessIntelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/BusinessIntel/config/business_intel.php',
            'business_intel'
        );

        $this->app->singleton(BusinessIntelService::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/business-intel')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
