<?php

namespace App\Modules\Prospecting;

use App\Modules\Prospecting\Services\ConsentService;
use App\Modules\Prospecting\Services\CsvImporterService;
use App\Modules\Prospecting\Services\DedupeService;
use App\Modules\Prospecting\Services\ProspectScoringService;
use App\Modules\Prospecting\Services\PublicScraperService;
use Illuminate\Support\ServiceProvider;

class ProspectingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Prospecting/config/prospecting.php',
            'prospecting'
        );

        $this->app->singleton(DedupeService::class);
        $this->app->singleton(ConsentService::class);
        $this->app->singleton(CsvImporterService::class);
        $this->app->singleton(PublicScraperService::class);
        $this->app->singleton(ProspectScoringService::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            \Illuminate\Support\Facades\Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/prospecting')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
