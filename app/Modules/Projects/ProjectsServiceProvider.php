<?php

namespace App\Modules\Projects;

use Illuminate\Support\Facades\Route;

use Illuminate\Support\ServiceProvider;
use App\Modules\Projects\Services\ProjectService;
use App\Modules\Projects\Services\TaskService;
use App\Modules\Projects\Services\MilestoneService;
use App\Modules\Projects\Services\TimesheetService;
use App\Modules\Projects\Services\DocumentService;

class ProjectsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Load config
        $this->loadConfig();

        // Bind services
        $this->app->bind(ProjectService::class);
        $this->app->bind(TaskService::class);
        $this->app->bind(MilestoneService::class);
        $this->app->bind(TimesheetService::class);
        $this->app->bind(DocumentService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            // Route `projects/*` dikendalikan Api\V1\ProjectController di routes/api.php.
            // Route modul didaftar di sini hanya jika prefix berbeza (elak pertindihan).
            Route::middleware(['api', 'auth:sanctum', 'tenant'])->prefix('api/v1/pm')->group(__DIR__ . '/routes/api.php');
        }
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /**
     * Load module configuration.
     */
    protected function loadConfig(): void
    {
        $configPath = __DIR__ . '/config/projects.php';
        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'projects');
        }
    }
}
