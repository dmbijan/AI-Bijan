<?php

use Illuminate\Support\Facades\Route;
use App\Modules\WorkflowStudio\Http\Controllers\WorkflowStudioController;

/*
|--------------------------------------------------------------------------
| Workflow Studio Routes (BAB 86 / Idea #26)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/workflows
| Visual workflow builder + template marketplace, semuanya web-based.
|
*/

// Statik dahulu
Route::get('/node-types', [WorkflowStudioController::class, 'nodeTypes'])->middleware('perm:workflow.view');
Route::get('/templates', [WorkflowStudioController::class, 'templates'])->middleware('perm:workflow.view');
Route::post('/templates/{id}/install', [WorkflowStudioController::class, 'pasangTemplate'])->middleware('perm:workflow.manage');
Route::get('/summary', [WorkflowStudioController::class, 'ringkasan'])->middleware('perm:workflow.view');

// CRUD
Route::get('/', [WorkflowStudioController::class, 'index'])->middleware('perm:workflow.view');
Route::post('/', [WorkflowStudioController::class, 'store'])->middleware('perm:workflow.manage');
Route::put('/{id}', [WorkflowStudioController::class, 'update'])->middleware('perm:workflow.manage');
Route::post('/{id}/run', [WorkflowStudioController::class, 'run'])->middleware('perm:workflow.execute');
Route::get('/{id}/runs', [WorkflowStudioController::class, 'runs'])->middleware('perm:workflow.view');
