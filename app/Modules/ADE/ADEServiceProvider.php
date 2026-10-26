<?php

namespace App\Modules\ADE;

use App\Modules\ADE\Services\AdeCoordinatorService;
use App\Modules\ADE\Services\AdeOrchestrator;
use App\Modules\ADE\Services\AdeReviewService;
use App\Modules\ADE\Services\AdeSandboxService;
use App\Modules\ADE\Services\AdeSkillService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ADEServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/ADE/config/ade.php',
            'ade'
        );

        $this->app->singleton(AdeSkillService::class);
        $this->app->singleton(AdeSandboxService::class);
        $this->app->singleton(AdeCoordinatorService::class);
        $this->app->singleton(AdeReviewService::class);
        $this->app->singleton(AdeOrchestrator::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/ade')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
