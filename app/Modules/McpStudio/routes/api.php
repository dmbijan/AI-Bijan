<?php

use Illuminate\Support\Facades\Route;
use App\Modules\McpStudio\Http\Controllers\McpStudioController;

/*
|--------------------------------------------------------------------------
| MCP Studio Routes (BAB 84 / Idea #1)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/mcp
| AI MCP Studio — sambungkan mana-mana AI luar (Claude Desktop,
| Cursor, n8n, Zapier) kepada AI BIJAN melalui protokol MCP.
|
*/

// Protokol MCP
Route::post('/initialize', [McpStudioController::class, 'initialize'])->middleware('perm:mcp.view');
Route::post('/rpc', [McpStudioController::class, 'rpc'])->middleware('perm:mcp.execute');

// Alat (tools)
Route::get('/tools', [McpStudioController::class, 'tools'])->middleware('perm:mcp.view');
Route::post('/tools', [McpStudioController::class, 'daftarTool'])->middleware('perm:mcp.manage');
Route::post('/tools/call', [McpStudioController::class, 'callTool'])->middleware('perm:mcp.execute');

// Kelulusan human-in-the-loop
Route::get('/approvals', [McpStudioController::class, 'pendingApprovals'])->middleware('perm:mcp.view');
Route::post('/approvals/{id}/approve', [McpStudioController::class, 'approve'])->middleware('perm:mcp.approve');
Route::post('/approvals/{id}/reject', [McpStudioController::class, 'reject'])->middleware('perm:mcp.approve');

// Sumber (resources)
Route::get('/resources', [McpStudioController::class, 'resources'])->middleware('perm:mcp.view');
Route::post('/resources', [McpStudioController::class, 'storeResource'])->middleware('perm:mcp.manage');

// Prompt
Route::get('/prompts', [McpStudioController::class, 'prompts'])->middleware('perm:mcp.view');
Route::post('/prompts', [McpStudioController::class, 'storePrompt'])->middleware('perm:mcp.manage');

// Utiliti
Route::get('/statistics', [McpStudioController::class, 'statistik'])->middleware('perm:mcp.view');
Route::get('/config', [McpStudioController::class, 'konfigurasi'])->middleware('perm:mcp.view');
