<?php

namespace App\Modules\Blast;

use App\Modules\Blast\Services\BlastService;
use App\Modules\Blast\Services\DeliverabilityService;
use App\Modules\Blast\Services\OptOutService;
use App\Modules\Blast\Services\RateLimiterService;
use App\Modules\Blast\Services\TemplateService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BlastServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Blast/config/blast.php',
            'blast'
        );

        $this->app->singleton(TemplateService::class);
        $this->app->singleton(OptOutService::class);
        $this->app->singleton(RateLimiterService::class);
        $this->app->singleton(DeliverabilityService::class);
        $this->app->singleton(BlastService::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/blast')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
