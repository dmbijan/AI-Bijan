<?php

use App\Modules\Workspace\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Workspace Routes
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/workspace
| inspired oleh https://github.com/AppFlowy-IO/AppFlowy
|
*/

// Ruang kerja
Route::get('/spaces', [WorkspaceController::class, 'index'])->middleware('perm:workspace.view');
Route::post('/spaces', [WorkspaceController::class, 'store'])->middleware('perm:workspace.manage');
Route::put('/spaces/{id}', [WorkspaceController::class, 'update'])->middleware('perm:workspace.manage');
Route::delete('/spaces/{id}', [WorkspaceController::class, 'destroy'])->middleware('perm:workspace.manage');

// Halaman
Route::get('/spaces/{id}/pages', [WorkspaceController::class, 'pages'])->middleware('perm:workspace.view');
Route::post('/spaces/{id}/pages', [WorkspaceController::class, 'storePage'])->middleware('perm:workspace.manage');
Route::get('/pages/{id}', [WorkspaceController::class, 'showPage'])->middleware('perm:workspace.view');
Route::put('/pages/{id}', [WorkspaceController::class, 'updatePage'])->middleware('perm:workspace.manage');
Route::delete('/pages/{id}', [WorkspaceController::class, 'destroyPage'])->middleware('perm:workspace.manage');
Route::post('/pages/{id}/generate-content', [WorkspaceController::class, 'generateContent'])->middleware('perm:workspace.manage');
Route::get('/pages/{id}/versions', [WorkspaceController::class, 'versions'])->middleware('perm:workspace.view');

// Blok
Route::post('/pages/{id}/blocks', [WorkspaceController::class, 'storeBlock'])->middleware('perm:workspace.manage');
Route::put('/pages/{pageId}/blocks/{blockId}', [WorkspaceController::class, 'updateBlock'])->middleware('perm:workspace.manage');
Route::post('/pages/{id}/blocks/reorder', [WorkspaceController::class, 'reorderBlocks'])->middleware('perm:workspace.manage');
Route::delete('/pages/{pageId}/blocks/{blockId}', [WorkspaceController::class, 'destroyBlock'])->middleware('perm:workspace.manage');

// Komen
Route::get('/pages/{id}/comments', [WorkspaceController::class, 'comments'])->middleware('perm:workspace.view');
Route::post('/pages/{id}/comments', [WorkspaceController::class, 'storeComment'])->middleware('perm:workspace.manage');
Route::post('/pages/{pageId}/comments/{commentId}/resolve', [WorkspaceController::class, 'resolveComment'])->middleware('perm:workspace.manage');

// Pangkalan data
Route::get('/spaces/{id}/databases', [WorkspaceController::class, 'databases'])->middleware('perm:workspace.view');
Route::post('/spaces/{id}/databases', [WorkspaceController::class, 'storeDatabase'])->middleware('perm:workspace.manage');
Route::get('/databases/{id}/rows', [WorkspaceController::class, 'rows'])->middleware('perm:workspace.view');
Route::post('/databases/{id}/rows', [WorkspaceController::class, 'storeRow'])->middleware('perm:workspace.manage');
Route::put('/databases/{databaseId}/rows/{rowId}', [WorkspaceController::class, 'updateRow'])->middleware('perm:workspace.manage');
