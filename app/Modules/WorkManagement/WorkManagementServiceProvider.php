<?php

namespace App\Modules\WorkManagement;

use App\Modules\WorkManagement\Services\WorkManagementService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class WorkManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/work_management.php', 'work_management');

        $this->app->singleton(WorkManagementService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        if (file_exists(__DIR__.'/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/work-management')
                ->group(__DIR__.'/routes/api.php');
        }
    }
}
