<?php

namespace App\Modules\ExecutiveAI;

use App\Modules\ExecutiveAI\Services\AutonomyService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ExecutiveAIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/ExecutiveAI/config/executive_ai.php',
            'executive_ai'
        );

        $this->app->singleton(AutonomyService::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/executive-ai')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
