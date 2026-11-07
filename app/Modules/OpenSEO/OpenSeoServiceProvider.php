<?php

namespace App\Modules\OpenSEO;

use App\Modules\OpenSEO\Services\SeoIntelligenceService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class OpenSeoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/open_seo.php', 'open_seo');

        $this->app->singleton(SeoIntelligenceService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        if (file_exists(__DIR__.'/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/open-seo')
                ->group(__DIR__.'/routes/api.php');
        }
    }
}
