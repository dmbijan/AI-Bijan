<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Reminder\Http\Controllers\ReminderController;

/*
|--------------------------------------------------------------------------
| Reminder & Notification Center Routes (BAB 86 / Idea #22)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/reminders
| Pusat peringatan merentas semua modul + kotak masuk AI.
|
*/

// Katalog jenis
Route::get('/types', [ReminderController::class, 'jenis'])->middleware('perm:reminder.view');
Route::post('/types/generate', [ReminderController::class, 'janaKatalog'])->middleware('perm:reminder.manage');
Route::post('/types', [ReminderController::class, 'simpanJenis'])->middleware('perm:reminder.manage');

// Kotak masuk AI
Route::get('/inbox/list', [ReminderController::class, 'kotakMasuk'])->middleware('perm:reminder.view');
Route::post('/inbox/{id}/read', [ReminderController::class, 'tandakanDibaca'])->middleware('perm:reminder.execute');

// Jadwal berulang
Route::get('/schedules/list', [ReminderController::class, 'jadual'])->middleware('perm:reminder.view');
Route::post('/schedules', [ReminderController::class, 'simpanJadual'])->middleware('perm:reminder.manage');
Route::post('/schedules/run', [ReminderController::class, 'jalankanJadual'])->middleware('perm:reminder.execute');

// Ringkasan & eskalasi
Route::get('/summary', [ReminderController::class, 'ringkasan'])->middleware('perm:reminder.view');
Route::post('/escalate', [ReminderController::class, 'eskalasi'])->middleware('perm:reminder.execute');

// Peringatan — senarai & cipta
Route::get('/', [ReminderController::class, 'index'])->middleware('perm:reminder.view');
Route::post('/', [ReminderController::class, 'store'])->middleware('perm:reminder.manage');

// Laluan dinamik SELEPAS laluan statik di atas
Route::get('/{id}', [ReminderController::class, 'show'])->middleware('perm:reminder.view');
Route::post('/{id}/send', [ReminderController::class, 'hantar'])->middleware('perm:reminder.execute');
Route::post('/{id}/snooze', [ReminderController::class, 'tunda'])->middleware('perm:reminder.execute');
Route::post('/{id}/acknowledge', [ReminderController::class, 'sahkan'])->middleware('perm:reminder.execute');
Route::post('/{id}/complete', [ReminderController::class, 'lengkapkan'])->middleware('perm:reminder.execute');
Route::post('/{id}/dismiss', [ReminderController::class, 'buang'])->middleware('perm:reminder.execute');
