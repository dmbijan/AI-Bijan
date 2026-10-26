<?php

use Illuminate\Support\Facades\Route;
use App\Modules\MediaStudio\Http\Controllers\MediaStudioController;

/*
|--------------------------------------------------------------------------
| Media Studio Routes (BAB 82 / Fasa 4)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/media
| Middleware: api, auth:sanctum, tenant (ditetapkan dalam ServiceProvider)
|
*/

// Aset
Route::get('/', [MediaStudioController::class, 'index'])->middleware('perm:media.view');
Route::post('/upload', [MediaStudioController::class, 'upload'])->middleware('perm:media.execute');
Route::get('/{id}', [MediaStudioController::class, 'show'])->middleware('perm:media.view');

// Pemahaman video
Route::post('/{id}/understand', [MediaStudioController::class, 'understand'])->middleware('perm:media.execute');
Route::post('/{id}/qa', [MediaStudioController::class, 'qa'])->middleware('perm:media.view');

// Penjanaan video
Route::post('/generate-video', [MediaStudioController::class, 'generateVideo'])->middleware('perm:media.execute');
Route::get('/jobs/{id}', [MediaStudioController::class, 'job'])->middleware('perm:media.view');
Route::post('/jobs/{id}/approve', [MediaStudioController::class, 'approveJob'])->middleware('perm:media.approve');

// Persona video
Route::get('/personas', [MediaStudioController::class, 'personas'])->middleware('perm:media.view');
Route::post('/personas', [MediaStudioController::class, 'storePersona'])->middleware('perm:media.execute');
Route::post('/personas/{id}/verify', [MediaStudioController::class, 'verifyPersona'])->middleware('perm:media.approve');

// Tangkapan skrin
Route::post('/screen-capture', [MediaStudioController::class, 'screenCapture'])->middleware('perm:media.execute');
