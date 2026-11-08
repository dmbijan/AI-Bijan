<?php

use App\Modules\OmniOrder\Http\Controllers\OmniOrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| OmniOrder Routes â€” pesanan omnichannel (asal: Luxana)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/omni-order
|
*/

// Pesanan
Route::get('/orders', [OmniOrderController::class, 'index'])->middleware('perm:omni_order.view');
Route::post('/orders', [OmniOrderController::class, 'store'])->middleware('perm:omni_order.manage');
Route::get('/orders/{id}', [OmniOrderController::class, 'show'])->middleware('perm:omni_order.view');

// Tindakan pukal & penghantaran
Route::post('/orders/bulk-approve', [OmniOrderController::class, 'bulkApprove'])->middleware('perm:omni_order.manage');
Route::post('/orders/{id}/awb', [OmniOrderController::class, 'printAwb'])->middleware('perm:omni_order.manage');
Route::post('/orders/{id}/push-courier', [OmniOrderController::class, 'pushCourier'])->middleware('perm:omni_order.execute');
Route::post('/orders/{id}/recover-delivery', [OmniOrderController::class, 'recoverDelivery'])->middleware('perm:omni_order.manage');

// Perlindungan hasil
Route::post('/orders/{id}/unified', [OmniOrderController::class, 'sendToUnifiedOrder'])->middleware('perm:omni_order.execute');
Route::get('/summary', [OmniOrderController::class, 'summary'])->middleware('perm:omni_order.view');
Route::get('/cod-payouts', [OmniOrderController::class, 'codPayouts'])->middleware('perm:omni_order.view');
Route::post('/cod-payouts/{id}/receive', [OmniOrderController::class, 'recordCodPayout'])->middleware('perm:omni_order.manage');

// Kurier & saluran
Route::get('/couriers', [OmniOrderController::class, 'couriers'])->middleware('perm:omni_order.view');
Route::get('/channels', [OmniOrderController::class, 'channels'])->middleware('perm:omni_order.view');
Route::post('/channels', [OmniOrderController::class, 'connectChannel'])->middleware('perm:omni_order.manage');
