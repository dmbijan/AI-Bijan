<?php

namespace App\Modules\OmniOrder;

use App\Modules\OmniOrder\Services\OmniOrderService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class OmniOrderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/omni_order.php', 'omni_order');

        $this->app->singleton(OmniOrderService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        if (file_exists(__DIR__.'/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/omni-order')
                ->group(__DIR__.'/routes/api.php');
        }
    }
}
