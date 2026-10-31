<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/app'));

// Observability (BAB 61) — tiada auth, tiada data sensitif
Route::get('/health', [HealthController::class, 'health']);
Route::get('/ready', [HealthController::class, 'ready']);
Route::get('/metrics', [HealthController::class, 'metrics'])->middleware('throttle:30,1');

Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register');

// Menu meja QR untuk pelanggan (awam)
Route::view('/meja/{token}', 'pos.menu-meja')->where('token', '[a-z0-9]{24}');

Route::prefix('app')->group(function () {
    Route::get('/', [AppController::class, 'dashboard'])->name('app.dashboard');
    Route::get('/{module}', [AppController::class, 'module'])->name('app.module');
});

