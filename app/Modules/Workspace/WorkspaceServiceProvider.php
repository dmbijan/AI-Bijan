<?php

namespace App\Modules\Workspace;

use App\Modules\Workspace\Services\WorkspaceService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class WorkspaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/workspace.php', 'workspace');

        $this->app->singleton(WorkspaceService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        if (file_exists(__DIR__.'/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/workspace')
                ->group(__DIR__.'/routes/api.php');
        }
    }
}
