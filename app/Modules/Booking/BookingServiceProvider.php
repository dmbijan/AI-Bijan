<?php

namespace App\Modules\Booking;

use Illuminate\Support\Facades\Route;

use Illuminate\Support\ServiceProvider;

class BookingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])->prefix('api/v1/booking')->group(__DIR__ . '/routes/api.php');
        }
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // Config
        $this->mergeConfigFrom(__DIR__ . '/config/booking.php', 'booking');
        $this->publishes([
            __DIR__ . '/config/booking.php' => config_path('booking.php'),
        ], 'booking-config');
    }

    public function register(): void
    {
        // Bind services
        $this->app->singleton(\App\Modules\Booking\Services\ServiceCatalogService::class, function ($app) {
            return new \App\Modules\Booking\Services\ServiceCatalogService();
        });

        $this->app->singleton(\App\Modules\Booking\Services\SlotService::class, function ($app) {
            return new \App\Modules\Booking\Services\SlotService();
        });

        $this->app->singleton(\App\Modules\Booking\Services\AppointmentService::class, function ($app) {
            return new \App\Modules\Booking\Services\AppointmentService();
        });
    }
}
