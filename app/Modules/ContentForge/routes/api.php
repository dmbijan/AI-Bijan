<?php

use App\Modules\ContentForge\Http\Controllers\ContentForgeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ContentForge Routes — kandungan & SEO (asal: SEO Writing AI)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/content-forge
|
*/

// Dokumen
Route::get('/documents', [ContentForgeController::class, 'index'])->middleware('perm:content_forge.view');
Route::post('/documents', [ContentForgeController::class, 'store'])->middleware('perm:content_forge.manage');
Route::get('/documents/{id}', [ContentForgeController::class, 'show'])->middleware('perm:content_forge.view');
Route::put('/documents/{id}', [ContentForgeController::class, 'update'])->middleware('perm:content_forge.manage');

// SEO & penjanaan
Route::post('/documents/{id}/score', [ContentForgeController::class, 'scoreSeo'])->middleware('perm:content_forge.execute');
Route::post('/serp', [ContentForgeController::class, 'analyzeSerp'])->middleware('perm:content_forge.execute');
Route::post('/outline', [ContentForgeController::class, 'outline'])->middleware('perm:content_forge.execute');

// Terbit
Route::post('/documents/{id}/publish', [ContentForgeController::class, 'publish'])->middleware('perm:content_forge.execute');
Route::get('/sites', [ContentForgeController::class, 'sites'])->middleware('perm:content_forge.view');
Route::post('/sites', [ContentForgeController::class, 'storeSite'])->middleware('perm:content_forge.manage');

// Ringkasan
Route::get('/summary', [ContentForgeController::class, 'summary'])->middleware('perm:content_forge.view');
