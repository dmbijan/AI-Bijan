<?php

namespace App\Modules\POSBpos;

use App\Modules\POSBpos\Services\BposService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class POSBposServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/pos_bpos.php', 'pos_bpos');

        $this->app->singleton(BposService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        if (file_exists(__DIR__.'/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/pos-bpos')
                ->group(__DIR__.'/routes/api.php');
        }
    }
}
