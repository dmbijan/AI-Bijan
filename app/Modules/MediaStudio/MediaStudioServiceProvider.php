<?php

namespace App\Modules\MediaStudio;

use App\Modules\MediaStudio\Services\ConsentGateService;
use App\Modules\MediaStudio\Services\MediaModerationService;
use App\Modules\MediaStudio\Services\MediaRetentionService;
use App\Modules\MediaStudio\Services\VideoUnderstandingService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MediaStudioServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/MediaStudio/config/media_studio.php',
            'media_studio'
        );

        $this->app->singleton(MediaModerationService::class);
        $this->app->singleton(ConsentGateService::class);
        $this->app->singleton(MediaRetentionService::class);
        $this->app->singleton(VideoUnderstandingService::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/media')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
