<?php

use App\Modules\Scheduling\Http\Controllers\SchedulingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Scheduling Routes
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/scheduling
| inspired oleh https://github.com/calcom/cal.diy
|
*/

// Pautan penjadualan
Route::get('/links', [SchedulingController::class, 'index'])->middleware('perm:scheduling.view');
Route::post('/links', [SchedulingController::class, 'store'])->middleware('perm:scheduling.manage');
Route::put('/links/{id}', [SchedulingController::class, 'update'])->middleware('perm:scheduling.manage');
Route::delete('/links/{id}', [SchedulingController::class, 'destroy'])->middleware('perm:scheduling.manage');

// Slot & tempahan
Route::get('/links/{id}/slots', [SchedulingController::class, 'slots'])->middleware('perm:scheduling.view');
Route::post('/links/{id}/book', [SchedulingController::class, 'book'])->middleware('perm:scheduling.execute');

// Jadual pasukan
Route::get('/team-calendar', [SchedulingController::class, 'teamCalendar'])->middleware('perm:scheduling.view');
