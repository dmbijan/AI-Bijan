<?php

namespace App\Modules\WhatsAppSuite;

use App\Modules\WhatsAppSuite\Services\WhatsAppService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class WhatsAppSuiteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/whatsapp_suite.php', 'whatsapp_suite');

        $this->app->singleton(WhatsAppService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        if (file_exists(__DIR__.'/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/whatsapp-suite')
                ->group(__DIR__.'/routes/api.php');
        }
    }
}
