<?php

namespace App\Modules\VoiceStudio;

use App\Modules\VoiceStudio\Services\VoiceEngineService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class VoiceStudioServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/voice_studio.php', 'voice_studio');

        $this->app->singleton(VoiceEngineService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        if (file_exists(__DIR__.'/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/voice-studio')
                ->group(__DIR__.'/routes/api.php');
        }
    }
}
