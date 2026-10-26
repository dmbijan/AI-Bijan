<?php

namespace App\Modules\McpStudio;

use App\Modules\McpStudio\Services\McpServerService;
use App\Modules\McpStudio\Services\McpToolRegistry;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class McpStudioServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/McpStudio/config/mcp.php',
            'mcp'
        );

        $this->app->singleton(McpToolRegistry::class);
        $this->app->singleton(McpServerService::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/mcp')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
