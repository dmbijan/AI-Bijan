<?php

namespace App\Modules\Fleet;

use App\Modules\Fleet\Services\FleetService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class FleetServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Fleet/config/fleet.php',
            'fleet'
        );

        $this->app->singleton(FleetService::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/fleet')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
