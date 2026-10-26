<?php

namespace App\Modules\AgentStudio;

use App\Modules\AgentStudio\Services\AgentBlueprintService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AgentStudioServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/AgentStudio/config/agent_studio.php',
            'agent_studio'
        );

        $this->app->singleton(AgentBlueprintService::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/agent-studio')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
