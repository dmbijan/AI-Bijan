<?php

use App\Modules\OpenSEO\Http\Controllers\OpenSeoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| OpenSEO Routes
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/open-seo
| inspired oleh https://github.com/every-app/open-seo
|
*/

// Projek
Route::get('/projects', [OpenSeoController::class, 'index'])->middleware('perm:open_seo.view');
Route::post('/projects', [OpenSeoController::class, 'store'])->middleware('perm:open_seo.manage');
Route::put('/projects/{id}', [OpenSeoController::class, 'update'])->middleware('perm:open_seo.manage');
Route::delete('/projects/{id}', [OpenSeoController::class, 'destroy'])->middleware('perm:open_seo.manage');
Route::get('/projects/{id}/summary', [OpenSeoController::class, 'ringkasan'])->middleware('perm:open_seo.view');

// SERP
Route::get('/projects/{id}/serp', [OpenSeoController::class, 'serp'])->middleware('perm:open_seo.view');
Route::post('/projects/{id}/serp', [OpenSeoController::class, 'rekodSerp'])->middleware('perm:open_seo.execute');

// Kata kunci
Route::get('/projects/{id}/keywords', [OpenSeoController::class, 'kataKunci'])->middleware('perm:open_seo.view');
Route::post('/projects/{id}/keywords', [OpenSeoController::class, 'simpanKataKunci'])->middleware('perm:open_seo.manage');
Route::get('/projects/{id}/gap', [OpenSeoController::class, 'analisisCelah'])->middleware('perm:open_seo.view');

// Backlink
Route::get('/projects/{id}/backlinks', [OpenSeoController::class, 'backlink'])->middleware('perm:open_seo.view');
Route::post('/projects/{id}/backlinks', [OpenSeoController::class, 'simpanBacklink'])->middleware('perm:open_seo.manage');

// Audit teknikal
Route::post('/audit', [OpenSeoController::class, 'audit'])->middleware('perm:open_seo.execute');
