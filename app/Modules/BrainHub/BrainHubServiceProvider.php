<?php

namespace App\Modules\BrainHub;

use Illuminate\Support\ServiceProvider;
use App\Modules\BrainHub\Services\InsightService;
use App\Modules\BrainHub\Services\ForecastService;
use App\Modules\BrainHub\Services\RecommendationService;
use App\Modules\BrainHub\Services\AIPatternEngine;

class BrainHubServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register config
        $this->mergeConfigFrom(
            __DIR__ . '/config/brainhub.php',
            'brainhub'
        );

        // Bind services
        $this->app->singleton(InsightService::class);
        $this->app->singleton(ForecastService::class);
        $this->app->singleton(RecommendationService::class);
        $this->app->singleton(AIPatternEngine::class);
    }

    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/config/brainhub.php' => config_path('brainhub.php'),
        ], 'brainhub-config');

        // Publish migrations
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }
}
