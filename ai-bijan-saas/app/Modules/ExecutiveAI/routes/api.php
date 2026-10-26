<?php

use Illuminate\Support\Facades\Route;
use App\Modules\ExecutiveAI\Http\Controllers\ExecutiveAIController;

/*
|--------------------------------------------------------------------------
| B-Executive AI Routes (BAB 82 / Fasa 5)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/executive-ai
| Middleware: api, auth:sanctum, tenant (ditetapkan dalam ServiceProvider)
|
*/

// Command Centre & soalan
Route::get('/command-centre', [ExecutiveAIController::class, 'commandCentre'])->middleware('perm:executive_ai.view');
Route::post('/ask', [ExecutiveAIController::class, 'ask'])->middleware('perm:executive_ai.execute');

// Briefing
Route::get('/briefings', [ExecutiveAIController::class, 'briefings'])->middleware('perm:executive_ai.view');
Route::post('/briefings/generate', [ExecutiveAIController::class, 'generateBriefing'])->middleware('perm:executive_ai.execute');
Route::post('/briefings/{id}/read', [ExecutiveAIController::class, 'markRead'])->middleware('perm:executive_ai.view');

// Autonomi
Route::get('/autonomy', [ExecutiveAIController::class, 'autonomy'])->middleware('perm:executive_ai.view');
Route::put('/autonomy', [ExecutiveAIController::class, 'updateAutonomy'])->middleware('perm:executive_ai.configure');
