<?php

namespace App\Modules\Scheduling;

use App\Modules\Scheduling\Services\SchedulingService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SchedulingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/scheduling.php', 'scheduling');

        $this->app->singleton(SchedulingService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        if (file_exists(__DIR__.'/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/scheduling')
                ->group(__DIR__.'/routes/api.php');
        }
    }
}
