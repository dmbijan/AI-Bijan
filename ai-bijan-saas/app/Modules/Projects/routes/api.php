<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Projects\Http\Controllers\ProjectController;
use App\Modules\Projects\Http\Controllers\TaskController;
use App\Modules\Projects\Http\Controllers\MilestoneController;
use App\Modules\Projects\Http\Controllers\TimesheetController;

/*
|--------------------------------------------------------------------------
| B-Projects Routes
|--------------------------------------------------------------------------
|
| Project, Task & Timesheet Management API routes.
|
*/

// Projects
Route::get('/', [ProjectController::class, 'index'])->middleware(['permission:projects.view']);
Route::get('/{project}', [ProjectController::class, 'show'])->middleware(['permission:projects.view']);
Route::post('/', [ProjectController::class, 'store'])->middleware(['permission:projects.manage']);
Route::put('/{project}', [ProjectController::class, 'update'])->middleware(['permission:projects.manage']);
Route::delete('/{project}', [ProjectController::class, 'destroy'])->middleware(['permission:projects.manage']);
Route::post('/{project}/members', [ProjectController::class, 'addMember'])->middleware(['permission:projects.manage']);
Route::delete('/{project}/members/{userId}', [ProjectController::class, 'removeMember'])->middleware(['permission:projects.manage']);
Route::get('/{project}/summary', [ProjectController::class, 'summary'])->middleware(['permission:projects.view']);

// Tasks
Route::get('/{project}/tasks', [TaskController::class, 'index'])->middleware(['permission:projects.view']);
Route::get('/tasks/{task}', [TaskController::class, 'show'])->middleware(['permission:projects.view']);
Route::post('/{project}/tasks', [TaskController::class, 'store'])->middleware(['permission:projects.manage']);
Route::put('/tasks/{task}', [TaskController::class, 'update'])->middleware(['permission:projects.manage']);
Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->middleware(['permission:projects.manage']);
Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->middleware(['permission:projects.manage']);
Route::patch('/tasks/{task}/assign', [TaskController::class, 'assign'])->middleware(['permission:projects.manage']);
Route::get('/{project}/tasks/overdue', [TaskController::class, 'overdue'])->middleware(['permission:projects.view']);
Route::get('/tasks/my-tasks', [TaskController::class, 'myTasks'])->middleware(['permission:projects.view']);

// Milestones
Route::get('/{project}/milestones', [MilestoneController::class, 'index'])->middleware(['permission:projects.view']);
Route::get('/milestones/{milestone}', [MilestoneController::class, 'show'])->middleware(['permission:projects.view']);
Route::post('/{project}/milestones', [MilestoneController::class, 'store'])->middleware(['permission:projects.manage']);
Route::put('/milestones/{milestone}', [MilestoneController::class, 'update'])->middleware(['permission:projects.manage']);
Route::delete('/milestones/{milestone}', [MilestoneController::class, 'destroy'])->middleware(['permission:projects.manage']);
Route::post('/milestones/{milestone}/complete', [MilestoneController::class, 'complete'])->middleware(['permission:projects.manage']);
Route::get('/{project}/milestones/overdue', [MilestoneController::class, 'overdue'])->middleware(['permission:projects.view']);

// Timesheets
Route::get('/{project}/timesheets', [TimesheetController::class, 'index'])->middleware(['permission:projects.timesheet']);
Route::get('/timesheets/{timesheet}', [TimesheetController::class, 'show'])->middleware(['permission:projects.timesheet']);
Route::post('/{project}/timesheets', [TimesheetController::class, 'store'])->middleware(['permission:projects.timesheet']);
Route::put('/timesheets/{timesheet}', [TimesheetController::class, 'update'])->middleware(['permission:projects.timesheet']);
Route::post('/timesheets/{timesheet}/submit', [TimesheetController::class, 'submit'])->middleware(['permission:projects.timesheet']);
Route::post('/timesheets/{timesheet}/approve', [TimesheetController::class, 'approve'])->middleware(['permission:projects.approve']);
Route::post('/timesheets/{timesheet}/reject', [TimesheetController::class, 'reject'])->middleware(['permission:projects.approve']);
Route::delete('/timesheets/{timesheet}', [TimesheetController::class, 'destroy'])->middleware(['permission:projects.timesheet']);
Route::get('/{project}/timesheets/summary', [TimesheetController::class, 'summary'])->middleware(['permission:projects.timesheet']);
Route::get('/timesheets/my-weekly-report', [TimesheetController::class, 'myWeeklyReport'])->middleware(['permission:projects.timesheet']);
Route::get('/timesheets/my-timesheets', [TimesheetController::class, 'myTimesheets'])->middleware(['permission:projects.timesheet']);
