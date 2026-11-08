<?php

use App\Modules\ScreenRecorder\Http\Controllers\ScreenRecorderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ScreenRecorder Routes
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/screen-recorder
| inspired oleh https://github.com/CapSoftware/Cap
|
*/

// Rakaman
Route::get('/recordings', [ScreenRecorderController::class, 'index'])->middleware('perm:screen_recorder.view');
Route::post('/recordings', [ScreenRecorderController::class, 'store'])->middleware('perm:screen_recorder.manage');
Route::get('/recordings/{id}', [ScreenRecorderController::class, 'show'])->middleware('perm:screen_recorder.view');
Route::put('/recordings/{id}', [ScreenRecorderController::class, 'update'])->middleware('perm:screen_recorder.manage');
Route::delete('/recordings/{id}', [ScreenRecorderController::class, 'destroy'])->middleware('perm:screen_recorder.manage');

// Perkongsian
Route::get('/recordings/{id}/share', [ScreenRecorderController::class, 'share'])->middleware('perm:screen_recorder.view');
Route::post('/recordings/{id}/share/regenerate', [ScreenRecorderController::class, 'regenerateToken'])->middleware('perm:screen_recorder.manage');

// Komen
Route::post('/recordings/{id}/open-in-media-editor', [ScreenRecorderController::class, 'openInMediaEditor'])->middleware('perm:screen_recorder.manage');
Route::get('/recordings/{id}/comments', [ScreenRecorderController::class, 'comments'])->middleware('perm:screen_recorder.view');
Route::post('/recordings/{id}/comments', [ScreenRecorderController::class, 'storeComment'])->middleware('perm:screen_recorder.execute');
