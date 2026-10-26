<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Marketplace\Http\Controllers\ProductController;
use App\Modules\Marketplace\Http\Controllers\VendorController;
use App\Modules\Marketplace\Http\Controllers\OrderController;
use App\Modules\Marketplace\Http\Controllers\CartController;
use App\Modules\Marketplace\Http\Controllers\ReviewController;

/*
|--------------------------------------------------------------------------
| Marketplace Module Routes
|--------------------------------------------------------------------------
|
| E-commerce marketplace routes for product catalog, vendors, orders,
| shopping cart, and product reviews.
|
*/

// Product routes
Route::prefix('products')->name('products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->middleware(['perm:marketplace.view'])->name('index');
    Route::get('/featured', [ProductController::class, 'featured'])->middleware(['perm:marketplace.view'])->name('featured');
    Route::get('/search', [ProductController::class, 'search'])->middleware(['perm:marketplace.view'])->name('search');
    Route::get('/low-stock', [ProductController::class, 'lowStock'])->middleware(['perm:marketplace.view'])->name('low-stock');
    Route::get('/{id}', [ProductController::class, 'show'])->middleware(['perm:marketplace.view'])->name('show');
    
    // Manage products (admin/vendor only)
    Route::post('/', [ProductController::class, 'store'])->middleware(['perm:marketplace.manage'])->name('store');
    Route::put('/{id}', [ProductController::class, 'update'])->middleware(['perm:marketplace.manage'])->name('update');
    Route::delete('/{id}', [ProductController::class, 'destroy'])->middleware(['perm:marketplace.manage'])->name('destroy');
    Route::post('/{id}/stock', [ProductController::class, 'updateStock'])->middleware(['perm:marketplace.manage'])->name('update-stock');
    Route::post('/bulk-status', [ProductController::class, 'bulkUpdateStatus'])->middleware(['perm:marketplace.manage'])->name('bulk-status');
});

// Vendor routes
Route::prefix('vendors')->name('vendors.')->group(function () {
    Route::get('/', [VendorController::class, 'index'])->middleware(['perm:marketplace.view'])->name('index');
    Route::get('/top', [VendorController::class, 'topVendors'])->middleware(['perm:marketplace.view'])->name('top');
    Route::get('/{id}', [VendorController::class, 'show'])->middleware(['perm:marketplace.view'])->name('show');
    
    // Manage vendors (admin only)
    Route::post('/', [VendorController::class, 'store'])->middleware(['perm:marketplace.manage'])->name('store');
    Route::put('/{id}', [VendorController::class, 'update'])->middleware(['perm:marketplace.manage'])->name('update');
    Route::delete('/{id}', [VendorController::class, 'destroy'])->middleware(['perm:marketplace.manage'])->name('destroy');
    Route::post('/{id}/approve', [VendorController::class, 'approve'])->middleware(['perm:marketplace.manage'])->name('approve');
    Route::post('/{id}/reject', [VendorController::class, 'reject'])->middleware(['perm:marketplace.manage'])->name('reject');
    Route::post('/{id}/toggle', [VendorController::class, 'toggleActive'])->middleware(['perm:marketplace.manage'])->name('toggle');
});

// Order routes
Route::prefix('orders')->name('orders.')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->middleware(['perm:marketplace.view'])->name('index');
    Route::get('/statistics', [OrderController::class, 'statistics'])->middleware(['perm:marketplace.view'])->name('statistics');
    Route::get('/{id}', [OrderController::class, 'show'])->middleware(['perm:marketplace.view'])->name('show');
    
    // Manage orders
    Route::post('/', [OrderController::class, 'store'])->middleware(['perm:marketplace.manage'])->name('store');
    Route::post('/{id}/status', [OrderController::class, 'updateStatus'])->middleware(['perm:marketplace.manage'])->name('update-status');
    Route::post('/{id}/payment', [OrderController::class, 'confirmPayment'])->middleware(['perm:marketplace.manage'])->name('confirm-payment');
    Route::post('/{id}/cancel', [OrderController::class, 'cancel'])->middleware(['perm:marketplace.manage'])->name('cancel');
    Route::post('/{id}/refund', [OrderController::class, 'refund'])->middleware(['perm:marketplace.manage'])->name('refund');
    Route::post('/{id}/tracking', [OrderController::class, 'updateTracking'])->middleware(['perm:marketplace.manage'])->name('update-tracking');
});

// Cart routes
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->middleware(['perm:marketplace.view'])->name('index');
    Route::get('/totals', [CartController::class, 'totals'])->middleware(['perm:marketplace.view'])->name('totals');
    Route::get('/validate', [CartController::class, 'validate'])->middleware(['perm:marketplace.view'])->name('validate');
    
    // Manage cart
    Route::post('/', [CartController::class, 'store'])->middleware(['perm:marketplace.manage'])->name('store');
    Route::put('/{id}', [CartController::class, 'update'])->middleware(['perm:marketplace.manage'])->name('update');
    Route::delete('/{id}', [CartController::class, 'destroy'])->middleware(['perm:marketplace.manage'])->name('destroy');
    Route::delete('/', [CartController::class, 'clear'])->middleware(['perm:marketplace.manage'])->name('clear');
});

// Review routes
Route::prefix('reviews')->name('reviews.')->group(function () {
    Route::get('/product/{productId}', [ReviewController::class, 'index'])->middleware(['perm:marketplace.view'])->name('index');
    Route::get('/product/{productId}/statistics', [ReviewController::class, 'statistics'])->middleware(['perm:marketplace.view'])->name('statistics');
    Route::get('/most-reviewed', [ReviewController::class, 'mostReviewed'])->middleware(['perm:marketplace.view'])->name('most-reviewed');
    Route::get('/pending', [ReviewController::class, 'pending'])->middleware(['perm:marketplace.manage'])->name('pending');
    
    // Manage reviews
    Route::post('/', [ReviewController::class, 'store'])->middleware(['perm:marketplace.manage'])->name('store');
    Route::put('/{id}', [ReviewController::class, 'update'])->middleware(['perm:marketplace.manage'])->name('update');
    Route::delete('/{id}', [ReviewController::class, 'destroy'])->middleware(['perm:marketplace.manage'])->name('destroy');
    Route::post('/{id}/approve', [ReviewController::class, 'approve'])->middleware(['perm:marketplace.manage'])->name('approve');
    Route::post('/{id}/reject', [ReviewController::class, 'reject'])->middleware(['perm:marketplace.manage'])->name('reject');
    Route::post('/{id}/helpful', [ReviewController::class, 'markHelpful'])->middleware(['perm:marketplace.manage'])->name('mark-helpful');
    Route::post('/bulk-approve', [ReviewController::class, 'bulkApprove'])->middleware(['perm:marketplace.manage'])->name('bulk-approve');
    Route::post('/bulk-reject', [ReviewController::class, 'bulkReject'])->middleware(['perm:marketplace.manage'])->name('bulk-reject');
});
