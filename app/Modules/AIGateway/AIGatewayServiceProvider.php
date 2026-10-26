<?php

namespace App\Modules\AIGateway;

use Illuminate\Support\ServiceProvider;
use App\Modules\AIGateway\Services\GatewayService;
use App\Modules\AIGateway\Services\WalletService;
use App\Modules\AIGateway\Services\ModelCatalogService;
use App\Modules\AIGateway\Services\PiiRedactor;
use App\Modules\AIGateway\Services\PromptGuard;

class AIGatewayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/ai_gateway.php',
            'ai_gateway'
        );

        $this->app->singleton(PiiRedactor::class);
        $this->app->singleton(PromptGuard::class);
        $this->app->singleton(WalletService::class);
        $this->app->singleton(ModelCatalogService::class);

        $this->app->singleton(GatewayService::class, function ($app) {
            return new GatewayService(
                $app->make(PiiRedactor::class),
                $app->make(PromptGuard::class)
            );
        });
    }

    public function boot(): void
    {
        // Migrations didaftarkan global dalam database/migrations
        // 2026_10_20_006000_create_ai_gateway_tables.php
    }
}
