<?php

namespace App\Modules\Expense;

use App\Modules\Expense\Services\ExpenseService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * B-Expense service provider (BAB 89 P1).
 */
class ExpenseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/expense.php',
            'expense'
        );

        $this->app->singleton(ExpenseService::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
