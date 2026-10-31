<?php

namespace App\Modules\POS;

use Illuminate\Support\Facades\Route;

use Illuminate\Support\ServiceProvider;
use App\Modules\POS\Services\SaleService;
use App\Modules\POS\Services\CashShiftService;
use App\Modules\POS\Services\RegisterService;
use App\Modules\POS\Services\TenderService;
use App\Modules\POS\Services\ModifierService;
use App\Modules\POS\Services\ServiceChargeService;
use App\Modules\POS\Services\KitchenService;
use App\Modules\POS\Services\DineOrderService;
use App\Modules\POS\Services\TngSoundboxService;
use App\Modules\POS\Services\DiscountApprovalService;
use App\Modules\POS\Services\SalePaymentService;

class POSServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->loadConfig();

        $this->app->bind(SaleService::class);
        $this->app->bind(CashShiftService::class);
        $this->app->bind(RegisterService::class);

        // BAB 88 — ciri B-POS yang dipindahkan (T-76 → T-83)
        $this->app->bind(TenderService::class);
        $this->app->bind(ModifierService::class);
        $this->app->bind(ServiceChargeService::class);
        $this->app->bind(KitchenService::class);
        $this->app->bind(DineOrderService::class);
        $this->app->bind(TngSoundboxService::class);
        $this->app->bind(DiscountApprovalService::class);
        $this->app->bind(SalePaymentService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])->prefix('api/v1/pos')->group(__DIR__ . '/routes/api.php');
        }
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /**
     * Load module configuration.
     */
    protected function loadConfig(): void
    {
        $configPath = __DIR__ . '/config/pos.php';
        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'pos');
        }
    }
}
