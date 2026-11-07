<?php

namespace App\Modules\OpenGenAI;

use App\Modules\OpenGenAI\Services\GenAiService;
use App\Modules\OpenGenAI\Services\ProviderAdapter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class OpenGenAIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/open_gen_ai.php', 'open_gen_ai');

        $this->app->singleton(ProviderAdapter::class);
        $this->app->singleton(GenAiService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        if (file_exists(__DIR__.'/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/open-gen-ai')
                ->group(__DIR__.'/routes/api.php');
        }
    }
}
