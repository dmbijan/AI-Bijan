<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Fleet\Http\Controllers\FleetController;

/*
|--------------------------------------------------------------------------
| Fleet & GPS Routes (BAB 84 / Idea #10)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/fleet
| Pengurusan armada + GPS — corak ServiceTitan "Fleet Pro"
|
*/

// Kenderaan
Route::get('/vehicles', [FleetController::class, 'vehicles'])->middleware('perm:fleet.view');
Route::post('/vehicles', [FleetController::class, 'storeVehicle'])->middleware('perm:fleet.manage');
Route::put('/vehicles/{id}', [FleetController::class, 'updateVehicle'])->middleware('perm:fleet.manage');
Route::post('/vehicles/{id}/location', [FleetController::class, 'rekodLokasi'])->middleware('perm:fleet.execute');

// Peta & perjalanan
Route::get('/map', [FleetController::class, 'peta'])->middleware('perm:fleet.view');
Route::post('/vehicles/{id}/trips', [FleetController::class, 'mulaPerjalanan'])->middleware('perm:fleet.execute');
Route::post('/trips/{tripId}/complete', [FleetController::class, 'tamatPerjalanan'])->middleware('perm:fleet.execute');

// Pemandu
Route::get('/drivers', [FleetController::class, 'drivers'])->middleware('perm:fleet.view');
Route::post('/drivers', [FleetController::class, 'storeDriver'])->middleware('perm:fleet.manage');
Route::get('/drivers/{id}/safety-score', [FleetController::class, 'skorKeselamatan'])->middleware('perm:fleet.view');

// Amaran & statistik
Route::get('/alerts', [FleetController::class, 'alerts'])->middleware('perm:fleet.view');
Route::post('/alerts/{id}/resolve', [FleetController::class, 'resolveAlert'])->middleware('perm:fleet.execute');
Route::get('/statistics', [FleetController::class, 'statistik'])->middleware('perm:fleet.reports');
