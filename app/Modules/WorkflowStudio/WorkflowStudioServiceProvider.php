<?php

namespace App\Modules\WorkflowStudio;

use App\Modules\WorkflowStudio\Services\WorkflowEngine;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class WorkflowStudioServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/WorkflowStudio/config/workflow_studio.php',
            'workflow_studio'
        );

        $this->app->singleton(WorkflowEngine::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/workflows')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
