<?php

use Illuminate\Support\Facades\Route;
use App\Modules\ContentStudio\Http\Controllers\ContentStudioController;

/*
|--------------------------------------------------------------------------
| Content Studio Routes (BAB 86 / Idea #21)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/content
| Jana kandungan AI → jadual → terbit ke semua platform, jaga komen & chat.
|
*/

// Platform
Route::get('/platforms', [ContentStudioController::class, 'platforms'])->middleware('perm:content.view');
Route::post('/platforms', [ContentStudioController::class, 'simpanPlatform'])->middleware('perm:content.manage');

// Brand voice
Route::get('/brand-profiles', [ContentStudioController::class, 'brandProfiles'])->middleware('perm:content.view');
Route::post('/brand-profiles', [ContentStudioController::class, 'simpanBrandProfile'])->middleware('perm:content.manage');

// Kandungan
Route::get('/pieces', [ContentStudioController::class, 'index'])->middleware('perm:content.view');
Route::post('/pieces', [ContentStudioController::class, 'simpan'])->middleware('perm:content.manage');
Route::post('/generate', [ContentStudioController::class, 'jana'])->middleware('perm:content.execute');
Route::post('/pieces/{id}/schedule', [ContentStudioController::class, 'jadual'])->middleware('perm:content.manage');
Route::get('/pieces/{id}/preview', [ContentStudioController::class, 'preview'])->middleware('perm:content.view');
Route::post('/publish', [ContentStudioController::class, 'terbitkan'])->middleware('perm:content.execute');

// Komen
Route::get('/comments', [ContentStudioController::class, 'komen'])->middleware('perm:content.view');
Route::post('/comments/ingest', [ContentStudioController::class, 'ingestKomen'])->middleware('perm:content.manage');
Route::post('/comments/{id}/suggest-reply', [ContentStudioController::class, 'janaBalasan'])->middleware('perm:content.execute');
Route::post('/comments/{id}/reply', [ContentStudioController::class, 'balasKomen'])->middleware('perm:content.execute');
Route::post('/comments/{id}/hide', [ContentStudioController::class, 'sembunyiKomen'])->middleware('perm:content.execute');

// Chat
Route::get('/chats', [ContentStudioController::class, 'chats'])->middleware('perm:content.view');
Route::post('/chats/ingest', [ContentStudioController::class, 'ingestChat'])->middleware('perm:content.manage');
Route::post('/chats/{id}/reply', [ContentStudioController::class, 'balasChat'])->middleware('perm:content.execute');

// Moderation
Route::get('/moderation-rules', [ContentStudioController::class, 'moderationRules'])->middleware('perm:content.view');
Route::post('/moderation-rules', [ContentStudioController::class, 'simpanModerationRule'])->middleware('perm:content.manage');

// Idea bank
Route::get('/ideas', [ContentStudioController::class, 'idea'])->middleware('perm:content.view');
Route::post('/ideas', [ContentStudioController::class, 'simpanIdea'])->middleware('perm:content.manage');

// Ringkasan
Route::get('/summary', [ContentStudioController::class, 'ringkasan'])->middleware('perm:content.view');
Route::get('/performance', [ContentStudioController::class, 'prestasi'])->middleware('perm:content.view');
