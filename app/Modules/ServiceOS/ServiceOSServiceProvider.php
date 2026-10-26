<?php

namespace App\Modules\ServiceOS;

use App\Modules\ServiceOS\Services\ServiceOSService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * B-ServiceOS service provider (BAB 87).
 *
 * NOTA: prefix `api/v1/service-os` didaftarkan di sini, manakala
 * laluan dalam `routes/api.php` bermula dengan `service-os`.
 */
class ServiceOSServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/service_os.php',
            'service_os'
        );

        $this->app->singleton(ServiceOSService::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
