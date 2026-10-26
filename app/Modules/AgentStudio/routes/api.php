<?php

use Illuminate\Support\Facades\Route;
use App\Modules\AgentStudio\Http\Controllers\AgentStudioController;

/*
|--------------------------------------------------------------------------
| Agent Studio Routes (BAB 84 / Idea #8)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/agent-studio
| B-Ejen — bina ejen AI tanpa kod, ala Zoho Agent Store (16 templat).
|
*/

// Templat & penjanaan
Route::get('/templates', [AgentStudioController::class, 'templates'])->middleware('perm:agent_studio.view');
Route::post('/generate', [AgentStudioController::class, 'generate'])->middleware('perm:agent_studio.manage');

// CRUD ejen
Route::get('/agents', [AgentStudioController::class, 'index'])->middleware('perm:agent_studio.view');
Route::get('/agents/{id}', [AgentStudioController::class, 'show'])->middleware('perm:agent_studio.view');
Route::put('/agents/{id}', [AgentStudioController::class, 'update'])->middleware('perm:agent_studio.manage');
Route::post('/agents/{id}/publish', [AgentStudioController::class, 'publish'])->middleware('perm:agent_studio.approve');

// Ikatan
Route::post('/agents/{id}/tools', [AgentStudioController::class, 'bindTool'])->middleware('perm:agent_studio.manage');
Route::post('/agents/{id}/knowledge', [AgentStudioController::class, 'bindKnowledge'])->middleware('perm:agent_studio.manage');

// Pelaksanaan & ujian
Route::post('/agents/{id}/run', [AgentStudioController::class, 'run'])->middleware('perm:agent_studio.execute');
Route::get('/agents/{id}/runs', [AgentStudioController::class, 'runs'])->middleware('perm:agent_studio.view');
Route::get('/agents/{id}/tests', [AgentStudioController::class, 'tests'])->middleware('perm:agent_studio.view');
Route::post('/agents/{id}/tests', [AgentStudioController::class, 'runTest'])->middleware('perm:agent_studio.execute');

// Statistik
Route::get('/statistics', [AgentStudioController::class, 'statistik'])->middleware('perm:agent_studio.view');
