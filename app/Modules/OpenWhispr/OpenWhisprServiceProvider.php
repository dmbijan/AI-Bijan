<?php

namespace App\Modules\OpenWhispr;

use App\Modules\OpenWhispr\Services\DictationService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class OpenWhisprServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/open_whispr.php', 'open_whispr');

        $this->app->singleton(DictationService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        if (file_exists(__DIR__.'/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/open-whispr')
                ->group(__DIR__.'/routes/api.php');
        }
    }
}
