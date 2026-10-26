<?php

use Illuminate\Support\Facades\Route;
use App\Modules\POS\Http\Controllers\RegisterController;
use App\Modules\POS\Http\Controllers\SaleController;
use App\Modules\POS\Http\Controllers\CashShiftController;

// POS Registers
Route::middleware('perm:assets.view')->group(function () {
    Route::get('/registers', [RegisterController::class, 'index']);
    Route::get('/registers/{id}', [RegisterController::class, 'show'])->whereNumber('id');
});

Route::middleware('perm:assets.manage')->group(function () {
    Route::post('/registers', [RegisterController::class, 'store']);
    Route::put('/registers/{id}', [RegisterController::class, 'update'])->whereNumber('id');
    Route::delete('/registers/{id}', [RegisterController::class, 'destroy'])->whereNumber('id');
    Route::post('/registers/{id}/toggle', [RegisterController::class, 'toggleActive'])->whereNumber('id');
    Route::get('/registers/{id}/summary', [RegisterController::class, 'salesSummary'])->whereNumber('id');
});

// Cash Shifts
Route::middleware('perm:assets.view')->group(function () {
    Route::get('/shifts', [CashShiftController::class, 'index']);
    Route::get('/shifts/open/{registerId}', [CashShiftController::class, 'getOpen'])->whereNumber('registerId');
    Route::get('/shifts/{id}', [CashShiftController::class, 'show'])->whereNumber('id');
    Route::get('/shifts/summary', [CashShiftController::class, 'summary']);
});

Route::middleware('perm:assets.manage')->group(function () {
    Route::post('/shifts/open', [CashShiftController::class, 'open']);
    Route::post('/shifts/{id}/close', [CashShiftController::class, 'close'])->whereNumber('id');
    Route::post('/shifts/{id}/lock', [CashShiftController::class, 'lock'])->whereNumber('id');
});

// Sales
Route::middleware('perm:assets.view')->group(function () {
    Route::get('/sales', [SaleController::class, 'index']);
    Route::get('/sales/{id}', [SaleController::class, 'show'])->whereNumber('id');
    Route::get('/sales/summary', [SaleController::class, 'summary']);
});

Route::middleware('perm:assets.manage')->group(function () {
    // Draft sales
    Route::post('/sales/draft', [SaleController::class, 'storeDraft']);
    
    // Sale items management
    Route::post('/sales/{saleId}/items', [SaleController::class, 'addItem'])->whereNumber('saleId');
    Route::put('/sales/items/{itemId}', [SaleController::class, 'updateItem'])->whereNumber('itemId');
    Route::delete('/sales/items/{itemId}', [SaleController::class, 'removeItem'])->whereNumber('itemId');
    
    // Complete/cancel/refund
    Route::post('/sales/{saleId}/complete', [SaleController::class, 'complete'])->whereNumber('saleId');
    Route::post('/sales/{saleId}/cancel', [SaleController::class, 'cancel'])->whereNumber('saleId');
    Route::post('/sales/{saleId}/refund', [SaleController::class, 'refund'])->whereNumber('saleId');
});
