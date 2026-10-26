<?php

namespace App\Modules\Logistics;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class LogisticsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/logistics.php', 'logistics');

        // Bind services
        $this->app->singleton(\App\Modules\Logistics\Services\WarehouseService::class, function () {
            return new \App\Modules\Logistics\Services\WarehouseService();
        });

        $this->app->singleton(\App\Modules\Logistics\Services\StockMoveService::class, function () {
            return new \App\Modules\Logistics\Services\StockMoveService();
        });

        $this->app->singleton(\App\Modules\Logistics\Services\ShipmentService::class, function () {
            return new \App\Modules\Logistics\Services\ShipmentService();
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }
}
