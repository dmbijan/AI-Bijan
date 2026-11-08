<?php

namespace App\Modules\SocialBridge;

use App\Modules\SocialBridge\Services\SocialBridgeService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SocialBridgeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/social_bridge.php', 'social_bridge');

        $this->app->singleton(SocialBridgeService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        if (file_exists(__DIR__.'/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/social-bridge')
                ->group(__DIR__.'/routes/api.php');
        }
    }
}
