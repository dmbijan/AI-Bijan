<?php

namespace App\Modules\ContentStudio;

use App\Modules\ContentStudio\Services\ContentStudioService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ContentStudioServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/ContentStudio/config/content_studio.php',
            'content_studio'
        );

        $this->app->singleton(ContentStudioService::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/content')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
