<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Blast\Http\Controllers\BlastController;

/*
|--------------------------------------------------------------------------
| Blast Engine Routes (BAB 82 / Fasa 2)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/blast
| Middleware: api, auth:sanctum, tenant (ditetapkan dalam ServiceProvider)
|
*/

// Kempen
Route::get('/campaigns', [BlastController::class, 'index'])->middleware('perm:blast.view');
Route::post('/campaigns', [BlastController::class, 'store'])->middleware('perm:blast.manage');
Route::get('/campaigns/{id}', [BlastController::class, 'show'])->middleware('perm:blast.view');
Route::put('/campaigns/{id}', [BlastController::class, 'update'])->middleware('perm:blast.manage');
Route::get('/campaigns/{id}/stats', [BlastController::class, 'stats'])->middleware('perm:blast.view');
Route::get('/campaigns/{id}/messages', [BlastController::class, 'messages'])->middleware('perm:blast.view');

// Audiens & penghantaran
Route::post('/campaigns/{id}/audience', [BlastController::class, 'buildAudience'])->middleware('perm:blast.manage');
Route::post('/campaigns/{id}/approve', [BlastController::class, 'approve'])->middleware('perm:blast.approve');
Route::post('/campaigns/{id}/schedule', [BlastController::class, 'schedule'])->middleware('perm:blast.execute');
Route::post('/campaigns/{id}/control', [BlastController::class, 'control'])->middleware('perm:blast.execute');

// Utiliti
Route::post('/preview', [BlastController::class, 'preview'])->middleware('perm:blast.view');
Route::get('/quota', [BlastController::class, 'quota'])->middleware('perm:blast.view');
Route::get('/suppressions', [BlastController::class, 'suppressions'])->middleware('perm:blast.execute');
Route::post('/suppressions', [BlastController::class, 'addSuppression'])->middleware('perm:blast.execute');
Route::post('/dns-check', [BlastController::class, 'dnsCheck'])->middleware('perm:blast.view');
