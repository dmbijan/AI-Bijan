<?php

namespace App\Modules\DocumentAI;

use Illuminate\Support\Facades\Route;

use Illuminate\Support\ServiceProvider;

class DocumentAIServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Modules\DocumentAI\Services\DocumentOCRService::class);
        $this->app->singleton(\App\Modules\DocumentAI\Services\DocumentExtractionService::class);
        $this->app->singleton(\App\Modules\DocumentAI\Services\DocumentClassificationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])->prefix('api/v1/document-ai')->group(__DIR__ . '/routes/api.php');
        }
    }
}
