<?php

use App\Modules\Fundamental\Http\Controllers\FundamentalController;
use Illuminate\Support\Facades\Route;

/**
 * Laluan API B-Fundamental (BAB 90).
 *
 * ⚠️ `live` TIDAK perlukan auth (load balancer probe).
 * `ready`/`deep` perlukan auth + tenant.
 */
Route::prefix('fundamental')->middleware(['auth:sanctum'])->group(function () {

    // ---- HEALTH (live tidak perlu tenant) ----
    Route::get('health/live', [FundamentalController::class, 'live'])
        ->withoutMiddleware(['auth:sanctum', 'tenant']);

    Route::get('health/ready', [FundamentalController::class, 'ready'])
        ->middleware('tenant');

    Route::get('health/deep', [FundamentalController::class, 'deep'])
        ->middleware('tenant');

    // ---- OBSERVABILITY ----
    Route::get('logs', [FundamentalController::class, 'logs'])
        ->middleware(['tenant', 'perm:fundamental.view']);

    Route::get('metrics', [FundamentalController::class, 'metrics'])
        ->middleware(['tenant', 'perm:fundamental.view']);

    Route::post('metrics', [FundamentalController::class, 'storeMetric'])
        ->middleware(['tenant', 'perm:fundamental.manage']);

    Route::get('metrics/series', [FundamentalController::class, 'metricSeries'])
        ->middleware(['tenant', 'perm:fundamental.view']);

    // ---- ALERTS ----
    Route::get('alerts', [FundamentalController::class, 'alerts'])
        ->middleware(['tenant', 'perm:fundamental.view']);

    Route::post('alerts', [FundamentalController::class, 'storeAlertRule'])
        ->middleware(['tenant', 'perm:fundamental.manage']);

    // ---- BACKUP ----
    Route::get('backup', [FundamentalController::class, 'backupStatus'])
        ->middleware(['tenant', 'perm:fundamental.view']);

    Route::post('backup/drills', [FundamentalController::class, 'storeRestoreDrill'])
        ->middleware(['tenant', 'perm:fundamental.manage']);
});
