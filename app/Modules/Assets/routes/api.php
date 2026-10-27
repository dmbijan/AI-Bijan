<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Assets\Http\Controllers\AssetController;
use App\Modules\Assets\Http\Controllers\AssetCategoryController;
use App\Modules\Assets\Http\Controllers\MaintenanceController;

/*
| T-105 (28 Okt 2026): sebelum ini SEMUA laluan Assets hanya ada `auth:sanctum`
| + `tenant` — tiada pagar kebenaran. Staff biasa (cth `cashier`) boleh padam
| aset, ubah kategori dan tutup permintaan penyelenggaraan. Kini:
|   assets.view   → baca
|   assets.manage → tulis/padam
*/

// Asset Categories
Route::middleware('perm:assets.view')->group(function () {
    Route::get('/categories', [AssetCategoryController::class, 'index']);
    Route::get('/categories/{id}', [AssetCategoryController::class, 'show']);
});

Route::middleware('perm:assets.manage')->group(function () {
    Route::post('/categories', [AssetCategoryController::class, 'store']);
    Route::put('/categories/{id}', [AssetCategoryController::class, 'update']);
    Route::delete('/categories/{id}', [AssetCategoryController::class, 'destroy']);
});

// Assets
Route::middleware('perm:assets.view')->group(function () {
    Route::get('/', [AssetController::class, 'index']);
    Route::get('/summary', [AssetController::class, 'summary']);
    Route::get('/search', [AssetController::class, 'search']);
    Route::get('/{id}', [AssetController::class, 'show'])->whereNumber('id');
});

Route::middleware('perm:assets.manage')->group(function () {
    Route::post('/', [AssetController::class, 'store']);
    Route::put('/{id}', [AssetController::class, 'update'])->whereNumber('id');
    Route::delete('/{id}', [AssetController::class, 'destroy'])->whereNumber('id');
    Route::post('/{id}/assign', [AssetController::class, 'assign'])->whereNumber('id');
    Route::post('/{id}/unassign', [AssetController::class, 'unassign'])->whereNumber('id');
    Route::post('/{id}/documents', [AssetController::class, 'uploadDocument'])->whereNumber('id');
});

// Maintenance Requests
Route::prefix('/maintenance')->group(function () {
    Route::middleware('perm:assets.view')->group(function () {
        Route::get('/', [MaintenanceController::class, 'index']);
        Route::get('/summary', [MaintenanceController::class, 'summary']);
        Route::get('/upcoming', [MaintenanceController::class, 'upcoming']);
        Route::get('/{id}', [MaintenanceController::class, 'show'])->whereNumber('id');
        Route::get('/asset/{assetId}/history', [MaintenanceController::class, 'assetHistory'])->whereNumber('assetId');
    });

    Route::middleware('perm:assets.manage')->group(function () {
        Route::post('/', [MaintenanceController::class, 'store']);
        Route::put('/{id}', [MaintenanceController::class, 'update'])->whereNumber('id');
        Route::delete('/{id}', [MaintenanceController::class, 'destroy'])->whereNumber('id');
        Route::post('/{id}/complete', [MaintenanceController::class, 'complete'])->whereNumber('id');
        Route::post('/{id}/cancel', [MaintenanceController::class, 'cancel'])->whereNumber('id');
        Route::post('/{id}/assign-technician', [MaintenanceController::class, 'assignTechnician'])->whereNumber('id');
        Route::post('/{id}/parts', [MaintenanceController::class, 'addPart'])->whereNumber('id');
        Route::delete('/parts/{partId}', [MaintenanceController::class, 'removePart'])->whereNumber('partId');
    });
});

