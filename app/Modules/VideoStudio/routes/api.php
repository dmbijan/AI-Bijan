<?php

use App\Modules\VideoStudio\Http\Controllers\VideoStudioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| VideoStudio Routes
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/video-studio
| Orkestrasi skill video AI BIJAN (mograph / motion / video-editor).
|
*/

// Projek
Route::get('/projects', [VideoStudioController::class, 'index'])->middleware('perm:video_studio.view');
Route::post('/projects', [VideoStudioController::class, 'store'])->middleware('perm:video_studio.manage');
Route::get('/projects/{id}', [VideoStudioController::class, 'show'])->middleware('perm:video_studio.view');
Route::put('/projects/{id}', [VideoStudioController::class, 'update'])->middleware('perm:video_studio.manage');
Route::delete('/projects/{id}', [VideoStudioController::class, 'destroy'])->middleware('perm:video_studio.manage');

// Render
Route::post('/preflight', [VideoStudioController::class, 'preflight'])->middleware('perm:video_studio.view');
Route::post('/projects/{id}/render', [VideoStudioController::class, 'render'])->middleware('perm:video_studio.execute');
Route::post('/projects/{id}/progress', [VideoStudioController::class, 'progress'])->middleware('perm:video_studio.execute');
Route::get('/projects/{id}/instructions', [VideoStudioController::class, 'instructions'])->middleware('perm:video_studio.view');

// Ringkasan
Route::get('/summary', [VideoStudioController::class, 'ringkasan'])->middleware('perm:video_studio.view');

// Brand kit
Route::get('/brand-kits', [VideoStudioController::class, 'brandKits'])->middleware('perm:video_studio.view');
Route::post('/brand-kits', [VideoStudioController::class, 'storeBrandKit'])->middleware('perm:video_studio.manage');
Route::put('/brand-kits/{id}', [VideoStudioController::class, 'updateBrandKit'])->middleware('perm:video_studio.manage');
Route::delete('/brand-kits/{id}', [VideoStudioController::class, 'destroyBrandKit'])->middleware('perm:video_studio.manage');
Route::get('/brand-kits/{id}/json', [VideoStudioController::class, 'brandKitJson'])->middleware('perm:video_studio.view');
