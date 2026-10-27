<?php

use App\Modules\TeamAI\Http\Controllers\TeamAiWorkspaceController;
use Illuminate\Support\Facades\Route;

Route::middleware('perm:agent_studio.view')->group(function () {
    Route::get('/repositories', [TeamAiWorkspaceController::class, 'repositories']);
    Route::get('/assets', [TeamAiWorkspaceController::class, 'assets']);
});

Route::middleware('perm:agent_studio.manage')->group(function () {
    Route::post('/repositories', [TeamAiWorkspaceController::class, 'saveRepository']);
    Route::post('/repositories/{id}/sync', [TeamAiWorkspaceController::class, 'sync'])->whereNumber('id');
    Route::delete('/repositories/{id}', [TeamAiWorkspaceController::class, 'deleteRepository'])->whereNumber('id');
});
