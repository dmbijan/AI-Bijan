<?php

use App\Modules\SharedInbox\Http\Controllers\SharedInboxController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SharedInbox Routes
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/shared-inbox
| inspired oleh https://github.com/chatwoot/chatwoot
|
*/

// Peti masuk
Route::get('/inboxes', [SharedInboxController::class, 'index'])->middleware('perm:shared_inbox.view');
Route::post('/inboxes', [SharedInboxController::class, 'store'])->middleware('perm:shared_inbox.manage');
Route::put('/inboxes/{id}', [SharedInboxController::class, 'update'])->middleware('perm:shared_inbox.manage');
Route::delete('/inboxes/{id}', [SharedInboxController::class, 'destroy'])->middleware('perm:shared_inbox.manage');
Route::get('/inboxes/{id}/statistics', [SharedInboxController::class, 'statistics'])->middleware('perm:shared_inbox.view');

// Perbincangan
Route::get('/inboxes/{id}/conversations', [SharedInboxController::class, 'conversations'])->middleware('perm:shared_inbox.view');
Route::post('/inboxes/{id}/conversations', [SharedInboxController::class, 'storeConversation'])->middleware('perm:shared_inbox.manage');
Route::get('/conversations/{id}', [SharedInboxController::class, 'showConversation'])->middleware('perm:shared_inbox.view');
Route::put('/conversations/{id}', [SharedInboxController::class, 'updateConversation'])->middleware('perm:shared_inbox.manage');
Route::post('/conversations/{id}/messages', [SharedInboxController::class, 'sendMessage'])->middleware('perm:shared_inbox.execute');
Route::post('/conversations/{id}/ai-reply', [SharedInboxController::class, 'generateAiReply'])->middleware('perm:shared_inbox.execute');
Route::post('/conversations/{id}/assign', [SharedInboxController::class, 'assign'])->middleware('perm:shared_inbox.manage');

// Ulasan sedia ada
Route::get('/inboxes/{id}/canned-responses', [SharedInboxController::class, 'cannedResponses'])->middleware('perm:shared_inbox.view');
Route::post('/inboxes/{id}/canned-responses', [SharedInboxController::class, 'storeCannedResponse'])->middleware('perm:shared_inbox.manage');
Route::put('/inboxes/{id}/canned-responses/{responseId}', [SharedInboxController::class, 'updateCannedResponse'])->middleware('perm:shared_inbox.manage');
Route::delete('/inboxes/{id}/canned-responses/{responseId}', [SharedInboxController::class, 'destroyCannedResponse'])->middleware('perm:shared_inbox.manage');

// Makro
Route::get('/inboxes/{id}/macros', [SharedInboxController::class, 'macros'])->middleware('perm:shared_inbox.view');
Route::post('/inboxes/{id}/macros', [SharedInboxController::class, 'storeMacro'])->middleware('perm:shared_inbox.manage');
Route::put('/inboxes/{id}/macros/{macroId}', [SharedInboxController::class, 'updateMacro'])->middleware('perm:shared_inbox.manage');
Route::delete('/inboxes/{id}/macros/{macroId}', [SharedInboxController::class, 'destroyMacro'])->middleware('perm:shared_inbox.manage');
Route::post('/inboxes/{id}/macros/{macroId}/run', [SharedInboxController::class, 'runMacro'])->middleware('perm:shared_inbox.execute');

// SLA
Route::post('/sla/check', [SharedInboxController::class, 'checkSla'])->middleware('perm:shared_inbox.view');
