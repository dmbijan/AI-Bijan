<?php

use App\Modules\OpenGenAI\Http\Controllers\OpenGenAIController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Open Generative AI Routes
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/open-gen-ai
| inspired oleh https://github.com/Anil-matcha/Open-Generative-AI
|
| Enjin generatif pelbagai provider: tenant sambungkan provider sendiri
| dan menjana teks / imej / video / audio melalui kontrak seragam.
|
*/

// Provider
Route::get('/providers', [OpenGenAIController::class, 'index'])->middleware('perm:open_gen_ai.view');
Route::post('/providers', [OpenGenAIController::class, 'store'])->middleware('perm:open_gen_ai.manage');
Route::put('/providers/{id}', [OpenGenAIController::class, 'update'])->middleware('perm:open_gen_ai.manage');
Route::delete('/providers/{id}', [OpenGenAIController::class, 'destroy'])->middleware('perm:open_gen_ai.manage');

// Penjanaan
Route::post('/generate', [OpenGenAIController::class, 'generate'])->middleware('perm:open_gen_ai.execute');
Route::get('/jobs', [OpenGenAIController::class, 'jobs'])->middleware('perm:open_gen_ai.view');
Route::get('/jobs/{id}', [OpenGenAIController::class, 'job'])->middleware('perm:open_gen_ai.view');
Route::get('/summary', [OpenGenAIController::class, 'ringkasan'])->middleware('perm:open_gen_ai.view');
