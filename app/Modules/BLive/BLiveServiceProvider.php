<?php

namespace App\Modules\BLive;

use App\Modules\BLive\Services\AvatarEngineService;
use App\Modules\BLive\Services\AvatarService;
use App\Modules\BLive\Services\ConsentService;
use App\Modules\BLive\Services\HostBrainService;
use App\Modules\BLive\Services\LiveControlService;
use App\Modules\BLive\Services\LiveSessionService;
use App\Modules\BLive\Services\StreamingGatewayService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BLiveServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/blive.php', 'blive');

        $this->app->singleton(AvatarEngineService::class);
        $this->app->singleton(AvatarService::class);
        $this->app->singleton(ConsentService::class);
        $this->app->singleton(HostBrainService::class);
        $this->app->singleton(LiveControlService::class);
        $this->app->singleton(LiveSessionService::class);
        $this->app->singleton(StreamingGatewayService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        if (file_exists(__DIR__.'/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/blive')
                ->group(__DIR__.'/routes/api.php');
        }
    }
}
