<?php

use App\Modules\ServiceOS\Http\Controllers\ServiceOSController;
use Illuminate\Support\Facades\Route;

/**
 * Laluan API B-ServiceOS (BAB 87 P1).
 *
 * ⚠️ SEMUA laluan statik DIDAHULUKAN sebelum `/{id}` — jika tidak,
 * `summary` / `industries` / `templates` akan ditangkap oleh `/{id}`.
 */
Route::prefix('service-os')->middleware(['auth:sanctum'])->group(function () {
    Route::get('summary', [ServiceOSController::class, 'summary'])
        ->middleware('perm:service_os.view');

    Route::get('industries', [ServiceOSController::class, 'industries'])
        ->middleware('perm:service_os.view');

    Route::get('checklist-template', [ServiceOSController::class, 'checklistTemplate'])
        ->middleware('perm:service_os.view');

    Route::get('package-catalogue', [ServiceOSController::class, 'packageCatalogue'])
        ->middleware('perm:service_os.view');

    Route::get('overdue-tasks', [ServiceOSController::class, 'overdueTasks'])
        ->middleware('perm:service_os.view');

    // ---- PELANGGAN ----
    Route::get('clients', [ServiceOSController::class, 'clients'])
        ->middleware('perm:service_os.view');
    Route::post('clients', [ServiceOSController::class, 'createClient'])
        ->middleware('perm:service_os.manage');
    Route::get('clients/{id}', [ServiceOSController::class, 'showClient'])
        ->middleware('perm:service_os.view')->whereNumber('id');

    // ---- PAKEJ ----
    Route::get('packages', [ServiceOSController::class, 'packages'])
        ->middleware('perm:service_os.view');
    Route::post('packages', [ServiceOSController::class, 'createPackage'])
        ->middleware('perm:service_os.manage');

    // ---- PROJEK ----
    Route::get('projects', [ServiceOSController::class, 'projects'])
        ->middleware('perm:service_os.view');
    Route::post('projects', [ServiceOSController::class, 'createProject'])
        ->middleware('perm:service_os.manage');

    Route::get('projects/{id}/conflicts', [ServiceOSController::class, 'projectConflicts'])
        ->middleware('perm:service_os.view')->whereNumber('id');
    Route::get('projects/{id}/margin', [ServiceOSController::class, 'projectMargin'])
        ->middleware('perm:service_os.view')->whereNumber('id');
    Route::get('projects/{id}/suggest-crew', [ServiceOSController::class, 'suggestCrew'])
        ->middleware('perm:service_os.view')->whereNumber('id');

    Route::get('projects/{id}', [ServiceOSController::class, 'showProject'])
        ->middleware('perm:service_os.view')->whereNumber('id');
    Route::patch('projects/{id}/status', [ServiceOSController::class, 'changeStatus'])
        ->middleware('perm:service_os.manage')->whereNumber('id');

    // ---- SESI ----
    Route::post('projects/{id}/sessions', [ServiceOSController::class, 'createSession'])
        ->middleware('perm:service_os.manage')->whereNumber('id');
    Route::get('sessions/{id}/conflicts', [ServiceOSController::class, 'sessionConflicts'])
        ->middleware('perm:service_os.view')->whereNumber('id');

    // ---- SEBUT HARGA ----
    Route::post('projects/{id}/quotations', [ServiceOSController::class, 'createQuotation'])
        ->middleware('perm:service_os.manage')->whereNumber('id');
    Route::post('quotations/{id}/accept', [ServiceOSController::class, 'acceptQuotation'])
        ->middleware('perm:service_os.manage')->whereNumber('id');
    Route::post('quotations/{id}/reject', [ServiceOSController::class, 'rejectQuotation'])
        ->middleware('perm:service_os.manage')->whereNumber('id');

    // ---- KRU ----
    Route::get('crews', [ServiceOSController::class, 'crews'])
        ->middleware('perm:service_os.view');
    Route::post('crews', [ServiceOSController::class, 'createCrew'])
        ->middleware('perm:service_os.manage');
    Route::post('projects/{id}/assignments', [ServiceOSController::class, 'assignCrew'])
        ->middleware('perm:service_os.manage')->whereNumber('id');

    // ---- TUGAS ----
    Route::post('projects/{id}/tasks', [ServiceOSController::class, 'createTask'])
        ->middleware('perm:service_os.manage')->whereNumber('id');
    Route::post('projects/{id}/tasks/generate', [ServiceOSController::class, 'generateChecklist'])
        ->middleware('perm:service_os.manage')->whereNumber('id');
    Route::post('tasks/{id}/complete', [ServiceOSController::class, 'completeTask'])
        ->middleware('perm:service_os.manage')->whereNumber('id');

    // ---- PERALATAN ----
    Route::get('equipments', [ServiceOSController::class, 'equipments'])
        ->middleware('perm:service_os.view');
    Route::post('equipments', [ServiceOSController::class, 'createEquipment'])
        ->middleware('perm:service_os.manage');
    Route::post('projects/{id}/equipment-reservations', [ServiceOSController::class, 'reserveEquipment'])
        ->middleware('perm:service_os.manage')->whereNumber('id');

    // ---- PENYERAHAN ----
    Route::post('projects/{id}/deliveries', [ServiceOSController::class, 'createDelivery'])
        ->middleware('perm:service_os.manage')->whereNumber('id');
    Route::post('deliveries/{id}/accept', [ServiceOSController::class, 'acceptDelivery'])
        ->middleware('perm:service_os.manage')->whereNumber('id');

    // ---- PERISTIWA DOMAIN ----
    Route::get('events', [ServiceOSController::class, 'events'])
        ->middleware('perm:service_os.view');
    Route::post('events/dispatch', [ServiceOSController::class, 'dispatchOutbox'])
        ->middleware('perm:service_os.manage');
});
