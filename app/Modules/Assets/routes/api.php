<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Assets\Http\Controllers\AssetController;
use App\Modules\Assets\Http\Controllers\AssetCategoryController;
use App\Modules\Assets\Http\Controllers\MaintenanceController;

// Asset Categories
Route::get('/categories', [AssetCategoryController::class, 'index']);
Route::get('/categories/{id}', [AssetCategoryController::class, 'show']);
Route::post('/categories', [AssetCategoryController::class, 'store']);
Route::put('/categories/{id}', [AssetCategoryController::class, 'update']);
Route::delete('/categories/{id}', [AssetCategoryController::class, 'destroy']);

// Assets
Route::get('/', [AssetController::class, 'index']);
Route::get('/summary', [AssetController::class, 'summary']);
Route::get('/search', [AssetController::class, 'search']);
Route::post('/', [AssetController::class, 'store']);
Route::get('/{id}', [AssetController::class, 'show']);
Route::put('/{id}', [AssetController::class, 'update']);
Route::delete('/{id}', [AssetController::class, 'destroy']);
Route::post('/{id}/assign', [AssetController::class, 'assign']);
Route::post('/{id}/unassign', [AssetController::class, 'unassign']);
Route::post('/{id}/documents', [AssetController::class, 'uploadDocument']);

// Maintenance Requests
Route::prefix('/maintenance')->group(function () {
    Route::get('/', [MaintenanceController::class, 'index']);
    Route::get('/summary', [MaintenanceController::class, 'summary']);
    Route::get('/upcoming', [MaintenanceController::class, 'upcoming']);
    Route::get('/{id}', [MaintenanceController::class, 'show']);
    Route::post('/', [MaintenanceController::class, 'store']);
    Route::put('/{id}', [MaintenanceController::class, 'update']);
    Route::delete('/{id}', [MaintenanceController::class, 'destroy']);
    Route::post('/{id}/complete', [MaintenanceController::class, 'complete']);
    Route::post('/{id}/cancel', [MaintenanceController::class, 'cancel']);
    Route::post('/{id}/assign-technician', [MaintenanceController::class, 'assignTechnician']);
    Route::post('/{id}/parts', [MaintenanceController::class, 'addPart']);
    Route::delete('/parts/{partId}', [MaintenanceController::class, 'removePart']);
    Route::get('/asset/{assetId}/history', [MaintenanceController::class, 'assetHistory']);
});
