<?php

namespace App\Modules\SignDocs;

use Illuminate\Support\ServiceProvider;

class SignDocsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind services
        $this->app->bind(\App\Modules\SignDocs\Services\DocumentService::class, function ($app) {
            return new \App\Modules\SignDocs\Services\DocumentService();
        });

        $this->app->bind(\App\Modules\SignDocs\Services\TemplateService::class, function ($app) {
            return new \App\Modules\SignDocs\Services\TemplateService();
        });

        $this->app->bind(\App\Modules\SignDocs\Services\SignatureService::class, function ($app) {
            return new \App\Modules\SignDocs\Services\SignatureService();
        });

        $this->app->bind(\App\Modules\SignDocs\Services\DocumentWorkflowService::class, function ($app) {
            return new \App\Modules\SignDocs\Services\DocumentWorkflowService();
        });
    }

    public function boot(): void
    {
        // Register config
        $this->mergeConfigFrom(__DIR__ . '/config/sign-docs.php', 'sign-docs');
    }
}
