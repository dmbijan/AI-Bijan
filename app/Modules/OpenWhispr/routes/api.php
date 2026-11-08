<?php

use App\Modules\OpenWhispr\Http\Controllers\OpenWhisprController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| OpenWhispr Routes
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/open-whispr
| inspired oleh https://github.com/OpenWhispr/openwhispr
|
*/

// Sesi diktasi
Route::get('/sessions', [OpenWhisprController::class, 'index'])->middleware('perm:open_whispr.view');
Route::post('/transcribe', [OpenWhisprController::class, 'transcribe'])->middleware('perm:open_whispr.execute');
Route::post('/sessions/{id}/narration', [OpenWhisprController::class, 'generateNarration'])->middleware('perm:open_whispr.execute');
Route::get('/sessions/{id}', [OpenWhisprController::class, 'show'])->middleware('perm:open_whispr.view');
Route::delete('/sessions/{id}', [OpenWhisprController::class, 'destroy'])->middleware('perm:open_whispr.manage');

// Kamus perniagaan
Route::get('/vocabulary', [OpenWhisprController::class, 'vocabulary'])->middleware('perm:open_whispr.view');
Route::post('/vocabulary', [OpenWhisprController::class, 'storeVocabulary'])->middleware('perm:open_whispr.manage');
Route::put('/vocabulary/{id}', [OpenWhisprController::class, 'updateVocabulary'])->middleware('perm:open_whispr.manage');

// Tetapan & ringkasan
Route::get('/settings', [OpenWhisprController::class, 'tetapan'])->middleware('perm:open_whispr.view');
Route::get('/summary', [OpenWhisprController::class, 'ringkasan'])->middleware('perm:open_whispr.view');
