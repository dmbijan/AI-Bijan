<?php

namespace App\Modules\VirtualOffice;

use Illuminate\Support\Facades\Route;

use Illuminate\Support\ServiceProvider;
use App\Modules\VirtualOffice\Services\TaskService;
use App\Modules\VirtualOffice\Services\BoardService;
use App\Modules\VirtualOffice\Services\ScheduleService;
use App\Modules\VirtualOffice\Services\AgentService;

class VirtualOfficeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Load config
        $this->loadConfig();

        // Bind services
        $this->app->bind(TaskService::class);
        $this->app->bind(BoardService::class);
        $this->app->bind(ScheduleService::class);
        $this->app->bind(AgentService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware('api')->prefix('api/v1/virtual-office')->group(__DIR__ . '/routes/api.php');
        }
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /**
     * Load module configuration.
     */
    protected function loadConfig(): void
    {
        $configPath = __DIR__ . '/config/virtual_office.php';
        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'virtual_office');
        }
    }
}
