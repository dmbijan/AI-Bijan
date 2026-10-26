<?php

namespace App\Modules\Boardroom;

use Illuminate\Support\ServiceProvider;
use App\Modules\Boardroom\Services\BoardroomOrchestrator;

class BoardroomServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/boardroom.php',
            'boardroom'
        );

        $this->app->singleton(BoardroomOrchestrator::class);
    }

    public function boot(): void
    {
        // Migrations didaftarkan global dalam database/migrations
        // 2026_10_20_004000_create_boardroom_tables.php
    }
}
