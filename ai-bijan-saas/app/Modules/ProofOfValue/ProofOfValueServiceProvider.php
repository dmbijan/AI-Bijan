<?php

namespace App\Modules\ProofOfValue;

use App\Modules\ProofOfValue\Services\ProofOfValueService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ProofOfValueServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/ProofOfValue/config/proof_of_value.php',
            'proof_of_value'
        );

        $this->app->singleton(ProofOfValueService::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/proof-of-value')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
