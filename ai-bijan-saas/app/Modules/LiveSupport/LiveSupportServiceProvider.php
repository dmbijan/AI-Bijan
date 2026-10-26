<?php

namespace App\Modules\LiveSupport;

use App\Modules\LiveSupport\Services\LiveSupportService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class LiveSupportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/LiveSupport/config/live_support.php',
            'live_support'
        );

        $this->app->singleton(LiveSupportService::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/live-support')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
