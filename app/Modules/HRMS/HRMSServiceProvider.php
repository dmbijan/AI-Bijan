<?php

namespace App\Modules\HRMS;

use Illuminate\Support\Facades\Route;

use Illuminate\Support\ServiceProvider;
use App\Modules\HRMS\Services\EmployeeService;
use App\Modules\HRMS\Services\LeaveService;
use App\Modules\HRMS\Services\AttendanceService;
use App\Modules\HRMS\Services\ShiftService;

class HRMSServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Load config
        $this->loadConfig();

        // Bind services
        $this->app->bind(EmployeeService::class);
        $this->app->bind(LeaveService::class);
        $this->app->bind(AttendanceService::class);
        $this->app->bind(ShiftService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])->prefix('api/v1/hrms')->group(__DIR__ . '/routes/api.php');
        }
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /**
     * Load module configuration.
     */
    protected function loadConfig(): void
    {
        $configPath = __DIR__ . '/config/hrms.php';
        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'hrms');
        }
    }
}
