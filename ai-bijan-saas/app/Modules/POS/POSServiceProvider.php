<?php

namespace App\Modules\POS;

use Illuminate\Support\Facades\Route;

use Illuminate\Support\ServiceProvider;
use App\Modules\POS\Services\SaleService;
use App\Modules\POS\Services\CashShiftService;
use App\Modules\POS\Services\RegisterService;

class POSServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->loadConfig();

        $this->app->bind(SaleService::class);
        $this->app->bind(CashShiftService::class);
        $this->app->bind(RegisterService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware('api')->prefix('api/v1/pos')->group(__DIR__ . '/routes/api.php');
        }
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /**
     * Load module configuration.
     */
    protected function loadConfig(): void
    {
        $configPath = __DIR__ . '/config/pos.php';
        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'pos');
        }
    }
}
