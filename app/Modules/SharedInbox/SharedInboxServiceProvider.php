<?php

namespace App\Modules\SharedInbox;

use App\Modules\SharedInbox\Services\SharedInboxService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SharedInboxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/shared_inbox.php', 'shared_inbox');

        $this->app->singleton(SharedInboxService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        if (file_exists(__DIR__.'/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/shared-inbox')
                ->group(__DIR__.'/routes/api.php');
        }
    }
}
