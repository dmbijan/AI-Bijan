<?php

namespace App\Modules\Community;

use App\Modules\Community\Services\CommunityService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CommunityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Community/config/community.php',
            'community'
        );

        $this->app->singleton(CommunityService::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/community')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
