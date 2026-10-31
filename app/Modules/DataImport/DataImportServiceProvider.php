<?php

namespace App\Modules\DataImport;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/** TEC-018 / Fasa 2.1 — Import Pintar (CSV): pratonton → sahkan → simpan. */
class DataImportServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        Route::middleware(['api', 'auth:sanctum', 'tenant'])->prefix('api/v1/imports')->group(__DIR__.'/routes/api.php');
    }
}
