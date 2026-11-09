<?php

use App\Modules\BLive\Http\Controllers\AvatarController;
use App\Modules\BLive\Http\Controllers\HostPersonalityController;
use App\Modules\BLive\Http\Controllers\LiveSessionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| B-Live AI Routes
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/blive
|
| B-Live AI - AI Human Live Host Platform.
|
*/

// ---------------------------------------------------------------------
// Host personalities
// ---------------------------------------------------------------------
Route::get('/personalities', [HostPersonalityController::class, 'index'])->middleware('perm:blive.view');
Route::post('/personalities', [HostPersonalityController::class, 'store'])->middleware('perm:blive.manage');
Route::get('/personalities/{id}', [HostPersonalityController::class, 'show'])->middleware('perm:blive.view');
Route::put('/personalities/{id}', [HostPersonalityController::class, 'update'])->middleware('perm:blive.manage');
Route::delete('/personalities/{id}', [HostPersonalityController::class, 'destroy'])->middleware('perm:blive.manage');

// ---------------------------------------------------------------------
// Avatars
// ---------------------------------------------------------------------
Route::get('/avatars', [AvatarController::class, 'index'])->middleware('perm:blive.view');
Route::post('/avatars', [AvatarController::class, 'store'])->middleware('perm:blive.manage');
Route::get('/avatars/{id}', [AvatarController::class, 'show'])->middleware('perm:blive.view');
Route::put('/avatars/{id}', [AvatarController::class, 'update'])->middleware('perm:blive.manage');
Route::delete('/avatars/{id}', [AvatarController::class, 'destroy'])->middleware('perm:blive.manage');
Route::post('/avatars/{id}/generate', [AvatarController::class, 'generate'])->middleware('perm:blive.execute');

// ---------------------------------------------------------------------
// Consent records
// ---------------------------------------------------------------------
Route::get('/consents', [AvatarController::class, 'consents'])->middleware('perm:blive.view');
Route::post('/consents/{id}/grant', [AvatarController::class, 'grantConsent'])->middleware('perm:blive.manage');
Route::post('/consents/{id}/revoke', [AvatarController::class, 'revokeConsent'])->middleware('perm:blive.manage');

// ---------------------------------------------------------------------
// Live sessions
// ---------------------------------------------------------------------
Route::get('/live-sessions', [LiveSessionController::class, 'index'])->middleware('perm:blive.view');
Route::post('/live-sessions', [LiveSessionController::class, 'store'])->middleware('perm:blive.manage');
Route::get('/live-sessions/{id}', [LiveSessionController::class, 'show'])->middleware('perm:blive.view');
Route::post('/live-sessions/{id}/start', [LiveSessionController::class, 'start'])->middleware('perm:blive.execute');
Route::post('/live-sessions/{id}/stop', [LiveSessionController::class, 'stop'])->middleware('perm:blive.execute');
Route::post('/live-sessions/{id}/takeover', [LiveSessionController::class, 'takeover'])->middleware('perm:blive.execute');
Route::post('/live-sessions/{id}/release', [LiveSessionController::class, 'release'])->middleware('perm:blive.execute');
Route::post('/live-sessions/{id}/pause-ai', [LiveSessionController::class, 'pauseAi'])->middleware('perm:blive.execute');
Route::post('/live-sessions/{id}/resume-ai', [LiveSessionController::class, 'resumeAi'])->middleware('perm:blive.execute');
Route::post('/live-sessions/{id}/speak', [LiveSessionController::class, 'speak'])->middleware('perm:blive.execute');
Route::post('/live-sessions/{id}/emergency-stop', [LiveSessionController::class, 'emergencyStop'])->middleware('perm:blive.execute');
Route::post('/live-sessions/{id}/comments', [LiveSessionController::class, 'comment'])->middleware('perm:blive.execute');
Route::get('/live-sessions/{id}/messages', [LiveSessionController::class, 'messages'])->middleware('perm:blive.view');
Route::get('/live-sessions/{id}/metrics', [LiveSessionController::class, 'metrics'])->middleware('perm:blive.view');

// ---------------------------------------------------------------------
// Streaming destinations
// ---------------------------------------------------------------------
Route::get('/live-sessions/{id}/channels', [LiveSessionController::class, 'channels'])->middleware('perm:blive.view');
Route::post('/live-sessions/{id}/channels', [LiveSessionController::class, 'connectChannel'])->middleware('perm:blive.manage');
Route::post('/live-sessions/{id}/channels/{destinationId}/start', [LiveSessionController::class, 'startChannel'])->middleware('perm:blive.execute');
