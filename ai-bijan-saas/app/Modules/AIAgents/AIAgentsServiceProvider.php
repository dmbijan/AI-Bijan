<?php

namespace App\Modules\AIAgents;

use Illuminate\Support\ServiceProvider;

class AIAgentsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/ai_agents.php',
            'ai_agents'
        );

        // Bind core services
        $this->app->singleton(\App\Modules\AIAgents\Services\AgentService::class, function ($app) {
            return new \App\Modules\AIAgents\Services\AgentService();
        });

        $this->app->singleton(\App\Modules\AIAgents\Services\AgentOrchestrator::class, function ($app) {
            return new \App\Modules\AIAgents\Services\AgentOrchestrator();
        });

        $this->app->singleton(\App\Modules\AIAgents\Services\ToolExecutor::class, function ($app) {
            return new \App\Modules\AIAgents\Services\ToolExecutor();
        });

        $this->app->singleton(\App\Modules\AIAgents\Services\AgentMemoryManager::class, function ($app) {
            return new \App\Modules\AIAgents\Services\AgentMemoryManager();
        });

        $this->app->singleton(\App\Modules\AIAgents\Services\AgentGovernance::class, function ($app) {
            return new \App\Modules\AIAgents\Services\AgentGovernance();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Migrations are registered globally in database/migrations
        // 2026_10_20_002000_create_ai_agents_tables.php
    }
}
