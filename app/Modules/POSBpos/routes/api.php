<?php

use App\Modules\POSBpos\Http\Controllers\POSBposController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| POSBpos Routes — ciri POS lanjutan daripada B-POS
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/pos-bpos
| Melengkapkan modul POS sedia ada. Semua laluan dilindungi perm + tenant.
|
*/

// Kad hadiah
Route::get('/gift-cards', [POSBposController::class, 'giftCards'])->middleware('perm:pos.view');
Route::post('/gift-cards', [POSBposController::class, 'storeGiftCard'])->middleware('perm:pos.manage');
Route::post('/gift-cards/redeem', [POSBposController::class, 'redeemGiftCard'])->middleware('perm:pos.manage');

// Tempahan meja
Route::get('/reservations', [POSBposController::class, 'reservations'])->middleware('perm:pos.view');
Route::post('/reservations', [POSBposController::class, 'storeReservation'])->middleware('perm:pos.manage');
Route::post('/reservations/{id}/confirm', [POSBposController::class, 'confirmReservation'])->middleware('perm:pos.manage');
Route::post('/reservations/{id}/cancel', [POSBposController::class, 'cancelReservation'])->middleware('perm:pos.manage');

// Kehadiran & syif staf
Route::get('/attendance', [POSBposController::class, 'attendance'])->middleware('perm:pos.view');
Route::post('/attendance/clock-in', [POSBposController::class, 'clockIn'])->middleware('perm:pos.manage');
Route::post('/attendance/clock-out', [POSBposController::class, 'clockOut'])->middleware('perm:pos.manage');
Route::get('/roster', [POSBposController::class, 'roster'])->middleware('perm:pos.view');
Route::post('/roster', [POSBposController::class, 'storeRoster'])->middleware('perm:pos.manage');

// Food court & giliran
Route::get('/food-courts', [POSBposController::class, 'foodCourts'])->middleware('perm:pos.view');
Route::post('/food-courts', [POSBposController::class, 'storeFoodCourt'])->middleware('perm:pos.manage');
Route::get('/food-courts/{id}/tables', [POSBposController::class, 'tables'])->middleware('perm:pos.view');
Route::post('/queue/next', [POSBposController::class, 'issueQueueNumber'])->middleware('perm:pos.manage');

// Sync offline
Route::get('/sync', [POSBposController::class, 'syncQueue'])->middleware('perm:pos.view');
Route::post('/sync', [POSBposController::class, 'pushSync'])->middleware('perm:pos.manage');
Route::post('/sync/{id}/synced', [POSBposController::class, 'markSynced'])->middleware('perm:pos.manage');
Route::post('/sync/{id}/failed', [POSBposController::class, 'markFailed'])->middleware('perm:pos.manage');

// Kedai online
Route::get('/online-stores', [POSBposController::class, 'onlineStores'])->middleware('perm:pos.view');
Route::post('/online-stores', [POSBposController::class, 'storeOnlineStore'])->middleware('perm:pos.manage');
Route::post('/online-stores/{id}/publish', [POSBposController::class, 'publishStore'])->middleware('perm:pos.manage');

// Bil cawangan & langganan
Route::get('/branch-invoices', [POSBposController::class, 'branchInvoices'])->middleware('perm:pos.view');
Route::post('/branch-invoices', [POSBposController::class, 'storeBranchInvoice'])->middleware('perm:pos.manage');
Route::get('/subscription-payments', [POSBposController::class, 'subscriptionPayments'])->middleware('perm:pos.view');
Route::post('/subscription-payments', [POSBposController::class, 'storeSubscriptionPayment'])->middleware('perm:pos.manage');
Route::post('/subscription-payments/{id}/confirm', [POSBposController::class, 'confirmSubscriptionPayment'])->middleware('perm:pos.manage');
