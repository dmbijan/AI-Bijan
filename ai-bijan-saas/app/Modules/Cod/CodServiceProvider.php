<?php

namespace App\Modules\Cod;

use App\Modules\Cod\Services\CodService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CodServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Cod/config/cod.php',
            'cod'
        );

        $this->app->singleton(CodService::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/cod')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
