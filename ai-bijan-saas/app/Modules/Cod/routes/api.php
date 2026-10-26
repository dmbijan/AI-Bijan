<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Cod\Http\Controllers\CodController;

/*
|--------------------------------------------------------------------------
| COD Routes (BAB 84 / Idea #6)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/cod
| Cash on Delivery — jurang Malaysia tanpa pesaing
|
*/

// Semakan (tanpa simpan)
Route::post('/check', [CodController::class, 'semakKelayakan'])->middleware('perm:cod.view');
Route::post('/check-address', [CodController::class, 'semakAlamat'])->middleware('perm:cod.view');

// Pesanan COD
Route::get('/orders', [CodController::class, 'index'])->middleware('perm:cod.view');
Route::post('/orders', [CodController::class, 'store'])->middleware('perm:cod.manage');
Route::get('/orders/{id}', [CodController::class, 'show'])->middleware('perm:cod.view');
Route::post('/orders/{id}/status', [CodController::class, 'tukarStatus'])->middleware('perm:cod.execute');

// Tetapan
Route::get('/settings', [CodController::class, 'tetapan'])->middleware('perm:cod.view');
Route::put('/settings', [CodController::class, 'kemasKiniTetapan'])->middleware('perm:cod.manage');

// Analitik & reconciliation
Route::get('/analysis', [CodController::class, 'analisis'])->middleware('perm:cod.reports');
Route::get('/reconcile', [CodController::class, 'reconcilesi'])->middleware('perm:cod.reports');
Route::post('/collections', [CodController::class, 'rekodKutipan'])->middleware('perm:cod.manage');
