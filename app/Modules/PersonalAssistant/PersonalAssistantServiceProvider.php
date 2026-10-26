<?php

namespace App\Modules\PersonalAssistant;

use Illuminate\Support\ServiceProvider;
use App\Modules\PersonalAssistant\Services\AssistantService;
use App\Modules\PersonalAssistant\Services\ContextBuilderService;
use App\Modules\PersonalAssistant\Services\MemoryService;
use App\Modules\PersonalAssistant\Services\ProactiveAlertService;
use App\Modules\PersonalAssistant\Services\TaskExecutor;

class PersonalAssistantServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/personal_assistant.php',
            'personal_assistant'
        );

        $this->app->singleton(AssistantService::class);
        $this->app->singleton(TaskExecutor::class);
        $this->app->singleton(ContextBuilderService::class);
        $this->app->singleton(MemoryService::class);
        $this->app->singleton(ProactiveAlertService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Migrations didaftarkan global dalam database/migrations
        // 2026_10_20_003000_create_personal_assistant_tables.php
    }
}
