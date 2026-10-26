<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Booking\Http\Controllers\ServiceController;
use App\Modules\Booking\Http\Controllers\AppointmentController;
use App\Modules\Booking\Http\Controllers\SlotController;

// Services (Service Catalog)
Route::middleware('perm:booking.view')->group(function () {
    Route::get('booking/services', [ServiceController::class, 'index']);
    Route::get('booking/services/{id}', [ServiceController::class, 'show'])->whereNumber('id');
});

Route::middleware('perm:booking.manage')->group(function () {
    Route::post('booking/services', [ServiceController::class, 'store']);
    Route::put('booking/services/{id}', [ServiceController::class, 'update'])->whereNumber('id');
    Route::delete('booking/services/{id}', [ServiceController::class, 'destroy'])->whereNumber('id');
    Route::post('booking/services/{id}/toggle', [ServiceController::class, 'toggleActive'])->whereNumber('id');
});

// Slots
Route::middleware('perm:booking.view')->group(function () {
    Route::get('booking/slots', [SlotController::class, 'index']);
    Route::get('booking/slots/daterange', [SlotController::class, 'byDateRange']);
});

Route::middleware('perm:booking.manage')->group(function () {
    Route::post('booking/slots/generate', [SlotController::class, 'generate']);
    Route::post('booking/slots/{id}/book', [SlotController::class, 'book'])->whereNumber('id');
    Route::post('booking/slots/{id}/cancel', [SlotController::class, 'cancel'])->whereNumber('id');
    Route::post('booking/slots/{id}/block', [SlotController::class, 'block'])->whereNumber('id');
});

// Appointments
Route::middleware('perm:booking.view')->group(function () {
    Route::get('booking/appointments', [AppointmentController::class, 'index']);
    Route::get('booking/appointments/{id}', [AppointmentController::class, 'show'])->whereNumber('id');
    Route::get('booking/appointments/summary', [AppointmentController::class, 'summary']);
});

Route::middleware('perm:booking.manage')->group(function () {
    Route::post('booking/appointments', [AppointmentController::class, 'store']);
    Route::put('booking/appointments/{id}', [AppointmentController::class, 'update'])->whereNumber('id');
    Route::post('booking/appointments/{id}/confirm', [AppointmentController::class, 'confirm'])->whereNumber('id');
    Route::post('booking/appointments/{id}/start', [AppointmentController::class, 'start'])->whereNumber('id');
    Route::post('booking/appointments/{id}/complete', [AppointmentController::class, 'complete'])->whereNumber('id');
    Route::post('booking/appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->whereNumber('id');
    Route::post('booking/appointments/{id}/no-show', [AppointmentController::class, 'markNoShow'])->whereNumber('id');
    Route::post('booking/appointments/{id}/reschedule', [AppointmentController::class, 'reschedule'])->whereNumber('id');
});
