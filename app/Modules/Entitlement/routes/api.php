<?php

use App\Modules\Entitlement\Http\Controllers\EntitlementController;
use Illuminate\Support\Facades\Route;

/**
 * Laluan API B-Entitlement (BAB 90).
 */
Route::prefix('entitlement')->middleware(['auth:sanctum', 'tenant'])->group(function () {

    // ---- STATIK ----
    Route::get('summary', [EntitlementController::class, 'summary'])
        ->middleware('perm:entitlement.view');

    Route::get('plans', [EntitlementController::class, 'plans'])
        ->middleware('perm:entitlement.view');

    Route::get('active-modules', [EntitlementController::class, 'activeModules'])
        ->middleware('perm:entitlement.view');

    Route::get('limits', [EntitlementController::class, 'limits'])
        ->middleware('perm:entitlement.view');

    Route::get('catalogue', [EntitlementController::class, 'catalogue'])
        ->middleware('perm:entitlement.view');

    Route::post('plans/seed', [EntitlementController::class, 'seedPlans'])
        ->middleware('perm:entitlement.manage');

    // ---- MUTASI ----
    Route::post('change-plan', [EntitlementController::class, 'changePlan'])
        ->middleware('perm:entitlement.manage');

    Route::post('grant-module', [EntitlementController::class, 'grantModule'])
        ->middleware('perm:entitlement.manage');

    Route::post('revoke-module', [EntitlementController::class, 'revokeModule'])
        ->middleware('perm:entitlement.manage');
});
