<?php

namespace App\Modules\Assets;

use Illuminate\Support\Facades\Route;

use Illuminate\Support\ServiceProvider;
use App\Modules\Assets\Services\AssetService;
use App\Modules\Assets\Services\DepreciationService;
use App\Modules\Assets\Services\MaintenanceService;

class AssetsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Load config
        $this->loadConfig();

        // Bind services
        $this->app->bind(AssetService::class);
        $this->app->bind(DepreciationService::class);
        $this->app->bind(MaintenanceService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])->prefix('api/v1/assets')->group(__DIR__ . '/routes/api.php');
        }
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /**
     * Load module configuration.
     */
    protected function loadConfig(): void
    {
        $configPath = __DIR__ . '/config/assets.php';
        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'assets');
        }
    }
}
