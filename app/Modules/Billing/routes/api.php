<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Billing\Http\Controllers\BillingController;

/*
|--------------------------------------------------------------------------
| B-Billing Routes — CHIP + komisen reseller berulang
|--------------------------------------------------------------------------
|
| ⚠️ JANGAN tambah Route::prefix('billing') di sini.
| BillingServiceProvider sudah mendaftar prefix api/v1 — rujuk provider.
|
*/

// ── Langganan (tenant) ─────────────────────────────────────────
Route::middleware(['api', 'auth:sanctum', 'tenant'])->prefix('billing')->group(function () {
    Route::get('/subscription', [BillingController::class, 'langgananSemasa'])
        ->middleware('perm:entitlement.view');

    Route::post('/subscribe', [BillingController::class, 'langgan'])
        ->middleware('perm:entitlement.manage');
});

// ── Reseller (platform — reseller sendiri) ─────────────────────
Route::middleware(['api', 'auth:sanctum'])->prefix('reseller')->group(function () {
    Route::get('/me', [BillingController::class, 'resellerSaya']);
    Route::get('/commissions', [BillingController::class, 'senaraiKomisen']);
});
