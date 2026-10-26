<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| B-SCM (Supply Chain Management) API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('scm')->group(function () {
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
});
