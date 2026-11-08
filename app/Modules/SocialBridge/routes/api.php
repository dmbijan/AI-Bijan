<?php

use App\Modules\SocialBridge\Http\Controllers\SocialBridgeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SocialBridge Routes — siaran silang sosial (asal: Post Bridge)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/social-bridge
|
*/

// Akaun
Route::get('/accounts', [SocialBridgeController::class, 'accounts'])->middleware('perm:social_bridge.view');
Route::post('/accounts', [SocialBridgeController::class, 'connectAccount'])->middleware('perm:social_bridge.manage');
Route::delete('/accounts/{id}', [SocialBridgeController::class, 'disconnectAccount'])->middleware('perm:social_bridge.manage');

// Catatan
Route::get('/posts', [SocialBridgeController::class, 'index'])->middleware('perm:social_bridge.view');
Route::post('/posts', [SocialBridgeController::class, 'store'])->middleware('perm:social_bridge.manage');
Route::get('/posts/{id}', [SocialBridgeController::class, 'show'])->middleware('perm:social_bridge.view');
Route::post('/posts/{id}/schedule', [SocialBridgeController::class, 'schedule'])->middleware('perm:social_bridge.manage');
Route::post('/posts/{id}/publish', [SocialBridgeController::class, 'publish'])->middleware('perm:social_bridge.execute');

// Ringkasan
Route::get('/summary', [SocialBridgeController::class, 'summary'])->middleware('perm:social_bridge.view');
