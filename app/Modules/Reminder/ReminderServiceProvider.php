<?php

namespace App\Modules\Reminder;

use App\Modules\Reminder\Services\ReminderService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ReminderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Reminder/config/reminder.php',
            'reminder'
        );

        $this->app->singleton(ReminderService::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/reminders')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
