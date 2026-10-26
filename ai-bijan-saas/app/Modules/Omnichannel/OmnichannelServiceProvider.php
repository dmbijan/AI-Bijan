<?php

namespace App\Modules\Omnichannel;

use Illuminate\Support\ServiceProvider;

class OmnichannelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind services
        $this->app->bind(\App\Modules\Omnichannel\Services\ChannelService::class, function ($app) {
            return new \App\Modules\Omnichannel\Services\ChannelService();
        });

        $this->app->bind(\App\Modules\Omnichannel\Services\ConversationService::class, function ($app) {
            return new \App\Modules\Omnichannel\Services\ConversationService();
        });

        $this->app->bind(\App\Modules\Omnichannel\Services\MessageService::class, function ($app) {
            return new \App\Modules\Omnichannel\Services\MessageService();
        });

        $this->app->bind(\App\Modules\Omnichannel\Services\InboundWebhookService::class, function ($app) {
            return new \App\Modules\Omnichannel\Services\InboundWebhookService();
        });

        $this->app->bind(\App\Modules\Omnichannel\Services\AnalyticsService::class, function ($app) {
            return new \App\Modules\Omnichannel\Services\AnalyticsService();
        });
    }

    public function boot(): void
    {
        // Register config
        $this->mergeConfigFrom(__DIR__ . '/config/omnichannel.php', 'omnichannel');
    }
}
