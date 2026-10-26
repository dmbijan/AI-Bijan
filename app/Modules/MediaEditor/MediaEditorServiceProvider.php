<?php

namespace App\Modules\MediaEditor;

use App\Modules\MediaEditor\Services\MediaEditorService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MediaEditorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/MediaEditor/config/media_editor.php',
            'media_editor'
        );

        $this->app->singleton(MediaEditorService::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/media-editor')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
