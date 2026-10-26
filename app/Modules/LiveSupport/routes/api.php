<?php

use Illuminate\Support\Facades\Route;
use App\Modules\LiveSupport\Http\Controllers\LiveSupportController;

/*
|--------------------------------------------------------------------------
| Live Support Routes (BAB 86 / Idea #28)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/live-support
| Customer service live diuruskan AI, dengan eskalasi manusia + eSIM.
|
*/

// Saluran
Route::get('/channels', [LiveSupportController::class, 'channels'])->middleware('perm:live_support.view');
Route::post('/channels', [LiveSupportController::class, 'simpanChannel'])->middleware('perm:live_support.manage');

// Panggilan
Route::get('/calls', [LiveSupportController::class, 'calls'])->middleware('perm:live_support.view');
Route::post('/calls', [LiveSupportController::class, 'terimaPanggilan'])->middleware('perm:live_support.execute');
Route::get('/calls/{id}', [LiveSupportController::class, 'butiran'])->middleware('perm:live_support.view');
Route::post('/calls/{id}/transcript', [LiveSupportController::class, 'tambahTranskrip'])->middleware('perm:live_support.execute');
Route::post('/calls/{id}/escalate', [LiveSupportController::class, 'eskalasi'])->middleware('perm:live_support.execute');
Route::post('/calls/{id}/end', [LiveSupportController::class, 'tamat'])->middleware('perm:live_support.execute');

// Ringkasan
Route::get('/summary', [LiveSupportController::class, 'ringkasan'])->middleware('perm:live_support.view');
