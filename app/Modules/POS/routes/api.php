<?php

use Illuminate\Support\Facades\Route;
use App\Modules\POS\Http\Controllers\RegisterController;
use App\Modules\POS\Http\Controllers\SaleController;
use App\Modules\POS\Http\Controllers\CashShiftController;
use App\Modules\POS\Http\Controllers\ModifierController;
use App\Modules\POS\Http\Controllers\DineInController;
use App\Modules\POS\Http\Controllers\KitchenDisplayController;
use App\Modules\POS\Http\Controllers\ShiftCashController;
use App\Modules\POS\Http\Controllers\TngSoundboxController;
use App\Modules\POS\Http\Controllers\DiscountApprovalController;

/*
|--------------------------------------------------------------------------
| POS — laluan modul (prefix `api/v1/pos` didaftar oleh POSServiceProvider)
|--------------------------------------------------------------------------
|
| ⚠️ Kebenaran: `pos.view` / `pos.manage` (config/aibijan.php).
| Sebelum 27 Okt 2026 fail ini guna `assets.view`/`assets.manage` — salah
| modul (salinan), menyebabkan juruwang yang ada `pos.view` ditolak 403.
|
| ⚠️ Susunan: laluan statik (/summary, /limits, /areas) MESTI sebelum /{id}.
*/

// ── Paparan / rujukan (pos.view) ──────────────────────────────────
Route::middleware('perm:pos.view')->group(function () {
    Route::get('/registers', [RegisterController::class, 'index']);
    Route::get('/registers/{id}', [RegisterController::class, 'show'])->whereNumber('id');
    Route::get('/registers/{id}/summary', [RegisterController::class, 'salesSummary'])->whereNumber('id');

    Route::get('/shifts', [CashShiftController::class, 'index']);
    Route::get('/shifts/summary', [CashShiftController::class, 'summary']);
    Route::get('/shifts/open/{registerId}', [CashShiftController::class, 'getOpen'])->whereNumber('registerId');
    Route::get('/shifts/{id}', [CashShiftController::class, 'show'])->whereNumber('id');

    Route::get('/sales', [SaleController::class, 'index']);
    Route::get('/sales/summary', [SaleController::class, 'summary']);
    Route::get('/stock-exceptions', [SaleController::class, 'stockExceptions']);
    Route::get('/sales/{id}', [SaleController::class, 'show'])->whereNumber('id');

    // T-77 Modifier
    Route::get('/modifiers', [ModifierController::class, 'index']);
    Route::get('/modifiers/for-items', [ModifierController::class, 'forItems']);

    // T-76 Dine-in & Kitchen Display
    Route::get('/dine/areas', [DineInController::class, 'areas']);
    Route::get('/dine/tables', [DineInController::class, 'tables']);
    Route::get('/dine/orders', [DineInController::class, 'orders']);
    Route::get('/kitchen', [KitchenDisplayController::class, 'index']);

    // T-82 TNG
    Route::get('/tng/unclaimed', [TngSoundboxController::class, 'unclaimed']);

    // T-83 Diskaun
    Route::get('/discounts/limits', [DiscountApprovalController::class, 'limits']);
    Route::get('/discounts/pending', [DiscountApprovalController::class, 'pending']);
});

// ── Operasi (pos.manage) ──────────────────────────────────────────
Route::middleware('perm:pos.manage')->group(function () {
    // Registers
    Route::post('/registers', [RegisterController::class, 'store']);
    Route::put('/registers/{id}', [RegisterController::class, 'update'])->whereNumber('id');
    Route::delete('/registers/{id}', [RegisterController::class, 'destroy'])->whereNumber('id');
    Route::post('/registers/{id}/toggle', [RegisterController::class, 'toggleActive'])->whereNumber('id');

    // Shifts
    Route::post('/shifts/open', [CashShiftController::class, 'open']);
    Route::post('/shifts/{id}/close', [CashShiftController::class, 'close'])->whereNumber('id');
    Route::post('/shifts/{id}/lock', [CashShiftController::class, 'lock'])->whereNumber('id');

    // T-80 Cash movement & tutup syif dengan ringkasan
    Route::post('/shifts/{id}/cash', [ShiftCashController::class, 'store'])->whereNumber('id');
    Route::get('/shifts/{id}/cash-summary', [ShiftCashController::class, 'summary'])->whereNumber('id');
    Route::post('/shifts/{id}/close-with-summary', [ShiftCashController::class, 'close'])->whereNumber('id');

    // Sales
    Route::post('/sales/draft', [SaleController::class, 'storeDraft']);
    Route::post('/sales/{saleId}/items', [SaleController::class, 'addItem'])->whereNumber('saleId');
    Route::put('/sales/items/{itemId}', [SaleController::class, 'updateItem'])->whereNumber('itemId');
    Route::delete('/sales/items/{itemId}', [SaleController::class, 'removeItem'])->whereNumber('itemId');
    Route::post('/sales/{saleId}/complete', [SaleController::class, 'complete'])->whereNumber('saleId');
    Route::post('/sales/{saleId}/cancel', [SaleController::class, 'cancel'])->whereNumber('saleId');
    Route::post('/sales/{saleId}/refund', [SaleController::class, 'refund'])->whereNumber('saleId');

    // T-79 Multi-tender checkout
    Route::post('/sales/checkout', [SaleController::class, 'checkout']);
    Route::post('/stock-exceptions/{itemId}/resolve', [SaleController::class, 'resolveStockException'])->whereNumber('itemId');
    Route::post('/sales/{saleId}/pay', [SaleController::class, 'pay'])->whereNumber('saleId');

    // T-77 Modifier
    Route::post('/modifiers', [ModifierController::class, 'store']);
    Route::put('/modifiers/{id}', [ModifierController::class, 'update'])->whereNumber('id');
    Route::post('/modifiers/attach', [ModifierController::class, 'attach']);

    // T-76 Dine-in
    Route::post('/dine/areas', [DineInController::class, 'storeArea']);
    Route::post('/dine/tables', [DineInController::class, 'storeTable']);
    Route::post('/dine/tables/{id}/qr', [DineInController::class, 'regenerateQr'])->whereNumber('id');
    Route::post('/dine/orders', [DineInController::class, 'store']);
    Route::post('/dine/orders/{id}/accept', [DineInController::class, 'accept'])->whereNumber('id');
    Route::post('/dine/orders/{id}/reject', [DineInController::class, 'reject'])->whereNumber('id');

    // T-76 Kitchen Display
    Route::post('/kitchen/{id}/status', [KitchenDisplayController::class, 'updateStatus'])->whereNumber('id');

    // T-82 TNG
    Route::post('/tng/notify', [TngSoundboxController::class, 'receive']);
    Route::post('/tng/{id}/match', [TngSoundboxController::class, 'match'])->whereNumber('id');

    // T-83 Diskaun
    Route::post('/discounts', [DiscountApprovalController::class, 'request']);
    Route::post('/discounts/{id}/approve', [DiscountApprovalController::class, 'approve'])->whereNumber('id');
    Route::post('/discounts/{id}/reject', [DiscountApprovalController::class, 'reject'])->whereNumber('id');
});

