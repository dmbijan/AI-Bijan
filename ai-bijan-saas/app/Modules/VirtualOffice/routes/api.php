<?php

use Illuminate\Support\Facades\Route;
use App\Modules\VirtualOffice\Http\Controllers\AgentController;
use App\Modules\VirtualOffice\Http\Controllers\TaskController;
use App\Modules\VirtualOffice\Http\Controllers\BoardController;
use App\Modules\VirtualOffice\Http\Controllers\ScheduleController;

/*
|--------------------------------------------------------------------------
| B-Virtual Office API Routes
|--------------------------------------------------------------------------
|
| Agent, Task, Board & Schedule management routes
|
*/

// ============================================
// Agents — Team members & AI agents
// ============================================

Route::middleware('perm:virtual_office.view')->group(function () {
    Route::get('agents', [AgentController::class, 'index']);
    Route::get('agents/{id}', [AgentController::class, 'show'])->whereNumber('id');
    Route::get('agents/statistics', [AgentController::class, 'statistics']);
});

Route::middleware('perm:virtual_office.manage')->group(function () {
    Route::post('agents', [AgentController::class, 'store']);
    Route::put('agents/{id}', [AgentController::class, 'update'])->whereNumber('id');
    Route::post('agents/{id}/status', [AgentController::class, 'updateStatus'])->whereNumber('id');
    Route::delete('agents/{id}', [AgentController::class, 'destroy'])->whereNumber('id');
});

// ============================================
// Boards — Kanban boards & columns
// ============================================

Route::middleware('perm:virtual_office.view')->group(function () {
    Route::get('boards', [BoardController::class, 'index']);
    Route::get('boards/{id}', [BoardController::class, 'show'])->whereNumber('id');
});

Route::middleware('perm:virtual_office.manage')->group(function () {
    Route::post('boards', [BoardController::class, 'store']);
    Route::put('boards/{id}', [BoardController::class, 'update'])->whereNumber('id');
    Route::post('boards/{id}/members', [BoardController::class, 'addMember'])->whereNumber('id');
    Route::delete('boards/{id}/members', [BoardController::class, 'removeMember'])->whereNumber('id');
    Route::post('boards/{id}/archive', [BoardController::class, 'archive'])->whereNumber('id');
    Route::post('boards/{id}/restore', [BoardController::class, 'restore'])->whereNumber('id');
    Route::post('boards/{id}/duplicate', [BoardController::class, 'duplicate'])->whereNumber('id');
    Route::delete('boards/{id}', [BoardController::class, 'destroy'])->whereNumber('id');
    Route::post('boards/{id}/columns', [BoardController::class, 'addColumn'])->whereNumber('id');
    Route::delete('boards/{id}/columns', [BoardController::class, 'deleteColumn'])->whereNumber('id');
});

// ============================================
// Tasks/Cards — Task management on boards
// ============================================

Route::middleware('perm:virtual_office.view')->group(function () {
    Route::get('tasks', [TaskController::class, 'index']);
    Route::get('tasks/{id}', [TaskController::class, 'show'])->whereNumber('id');
    Route::get('tasks/overdue', [TaskController::class, 'overdue']);
    Route::get('tasks/statistics', [TaskController::class, 'statistics']);
});

Route::middleware('perm:virtual_office.manage')->group(function () {
    Route::post('tasks', [TaskController::class, 'store']);
    Route::put('tasks/{id}', [TaskController::class, 'update'])->whereNumber('id');
    Route::post('tasks/{id}/move', [TaskController::class, 'move'])->whereNumber('id');
    Route::post('tasks/reorder', [TaskController::class, 'reorder']);
    Route::post('tasks/{id}/complete', [TaskController::class, 'complete'])->whereNumber('id');
    Route::post('tasks/{id}/comments', [TaskController::class, 'addComment'])->whereNumber('id');
    Route::post('tasks/{id}/assign', [TaskController::class, 'assign'])->whereNumber('id');
    Route::delete('tasks/{id}', [TaskController::class, 'destroy'])->whereNumber('id');
});

// ============================================
// Schedules — Meetings & calendar events
// ============================================

Route::middleware('perm:virtual_office.view')->group(function () {
    Route::get('schedules', [ScheduleController::class, 'index']);
    Route::get('schedules/{id}', [ScheduleController::class, 'show'])->whereNumber('id');
    Route::get('schedules/today', [ScheduleController::class, 'today']);
    Route::get('schedules/upcoming', [ScheduleController::class, 'upcoming']);
    Route::get('schedules/statistics', [ScheduleController::class, 'statistics']);
});

Route::middleware('perm:virtual_office.manage')->group(function () {
    Route::post('schedules', [ScheduleController::class, 'store']);
    Route::put('schedules/{id}', [ScheduleController::class, 'update'])->whereNumber('id');
    Route::post('schedules/{id}/cancel', [ScheduleController::class, 'cancel'])->whereNumber('id');
    Route::post('schedules/{id}/complete', [ScheduleController::class, 'complete'])->whereNumber('id');
    Route::post('schedules/{id}/respond', [ScheduleController::class, 'respond'])->whereNumber('id');
    Route::delete('schedules/{id}', [ScheduleController::class, 'destroy'])->whereNumber('id');
});
