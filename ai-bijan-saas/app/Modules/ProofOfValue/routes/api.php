<?php

use Illuminate\Support\Facades\Route;
use App\Modules\ProofOfValue\Http\Controllers\ProofOfValueController;

/*
|--------------------------------------------------------------------------
| Proof of Value Routes (BAB 84 / Idea #5)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/proof-of-value
| Metrik hasil per modul — corak ServiceTitan Pro Products
|
*/

// Katalog & metrik
Route::get('/metrics', [ProofOfValueController::class, 'index'])->middleware('perm:proof_of_value.view');
Route::post('/metrics', [ProofOfValueController::class, 'store'])->middleware('perm:proof_of_value.manage');
Route::post('/catalog/generate', [ProofOfValueController::class, 'janaKatalog'])->middleware('perm:proof_of_value.manage');

// Bacaan & baseline
Route::post('/metrics/{id}/reading', [ProofOfValueController::class, 'rekodBacaan'])->middleware('perm:proof_of_value.manage');
Route::post('/metrics/{id}/baseline', [ProofOfValueController::class, 'rekodBaseline'])->middleware('perm:proof_of_value.manage');
Route::post('/metrics/{id}/verify', [ProofOfValueController::class, 'sahkan'])->middleware('perm:proof_of_value.approve');

// Ringkasan & eksport
Route::get('/summary', [ProofOfValueController::class, 'ringkasan'])->middleware('perm:proof_of_value.view');
Route::get('/exports', [ProofOfValueController::class, 'exports'])->middleware('perm:proof_of_value.view');
Route::post('/exports', [ProofOfValueController::class, 'janaEksport'])->middleware('perm:proof_of_value.manage');
