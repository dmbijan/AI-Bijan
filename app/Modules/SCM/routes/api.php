<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| B-SCM (Supply Chain Management) API Routes
|--------------------------------------------------------------------------
|
| ⚠️ JANGAN tambah Route::prefix('scm') di sini.
| ScmServiceProvider sudah mendaftar ->prefix('api/v1/scm').
| Menambah prefix di sini menghasilkan laluan berganda api/v1/scm/scm/*.
|
*/

// Suppliers
Route::middleware('perm:inventory.view')->group(function () {
    Route::get('suppliers', [\App\Modules\SCM\Http\Controllers\SupplierController::class, 'index']);
    Route::get('suppliers/{id}', [\App\Modules\SCM\Http\Controllers\SupplierController::class, 'show'])->whereNumber('id');
});

Route::middleware('perm:inventory.manage')->group(function () {
    Route::post('suppliers', [\App\Modules\SCM\Http\Controllers\SupplierController::class, 'store']);
    Route::put('suppliers/{id}', [\App\Modules\SCM\Http\Controllers\SupplierController::class, 'update'])->whereNumber('id');
    Route::post('suppliers/{id}/approve', [\App\Modules\SCM\Http\Controllers\SupplierController::class, 'approve'])->whereNumber('id');
});

// Purchase Orders
Route::middleware('perm:inventory.view')->group(function () {
    Route::get('purchase-orders', [\App\Modules\SCM\Http\Controllers\PurchaseOrderController::class, 'index']);
    Route::get('purchase-orders/{id}', [\App\Modules\SCM\Http\Controllers\PurchaseOrderController::class, 'show'])->whereNumber('id');
});

Route::middleware('perm:inventory.manage')->group(function () {
    Route::post('purchase-orders', [\App\Modules\SCM\Http\Controllers\PurchaseOrderController::class, 'store']);
    Route::post('purchase-orders/{id}/submit', [\App\Modules\SCM\Http\Controllers\PurchaseOrderController::class, 'submit'])->whereNumber('id');
    Route::post('purchase-orders/{id}/approve', [\App\Modules\SCM\Http\Controllers\PurchaseOrderController::class, 'approve'])->whereNumber('id');
    Route::post('purchase-orders/{id}/send', [\App\Modules\SCM\Http\Controllers\PurchaseOrderController::class, 'send'])->whereNumber('id');
    Route::post('purchase-orders/{id}/receive', [\App\Modules\SCM\Http\Controllers\PurchaseOrderController::class, 'receive'])->whereNumber('id');
    Route::post('purchase-orders/{id}/cancel', [\App\Modules\SCM\Http\Controllers\PurchaseOrderController::class, 'cancel'])->whereNumber('id');
});

// Kelompok 2 stok: ramalan (SUP-011), maklumat produk (SUP-013), pesanan semula (SUP-014), kos pendaratan (SUP-015)
use App\Modules\SCM\Http\Controllers\PurchaseOrderController;
use App\Modules\SCM\Http\Controllers\SupplierController;
use App\Modules\SCM\Http\Controllers\SupplyPlanningController as Plan;

Route::middleware('perm:inventory.view')->group(function () {
    Route::get('planning/forecast', [Plan::class, 'forecast']);
    Route::get('planning/reorder', [Plan::class, 'reorder']);
    Route::get('landed-costs', [Plan::class, 'landedCosts']);
    Route::get('pim', [Plan::class, 'pim']);
});
Route::middleware('perm:inventory.manage')->group(function () {
    Route::put('planning/items/{id}', [Plan::class, 'reorderSettings'])->whereNumber('id');
    Route::post('planning/reorder/draft-pos', [Plan::class, 'createPos']);
    Route::post('landed-costs', [Plan::class, 'storeLandedCost']);
    Route::put('pim/{id}', [Plan::class, 'savePim'])->whereNumber('id');
    Route::post('pim/{id}/variants', [Plan::class, 'variants'])->whereNumber('id');
});
// Kos pendaratan ubah nilai stok & kos jualan → perlu kelulusan stok
Route::middleware('perm:inventory.approve')->group(function () {
    Route::post('landed-costs/{id}/post', [Plan::class, 'postLandedCost'])->whereNumber('id');
});
