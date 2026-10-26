<?php

namespace App\Modules\Loyalty;

use Illuminate\Support\ServiceProvider;

class LoyaltyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/config/loyalty.php',
            'loyalty'
        );

        // Bind services
        $this->app->bind(\App\Modules\Loyalty\Services\MemberService::class, \App\Modules\Loyalty\Services\MemberService::class);
        $this->app->bind(\App\Modules\Loyalty\Services\PointsService::class, \App\Modules\Loyalty\Services\PointsService::class);
        $this->app->bind(\App\Modules\Loyalty\Services\RewardService::class, \App\Modules\Loyalty\Services\RewardService::class);
        $this->app->bind(\App\Modules\Loyalty\Services\TierService::class, \App\Modules\Loyalty\Services\TierService::class);
        $this->app->bind(\App\Modules\Loyalty\Services\LoyaltyAnalyticsService::class, \App\Modules\Loyalty\Services\LoyaltyAnalyticsService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // Load routes
        if (file_exists(__DIR__ . '/routes/api.php')) {
            $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        }
    }
}
