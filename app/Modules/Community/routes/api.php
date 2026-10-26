<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Community\Http\Controllers\CommunityController;

/*
|--------------------------------------------------------------------------
| Community Routes (BAB 86 / Idea #29)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/community
| Komuniti AI BIJAN — forum, showcase, academy, help.
|
*/

// Statik dahulu
Route::get('/spaces', [CommunityController::class, 'spaces'])->middleware('perm:community.view');
Route::post('/spaces', [CommunityController::class, 'createSpace'])->middleware('perm:community.manage');
Route::get('/feed', [CommunityController::class, 'feed'])->middleware('perm:community.view');
Route::post('/posts', [CommunityController::class, 'createPost'])->middleware('perm:community.manage');
Route::get('/summary', [CommunityController::class, 'ringkasan'])->middleware('perm:community.view');

// Pos
Route::get('/posts/{id}', [CommunityController::class, 'showPost'])->middleware('perm:community.view');
Route::post('/posts/{id}/reply', [CommunityController::class, 'reply'])->middleware('perm:community.manage');
Route::post('/posts/{postId}/replies/{replyId}/accept', [CommunityController::class, 'acceptReply'])->middleware('perm:community.manage');
