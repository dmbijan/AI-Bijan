<?php

use Illuminate\Support\Facades\Route;
use App\Modules\HRMS\Http\Controllers\EmployeeController;
use App\Modules\HRMS\Http\Controllers\DepartmentController;
use App\Modules\HRMS\Http\Controllers\LeaveController;
use App\Modules\HRMS\Http\Controllers\AttendanceController;
use App\Modules\HRMS\Http\Controllers\ShiftController;

/*
|--------------------------------------------------------------------------
| HRMS Module Routes
|--------------------------------------------------------------------------
|
| Here is where you can register the routes for the HRMS module.
| Handles Employee, Shift & Leave Management.
|
*/

// Employee routes
Route::prefix('employees')->name('hrms.employees.')->group(function () {
    Route::get('/', [EmployeeController::class, 'index'])->middleware('perm:hrms.view')->name('index');
    Route::post('/', [EmployeeController::class, 'store'])->middleware('perm:hrms.manage')->name('store');
    Route::get('/expiring-contracts', [EmployeeController::class, 'getExpiringContracts'])->middleware('perm:hrms.view')->name('expiring');
    Route::get('/probationers', [EmployeeController::class, 'getProbationers'])->middleware('perm:hrms.view')->name('probationers');
    Route::post('/bulk-import', [EmployeeController::class, 'bulkImport'])->middleware('perm:hrms.manage')->name('bulk-import');

    Route::middleware('can:hrms.view')->group(function () {
        Route::get('/{id}', [EmployeeController::class, 'show'])->name('show');
        Route::get('/{id}/attendance-summary', [EmployeeController::class, 'attendanceSummary'])->name('attendance-summary');
    });

    Route::middleware('can:hrms.manage')->group(function () {
        Route::put('/{id}', [EmployeeController::class, 'update'])->name('update');
        Route::post('/{id}/terminate', [EmployeeController::class, 'terminate'])->name('terminate');
        Route::post('/{id}/assign-shift', [EmployeeController::class, 'assignShift'])->name('assign-shift');
    });
});

// Department routes
Route::prefix('departments')->name('hrms.departments.')->group(function () {
    Route::get('/', [DepartmentController::class, 'index'])->middleware('perm:hrms.view')->name('index');
    Route::post('/', [DepartmentController::class, 'store'])->middleware('perm:hrms.manage')->name('store');
    Route::get('/statistics', [DepartmentController::class, 'statistics'])->middleware('perm:hrms.view')->name('statistics');

    Route::middleware('can:hrms.view')->group(function () {
        Route::get('/{id}', [DepartmentController::class, 'show'])->name('show');
    });

    Route::middleware('can:hrms.manage')->group(function () {
        Route::put('/{id}', [DepartmentController::class, 'update'])->name('update');
        Route::delete('/{id}', [DepartmentController::class, 'destroy'])->name('destroy');
    });
});

// Leave routes
Route::prefix('leave')->name('hrms.leave.')->group(function () {
    // Leave types
    Route::get('/types', [LeaveController::class, 'leaveTypes'])->middleware('perm:hrms.view')->name('types');
    Route::post('/types', [LeaveController::class, 'createLeaveType'])->middleware('perm:hrms.manage')->name('types.store');

    // Leave requests
    Route::post('/requests', [LeaveController::class, 'submitRequest'])->middleware('perm:hrms.manage')->name('requests.submit');
    Route::get('/pending', [LeaveController::class, 'getPending'])->middleware('perm:hrms.approve')->name('pending');
    Route::post('/auto-approve', [LeaveController::class, 'autoApprove'])->middleware('perm:hrms.approve')->name('auto-approve');

    Route::middleware('can:hrms.view')->group(function () {
        Route::get('/balance', [LeaveController::class, 'getBalance'])->name('balance');
        Route::get('/history', [LeaveController::class, 'getHistory'])->name('history');
    });

    Route::middleware('can:hrms.approve')->group(function () {
        Route::post('/requests/{id}/approve', [LeaveController::class, 'approveRequest'])->name('requests.approve');
        Route::post('/requests/{id}/reject', [LeaveController::class, 'rejectRequest'])->name('requests.reject');
    });

    Route::middleware('can:hrms.manage')->group(function () {
        Route::post('/requests/{id}/cancel', [LeaveController::class, 'cancelRequest'])->name('requests.cancel');
        Route::post('/allocate', [LeaveController::class, 'allocateLeave'])->name('allocate');
    });
});

// Attendance routes
Route::prefix('attendance')->name('hrms.attendance.')->group(function () {
    // Self-service
    Route::post('/check-in', [AttendanceController::class, 'checkIn'])->middleware('perm:hrms.view')->name('check-in');
    Route::post('/check-out', [AttendanceController::class, 'checkOut'])->middleware('perm:hrms.view')->name('check-out');
    Route::get('/today', [AttendanceController::class, 'getToday'])->middleware('perm:hrms.view')->name('today');

    // Admin attendance management
    Route::middleware('can:hrms.view')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/daily-summary', [AttendanceController::class, 'dailySummary'])->name('daily-summary');
        Route::get('/monthly-report', [AttendanceController::class, 'monthlyReport'])->name('monthly-report');
    });

    Route::middleware('can:hrms.manage')->group(function () {
        Route::post('/mark', [AttendanceController::class, 'markAttendance'])->name('mark');
        Route::post('/bulk-mark', [AttendanceController::class, 'bulkMark'])->name('bulk-mark');
    });
});

// Shift routes
Route::prefix('shifts')->name('hrms.shifts.')->group(function () {
    Route::get('/', [ShiftController::class, 'index'])->middleware('perm:hrms.view')->name('index');
    Route::post('/', [ShiftController::class, 'store'])->middleware('perm:hrms.manage')->name('store');

    Route::middleware('can:hrms.view')->group(function () {
        Route::get('/{id}', [ShiftController::class, 'show'])->name('show');
    });

    Route::middleware('can:hrms.manage')->group(function () {
        Route::put('/{id}', [ShiftController::class, 'update'])->name('update');
        Route::delete('/{id}', [ShiftController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-status', [ShiftController::class, 'toggleStatus'])->name('toggle-status');
    });
});
