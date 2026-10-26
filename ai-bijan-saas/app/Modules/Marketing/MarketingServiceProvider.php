<?php

namespace App\Modules\Marketing;

use Illuminate\Support\ServiceProvider;
use App\Modules\Marketing\Services\CampaignService;
use App\Modules\Marketing\Services\SegmentService;
use App\Modules\Marketing\Services\VoucherService;
use App\Modules\Marketing\Services\ContactService;
use App\Modules\Marketing\Services\DealService;
use App\Modules\Marketing\Services\SequenceService;
use App\Modules\Marketing\Services\WorkflowService;
use App\Modules\Marketing\Services\LeadScoreService;
use App\Modules\Marketing\Services\AdIntegrationService;
use App\Modules\Marketing\Services\AnalyticsService;

class MarketingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Marketing/config/marketing.php',
            'marketing'
        );

        // Bind services
        $this->app->singleton(CampaignService::class);
        $this->app->singleton(SegmentService::class);
        $this->app->singleton(VoucherService::class);
        $this->app->singleton(ContactService::class);
        $this->app->singleton(DealService::class);
        $this->app->singleton(SequenceService::class);
        $this->app->singleton(WorkflowService::class);
        $this->app->singleton(LeadScoreService::class);
        $this->app->singleton(AdIntegrationService::class);
        $this->app->singleton(AnalyticsService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        }
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }
}
