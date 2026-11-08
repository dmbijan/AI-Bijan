<?php

namespace App\Modules\ContentForge;

use App\Modules\ContentForge\Services\ContentForgeService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ContentForgeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/content_forge.php', 'content_forge');

        $this->app->singleton(ContentForgeService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        if (file_exists(__DIR__.'/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/content-forge')
                ->group(__DIR__.'/routes/api.php');
        }
    }
}
