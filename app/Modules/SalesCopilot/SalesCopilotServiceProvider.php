<?php

namespace App\Modules\SalesCopilot;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SalesCopilotServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/SalesCopilot/config/sales_copilot.php',
            'sales_copilot'
        );
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/sales-copilot')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
