<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Orders\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| Unified Order Management Routes (BAB 86 / Idea #19)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/orders
| SATU tempat untuk semua pesanan — marketplace, POS, site, booking, COD.
|
*/

// Pesanan
Route::get('/', [OrderController::class, 'index'])->middleware('perm:orders.view');
Route::post('/', [OrderController::class, 'store'])->middleware('perm:orders.manage');
Route::get('/summary', [OrderController::class, 'ringkasan'])->middleware('perm:orders.view');
Route::get('/needs-action', [OrderController::class, 'perluTindakan'])->middleware('perm:orders.view');

// Pesanan tunggal
Route::get('/{id}', [OrderController::class, 'show'])->middleware('perm:orders.view');
Route::post('/{id}/status', [OrderController::class, 'tukarStatus'])->middleware('perm:orders.execute');
Route::post('/{id}/payment', [OrderController::class, 'rekodBayaran'])->middleware('perm:orders.execute');
Route::get('/{id}/timeline', [OrderController::class, 'timeline'])->middleware('perm:orders.view');

// Saluran & sinkronisasi
Route::get('/channels/list', [OrderController::class, 'channels'])->middleware('perm:orders.view');
Route::post('/channels', [OrderController::class, 'simpanChannel'])->middleware('perm:orders.manage');
Route::get('/sync/logs', [OrderController::class, 'syncLogs'])->middleware('perm:orders.view');
Route::post('/sync', [OrderController::class, 'sync'])->middleware('perm:orders.execute');
