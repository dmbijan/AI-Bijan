<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Prospecting\Http\Controllers\ProspectingController;

/*
|--------------------------------------------------------------------------
| Prospecting Routes (BAB 82 / Fasa 1)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/prospecting
| Middleware: api, auth:sanctum, tenant (ditetapkan dalam ServiceProvider)
|
*/

// Prospect
Route::get('/prospects', [ProspectingController::class, 'index'])->middleware('perm:prospecting.view');
Route::get('/prospects/{id}', [ProspectingController::class, 'show'])->middleware('perm:prospecting.view');
Route::post('/prospects/{id}/consent', [ProspectingController::class, 'recordConsent'])->middleware('perm:prospecting.manage');
Route::post('/prospects/{id}/consent/revoke', [ProspectingController::class, 'revokeConsent'])->middleware('perm:prospecting.manage');
Route::post('/prospects/{id}/rescore', [ProspectingController::class, 'rescore'])->middleware('perm:prospecting.manage');
Route::get('/prospects/{id}/audit', [ProspectingController::class, 'auditLog'])->middleware('perm:prospecting.audit');

// Sumber
Route::get('/sources', [ProspectingController::class, 'sources'])->middleware('perm:prospecting.view');
Route::post('/sources', [ProspectingController::class, 'storeSource'])->middleware('perm:prospecting.manage');

// Import CSV
Route::post('/import-csv', [ProspectingController::class, 'importCsv'])->middleware('perm:prospecting.manage');

// Semak URL sebelum scraping (dry-run audit)
Route::post('/check-url', [ProspectingController::class, 'checkUrl'])->middleware('perm:prospecting.view');

// Audit consent PDPA
Route::get('/consent-report', [ProspectingController::class, 'consentReport'])->middleware('perm:prospecting.audit');
