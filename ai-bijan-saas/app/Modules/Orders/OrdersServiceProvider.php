<?php

namespace App\Modules\Orders;

use App\Modules\Orders\Services\UnifiedOrderService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class OrdersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Orders/config/orders.php',
            'orders'
        );

        $this->app->singleton(UnifiedOrderService::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/orders')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
