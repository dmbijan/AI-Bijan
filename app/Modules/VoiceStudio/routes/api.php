<?php

use App\Modules\VoiceStudio\Http\Controllers\VoiceStudioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| VoiceStudio Routes
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/voice-studio
| inspired oleh https://github.com/debpalash/VoiceStudio
|
*/

// Profil suara
Route::get('/profiles', [VoiceStudioController::class, 'index'])->middleware('perm:voice_studio.view');
Route::post('/profiles', [VoiceStudioController::class, 'store'])->middleware('perm:voice_studio.manage');
Route::put('/profiles/{id}', [VoiceStudioController::class, 'update'])->middleware('perm:voice_studio.manage');
Route::delete('/profiles/{id}', [VoiceStudioController::class, 'destroy'])->middleware('perm:voice_studio.manage');

// Sintesis
Route::post('/synthesize', [VoiceStudioController::class, 'synthesize'])->middleware('perm:voice_studio.execute');
Route::get('/tracks', [VoiceStudioController::class, 'tracks'])->middleware('perm:voice_studio.view');
Route::get('/tracks/{id}', [VoiceStudioController::class, 'show'])->middleware('perm:voice_studio.view');
Route::get('/summary', [VoiceStudioController::class, 'ringkasan'])->middleware('perm:voice_studio.view');
