<?php

use Illuminate\Support\Facades\Route;
use App\Modules\SalesCopilot\Http\Controllers\SalesCopilotController;

/*
|--------------------------------------------------------------------------
| Sales Copilot Routes (BAB 82 / Fasa 3)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/sales-copilot
| Middleware: api, auth:sanctum, tenant (ditetapkan dalam ServiceProvider)
|
*/

// Brief prospect
Route::get('/brief/{prospectId}', [SalesCopilotController::class, 'brief'])->middleware('perm:sales_copilot.view');
Route::get('/requires-attention', [SalesCopilotController::class, 'requiresAttention'])->middleware('perm:sales_copilot.view');

// Analisis balasan
Route::post('/analyze-reply', [SalesCopilotController::class, 'analyzeReply'])->middleware('perm:sales_copilot.view');
Route::post('/prospects/{prospectId}/reply', [SalesCopilotController::class, 'processReply'])->middleware('perm:sales_copilot.execute');

// Follow-up
Route::get('/follow-up-queue', [SalesCopilotController::class, 'followUpQueue'])->middleware('perm:sales_copilot.view');
