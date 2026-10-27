<?php

namespace App\Modules\TeamAI;

use App\Modules\TeamAI\Services\TeamAiRepositorySync;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TeamAIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TeamAiRepositorySync::class);
    }

    public function boot(): void
    {
        Route::middleware(['api', 'auth:sanctum', 'tenant'])
            ->prefix('api/v1/team-ai')
            ->group(__DIR__.'/routes/api.php');
    }
}
