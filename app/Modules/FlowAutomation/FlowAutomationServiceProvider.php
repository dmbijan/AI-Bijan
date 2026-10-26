<?php

namespace App\Modules\FlowAutomation;

use Illuminate\Support\ServiceProvider;
use App\Modules\FlowAutomation\Services\ActionDispatcher;
use App\Modules\FlowAutomation\Services\FlowEngine;

class FlowAutomationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/flow_automation.php',
            'flow_automation'
        );

        $this->app->singleton(ActionDispatcher::class);
        $this->app->singleton(FlowEngine::class, function ($app) {
            return new FlowEngine($app->make(ActionDispatcher::class));
        });
    }

    public function boot(): void
    {
        // Migrations didaftarkan global dalam database/migrations
        // 2026_10_20_005000_create_flow_automation_tables.php
    }
}
