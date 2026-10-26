<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Booking\Http\Controllers\ServiceController;
use App\Modules\Booking\Http\Controllers\AppointmentController;
use App\Modules\Booking\Http\Controllers\SlotController;

// Services (Service Catalog)
Route::middleware('perm:booking.view')->group(function () {
    Route::get('services', [ServiceController::class, 'index']);
    Route::get('services/{id}', [ServiceController::class, 'show'])->whereNumber('id');
});

Route::middleware('perm:booking.manage')->group(function () {
    Route::post('services', [ServiceController::class, 'store']);
    Route::put('services/{id}', [ServiceController::class, 'update'])->whereNumber('id');
    Route::delete('services/{id}', [ServiceController::class, 'destroy'])->whereNumber('id');
    Route::post('services/{id}/toggle', [ServiceController::class, 'toggleActive'])->whereNumber('id');
});

// Slots
Route::middleware('perm:booking.view')->group(function () {
    Route::get('slots', [SlotController::class, 'index']);
    Route::get('slots/daterange', [SlotController::class, 'byDateRange']);
});

Route::middleware('perm:booking.manage')->group(function () {
    Route::post('slots/generate', [SlotController::class, 'generate']);
    Route::post('slots/{id}/book', [SlotController::class, 'book'])->whereNumber('id');
    Route::post('slots/{id}/cancel', [SlotController::class, 'cancel'])->whereNumber('id');
    Route::post('slots/{id}/block', [SlotController::class, 'block'])->whereNumber('id');
});

// Appointments
Route::middleware('perm:booking.view')->group(function () {
    Route::get('appointments', [AppointmentController::class, 'index']);
    Route::get('appointments/{id}', [AppointmentController::class, 'show'])->whereNumber('id');
    Route::get('appointments/summary', [AppointmentController::class, 'summary']);
});

Route::middleware('perm:booking.manage')->group(function () {
    Route::post('appointments', [AppointmentController::class, 'store']);
    Route::put('appointments/{id}', [AppointmentController::class, 'update'])->whereNumber('id');
    Route::post('appointments/{id}/confirm', [AppointmentController::class, 'confirm'])->whereNumber('id');
    Route::post('appointments/{id}/start', [AppointmentController::class, 'start'])->whereNumber('id');
    Route::post('appointments/{id}/complete', [AppointmentController::class, 'complete'])->whereNumber('id');
    Route::post('appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->whereNumber('id');
    Route::post('appointments/{id}/no-show', [AppointmentController::class, 'markNoShow'])->whereNumber('id');
    Route::post('appointments/{id}/reschedule', [AppointmentController::class, 'reschedule'])->whereNumber('id');
});
