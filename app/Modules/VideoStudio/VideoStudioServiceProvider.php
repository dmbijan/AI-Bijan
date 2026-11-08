<?php

namespace App\Modules\VideoStudio;

use App\Modules\VideoStudio\Services\VideoRenderService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class VideoStudioServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/video_studio.php', 'video_studio');

        $this->app->singleton(VideoRenderService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        if (file_exists(__DIR__.'/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/video-studio')
                ->group(__DIR__.'/routes/api.php');
        }
    }
}
