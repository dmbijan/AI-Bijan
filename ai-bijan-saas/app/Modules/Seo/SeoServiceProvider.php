<?php

namespace App\Modules\Seo;

use App\Modules\Seo\Services\SeoService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SeoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Seo/config/seo.php',
            'seo'
        );

        $this->app->singleton(SeoService::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/seo')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
