<?php

namespace App\Modules\ScreenRecorder;

use App\Modules\ScreenRecorder\Services\ScreenRecorderService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ScreenRecorderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/screen_recorder.php', 'screen_recorder');

        $this->app->singleton(ScreenRecorderService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        if (file_exists(__DIR__.'/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/screen-recorder')
                ->group(__DIR__.'/routes/api.php');
        }
    }
}
