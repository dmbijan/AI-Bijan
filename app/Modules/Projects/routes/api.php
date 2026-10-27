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
Route::get('/', [ProjectController::class, 'index'])->middleware(['perm:projects.view']);
Route::get('/{project}', [ProjectController::class, 'show'])->middleware(['perm:projects.view']);
Route::post('/', [ProjectController::class, 'store'])->middleware(['perm:projects.manage']);
Route::put('/{project}', [ProjectController::class, 'update'])->middleware(['perm:projects.manage']);
Route::delete('/{project}', [ProjectController::class, 'destroy'])->middleware(['perm:projects.manage']);
Route::post('/{project}/members', [ProjectController::class, 'addMember'])->middleware(['perm:projects.manage']);
Route::delete('/{project}/members/{userId}', [ProjectController::class, 'removeMember'])->middleware(['perm:projects.manage']);
Route::get('/{project}/summary', [ProjectController::class, 'summary'])->middleware(['perm:projects.view']);

// Tasks
Route::get('/{project}/tasks', [TaskController::class, 'index'])->middleware(['perm:projects.view']);
Route::get('/tasks/{task}', [TaskController::class, 'show'])->middleware(['perm:projects.view']);
Route::post('/{project}/tasks', [TaskController::class, 'store'])->middleware(['perm:projects.manage']);
Route::put('/tasks/{task}', [TaskController::class, 'update'])->middleware(['perm:projects.manage']);
Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->middleware(['perm:projects.manage']);
Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->middleware(['perm:projects.manage']);
Route::patch('/tasks/{task}/assign', [TaskController::class, 'assign'])->middleware(['perm:projects.manage']);
Route::get('/{project}/tasks/overdue', [TaskController::class, 'overdue'])->middleware(['perm:projects.view']);
Route::get('/tasks/my-tasks', [TaskController::class, 'myTasks'])->middleware(['perm:projects.view']);

// Milestones
Route::get('/{project}/milestones', [MilestoneController::class, 'index'])->middleware(['perm:projects.view']);
Route::get('/milestones/{milestone}', [MilestoneController::class, 'show'])->middleware(['perm:projects.view']);
Route::post('/{project}/milestones', [MilestoneController::class, 'store'])->middleware(['perm:projects.manage']);
Route::put('/milestones/{milestone}', [MilestoneController::class, 'update'])->middleware(['perm:projects.manage']);
Route::delete('/milestones/{milestone}', [MilestoneController::class, 'destroy'])->middleware(['perm:projects.manage']);
Route::post('/milestones/{milestone}/complete', [MilestoneController::class, 'complete'])->middleware(['perm:projects.manage']);
Route::get('/{project}/milestones/overdue', [MilestoneController::class, 'overdue'])->middleware(['perm:projects.view']);

// Timesheets
Route::get('/{project}/timesheets', [TimesheetController::class, 'index'])->middleware(['perm:projects.timesheet']);
Route::get('/timesheets/{timesheet}', [TimesheetController::class, 'show'])->middleware(['perm:projects.timesheet']);
Route::post('/{project}/timesheets', [TimesheetController::class, 'store'])->middleware(['perm:projects.timesheet']);
Route::put('/timesheets/{timesheet}', [TimesheetController::class, 'update'])->middleware(['perm:projects.timesheet']);
Route::post('/timesheets/{timesheet}/submit', [TimesheetController::class, 'submit'])->middleware(['perm:projects.timesheet']);
Route::post('/timesheets/{timesheet}/approve', [TimesheetController::class, 'approve'])->middleware(['perm:projects.approve']);
Route::post('/timesheets/{timesheet}/reject', [TimesheetController::class, 'reject'])->middleware(['perm:projects.approve']);
Route::delete('/timesheets/{timesheet}', [TimesheetController::class, 'destroy'])->middleware(['perm:projects.timesheet']);
Route::get('/{project}/timesheets/summary', [TimesheetController::class, 'summary'])->middleware(['perm:projects.timesheet']);
Route::get('/timesheets/my-weekly-report', [TimesheetController::class, 'myWeeklyReport'])->middleware(['perm:projects.timesheet']);
Route::get('/timesheets/my-timesheets', [TimesheetController::class, 'myTimesheets'])->middleware(['perm:projects.timesheet']);
